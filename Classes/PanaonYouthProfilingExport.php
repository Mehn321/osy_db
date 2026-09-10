<?php

/**
 * Fills the official Panaon Youth Profiling Excel template without replacing its layout.
 */
class PanaonYouthProfilingExport
{
    private const TEMPLATE_RELATIVE = '/templates/PANAON-YOUTH-PROFILING.xlsx';
    private const DATA_START_ROW = 9;
    private const MUNICIPALITY = 'PANAON';
    private const PROVINCE = 'MISAMIS OCCIDENTAL';
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    public static function templatePath()
    {
        return dirname(__DIR__) . self::TEMPLATE_RELATIVE;
    }

    /**
     * @param array<int, array<string, mixed>> $profiles
     * @return array{success:bool,filename?:string,filepath?:string,message?:string}
     */
    public function export(array $profiles)
    {
        $template = self::templatePath();
        if (!is_readable($template)) {
            return [
                'success' => false,
                'message' => 'Panaon Youth Profiling template is missing.',
            ];
        }

        $exportDir = dirname(__DIR__) . '/exports';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $filename = 'PANAON-YOUTH-PROFILING_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = $exportDir . '/' . $filename;
        if (!copy($template, $filepath)) {
            return [
                'success' => false,
                'message' => 'Unable to copy the profiling template.',
            ];
        }

        $zip = new ZipArchive();
        if ($zip->open($filepath) !== true) {
            @unlink($filepath);
            return [
                'success' => false,
                'message' => 'Unable to open the profiling template.',
            ];
        }

        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $stylesXml = $zip->getFromName('xl/styles.xml');
        if ($sharedXml === false || $sheetXml === false || $stylesXml === false) {
            $zip->close();
            @unlink($filepath);
            return [
                'success' => false,
                'message' => 'The profiling template is incomplete.',
            ];
        }

        $styleIndex = $this->ensureDataStyle($stylesXml);
        $zip->deleteName('xl/styles.xml');
        $zip->addFromString('xl/styles.xml', $stylesXml);

        $strings = $this->loadSharedStrings($sharedXml);
        $rowsXml = $this->buildDataRows($profiles, $strings, $styleIndex);
        $sharedXml = $this->buildSharedStringsXml($strings);
        $lastRow = count($profiles) > 0 ? (self::DATA_START_ROW + count($profiles) - 1) : 8;
        $sheetXml = $this->injectRows($sheetXml, $rowsXml, $lastRow);

        $zip->deleteName('xl/sharedStrings.xml');
        $zip->addFromString('xl/sharedStrings.xml', $sharedXml);
        $zip->deleteName('xl/worksheets/sheet1.xml');
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
        ];
    }

    private function ensureDataStyle(&$stylesXml)
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($stylesXml);

        $xfs = $dom->getElementsByTagName('cellXfs')->item(0);
        if (!$xfs) {
            return 0;
        }

        $index = (int) $xfs->getAttribute('count');
        $xf = $dom->createElementNS(self::NS, 'xf');
        $xf->setAttribute('numFmtId', '0');
        $xf->setAttribute('fontId', '0');
        $xf->setAttribute('fillId', '0');
        $xf->setAttribute('borderId', '4');
        $xf->setAttribute('xfId', '0');
        $xf->setAttribute('applyFont', '1');
        $xf->setAttribute('applyBorder', '1');
        $xf->setAttribute('applyAlignment', '1');

        $alignment = $dom->createElementNS(self::NS, 'alignment');
        $alignment->setAttribute('horizontal', 'center');
        $alignment->setAttribute('vertical', 'center');
        $alignment->setAttribute('wrapText', '1');
        $xf->appendChild($alignment);
        $xfs->appendChild($xf);
        $xfs->setAttribute('count', (string) ($index + 1));

        $stylesXml = $dom->saveXML();
        return $index;
    }

    private function loadSharedStrings($sharedXml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($sharedXml);
        $strings = [];
        foreach ($dom->getElementsByTagName('si') as $si) {
            $strings[] = $si->textContent;
        }
        return $strings;
    }

    private function addSharedString(array &$strings, $value)
    {
        $value = (string) $value;
        foreach ($strings as $index => $existing) {
            if ($existing === $value) {
                return $index;
            }
        }
        $strings[] = $value;
        return count($strings) - 1;
    }

    private function buildSharedStringsXml(array $strings)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"';
        $xml .= ' count="' . count($strings) . '" uniqueCount="' . count($strings) . '">';
        foreach ($strings as $value) {
            $escaped = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $space = ($value !== trim($value)) ? ' xml:space="preserve"' : '';
            $xml .= '<si><t' . $space . '>' . $escaped . '</t></si>';
        }
        $xml .= '</sst>';
        return $xml;
    }

    private function buildDataRows(array $profiles, array &$strings, $styleIndex)
    {
        $xml = '';
        $rowNum = self::DATA_START_ROW;
        foreach ($profiles as $index => $profile) {
            $age = $profile['age'] ?? '';
            if ($age === '' || $age === null) {
                $age = $this->ageFromBirthdate($profile['date_of_birth'] ?? '');
            }

            $occupation = trim((string) ($profile['occupation'] ?? ''));
            if ($occupation === '') {
                $occupation = trim((string) ($profile['engagement_status'] ?? ''));
            }
            if ($occupation === '') {
                $occupation = trim((string) ($profile['primary_skill'] ?? ''));
            }

            $purok = trim((string) ($profile['purok'] ?? ''));
            if ($purok === '') {
                $purok = trim((string) ($profile['address'] ?? ''));
            }

            $birthday = '';
            if (!empty($profile['date_of_birth']) && $profile['date_of_birth'] !== '0000-00-00') {
                $dt = DateTime::createFromFormat('Y-m-d', substr((string) $profile['date_of_birth'], 0, 10));
                $birthday = $dt ? $dt->format('m/d/Y') : (string) $profile['date_of_birth'];
            }

            $values = [
                'A' => (string) ($index + 1),
                'B' => strtoupper(trim((string) ($profile['last_name'] ?? ''))),
                'C' => strtoupper(trim((string) ($profile['first_name'] ?? ''))),
                'D' => strtoupper(trim((string) ($profile['middle_name'] ?? ''))),
                'E' => strtoupper(trim((string) ($profile['suffix'] ?? ''))),
                'F' => strtoupper(trim((string) ($profile['gender'] ?? ''))),
                'G' => strtoupper(trim((string) ($profile['civil_status'] ?? ''))),
                'H' => $birthday,
                'I' => $age === '' || $age === null ? '' : (string) $age,
                'J' => strtoupper($purok),
                'K' => strtoupper(trim((string) ($profile['barangay'] ?? ''))),
                'L' => strtoupper(trim((string) ($profile['municipality'] ?? self::MUNICIPALITY))),
                'M' => strtoupper(trim((string) ($profile['province'] ?? self::PROVINCE))),
                'N' => strtoupper(trim((string) ($profile['education_level'] ?? ''))),
                'O' => strtoupper($occupation),
                'P' => strtoupper(trim((string) ($profile['profile_type'] ?? ''))),
            ];

            $xml .= '<row r="' . $rowNum . '" spans="1:16" x14ac:dyDescent="0.3">';
            foreach ($values as $col => $value) {
                $cell = $col . $rowNum;
                if ($value === '') {
                    $xml .= '<c r="' . $cell . '" s="' . $styleIndex . '"/>';
                    continue;
                }
                if ($col === 'A' || $col === 'I') {
                    $xml .= '<c r="' . $cell . '" s="' . $styleIndex . '"><v>' . htmlspecialchars($value, ENT_XML1, 'UTF-8') . '</v></c>';
                    continue;
                }
                $si = $this->addSharedString($strings, $value);
                $xml .= '<c r="' . $cell . '" s="' . $styleIndex . '" t="s"><v>' . $si . '</v></c>';
            }
            $xml .= '</row>';
            $rowNum++;
        }

        return $xml;
    }

    private function injectRows($sheetXml, $rowsXml, $lastRow)
    {
        if ($lastRow < 8) {
            $lastRow = 8;
        }

        $sheetXml = preg_replace(
            '/dimension ref="A1:P8"/',
            'dimension ref="A1:P' . $lastRow . '"',
            $sheetXml,
            1
        );

        $closeTag = '</sheetData>';
        $pos = strrpos($sheetXml, $closeTag);
        if ($pos === false) {
            return $sheetXml;
        }

        return substr($sheetXml, 0, $pos) . $rowsXml . substr($sheetXml, $pos);
    }

    private function ageFromBirthdate($dateOfBirth)
    {
        if (empty($dateOfBirth) || $dateOfBirth === '0000-00-00') {
            return '';
        }
        $birth = DateTime::createFromFormat('Y-m-d', substr((string) $dateOfBirth, 0, 10));
        if (!$birth) {
            return '';
        }
        return $birth->diff(new DateTime('today'))->y;
    }
}
