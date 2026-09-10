<?php
/**
 * Cascading province → municipality → barangay selects, plus a manual purok field.
 *
 * Expected variables:
 *   $barangays (array)
 *   $selectedProvince, $selectedMunicipality, $selectedBarangay, $selectedPurok
 *   $inputClass (optional)
 *   $lockBarangay (optional string to freeze barangay)
 *   $purokRequired (bool, default true)
 *   $fieldPrefix (optional id prefix)
 */
if (!class_exists('Location')) {
    require_once dirname(__DIR__) . '/Classes/Location.php';
}

$inputClass = $inputClass ?? 'w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 py-3 px-4 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-700';
$labelClassLoc = $labelClass ?? 'block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5';
$selectedProvince = $selectedProvince ?? Location::DEFAULT_PROVINCE;
$selectedMunicipality = $selectedMunicipality ?? Location::DEFAULT_MUNICIPALITY;
$selectedBarangay = $selectedBarangay ?? '';
$selectedPurok = $selectedPurok ?? '';
$lockBarangay = $lockBarangay ?? null;
$purokRequired = $purokRequired ?? true;
$fieldPrefix = $fieldPrefix ?? '';
$barangays = $barangays ?? [];
$provinceId = $fieldPrefix . 'province';
$municipalityId = $fieldPrefix . 'municipality';
$barangayId = $fieldPrefix . 'barangay';
$purokId = $fieldPrefix . 'purok';
?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 location-cascade" data-barangays="<?php echo htmlspecialchars(json_encode(array_values($barangays)), ENT_QUOTES, 'UTF-8'); ?>">
    <div>
        <label class="<?php echo $labelClassLoc; ?>" for="<?php echo htmlspecialchars($provinceId); ?>">Province *</label>
        <select name="province" id="<?php echo htmlspecialchars($provinceId); ?>" required class="<?php echo $inputClass; ?> loc-province">
            <option value="">Select province</option>
            <?php foreach (Location::provinces() as $provinceOption): ?>
                <option value="<?php echo htmlspecialchars($provinceOption); ?>" <?php echo $selectedProvince === $provinceOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($provinceOption); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="<?php echo $labelClassLoc; ?>" for="<?php echo htmlspecialchars($municipalityId); ?>">Municipality *</label>
        <select name="municipality" id="<?php echo htmlspecialchars($municipalityId); ?>" required class="<?php echo $inputClass; ?> loc-municipality">
            <option value="">Select municipality</option>
            <?php foreach (Location::municipalities($selectedProvince) as $muniOption): ?>
                <option value="<?php echo htmlspecialchars($muniOption); ?>" <?php echo $selectedMunicipality === $muniOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($muniOption); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="<?php echo $labelClassLoc; ?>" for="<?php echo htmlspecialchars($barangayId); ?>">Barangay *</label>
        <?php if ($lockBarangay): ?>
            <input type="text" value="<?php echo htmlspecialchars($lockBarangay); ?>" disabled class="<?php echo $inputClass; ?>">
            <input type="hidden" name="barangay" value="<?php echo htmlspecialchars($lockBarangay); ?>">
        <?php else: ?>
            <select name="barangay" id="<?php echo htmlspecialchars($barangayId); ?>" required class="<?php echo $inputClass; ?> loc-barangay">
                <option value="">Select barangay</option>
                <?php foreach ($barangays as $barangayOption): ?>
                    <option value="<?php echo htmlspecialchars($barangayOption); ?>" <?php echo $selectedBarangay === $barangayOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($barangayOption); ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </div>
    <div>
        <label class="<?php echo $labelClassLoc; ?>" for="<?php echo htmlspecialchars($purokId); ?>">Purok<?php echo $purokRequired ? ' *' : ''; ?></label>
        <input type="text" name="purok" id="<?php echo htmlspecialchars($purokId); ?>" maxlength="255" <?php echo $purokRequired ? 'required' : ''; ?> value="<?php echo htmlspecialchars($selectedPurok); ?>" placeholder="Enter your purok" class="<?php echo $inputClass; ?>">
    </div>
</div>
<script>
(function() {
    if (window.osyLocationCascadeBound) {
        document.querySelectorAll('.location-cascade:not([data-bound])').forEach(function(root) {
            root.setAttribute('data-bound', '1');
        });
        return;
    }
    window.osyLocationCascadeBound = true;
    const municipalities = { 'Misamis Occidental': ['Panaon'] };
    function bindCascade(root) {
        const province = root.querySelector('.loc-province');
        const municipality = root.querySelector('.loc-municipality');
        const barangay = root.querySelector('.loc-barangay');
        if (!province || !municipality) return;
        const barangayList = JSON.parse(root.getAttribute('data-barangays') || '[]');
        function fillSelect(select, items, placeholder, selected) {
            if (!select) return;
            const current = selected || select.value;
            select.innerHTML = '';
            const empty = document.createElement('option');
            empty.value = '';
            empty.textContent = placeholder;
            select.appendChild(empty);
            items.forEach(function(item) {
                const opt = document.createElement('option');
                opt.value = item;
                opt.textContent = item;
                if (item === current) opt.selected = true;
                select.appendChild(opt);
            });
            select.disabled = items.length === 0;
        }
        function onProvinceChange() {
            fillSelect(municipality, municipalities[province.value] || [], 'Select municipality', municipality.value);
            onMunicipalityChange();
        }
        function onMunicipalityChange() {
            if (barangay) {
                fillSelect(barangay, municipality.value ? barangayList : [], 'Select barangay', barangay.value);
            }
        }
        province.addEventListener('change', onProvinceChange);
        municipality.addEventListener('change', onMunicipalityChange);
        if (!municipality.value) {
            onProvinceChange();
        } else if (barangay && !barangay.value) {
            onMunicipalityChange();
        }
    }
    document.querySelectorAll('.location-cascade:not([data-bound])').forEach(function(root) {
        root.setAttribute('data-bound', '1');
        bindCascade(root);
    });
})();
</script>
