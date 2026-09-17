<?php
$files = [
    'c:/xampp/htdocs/osy_db/pages/my-job-openings.php', 
    'c:/xampp/htdocs/osy_db/pages/my-training-programs.php', 
    'c:/xampp/htdocs/osy_db/pages/opportunity-applications.php'
];
foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    if (strpos($c, 'DOMContentLoaded') !== false) {
        $c = preg_replace('/document\.addEventListener\([\'"]DOMContentLoaded[\'"],\s*\(\)\s*=>\s*\{/', '(function() {', $c);
        $c = preg_replace('/\}\);(\s*<\/script>)/', '})();$1', $c);
        file_put_contents($f, $c);
        echo 'Fixed ' . basename($f) . "\n";
    }
}
