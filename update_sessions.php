<?php
$files = glob('c:/xampp/htdocs/osy_db/api/get_*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'session_write_close()') === false) {
        $content = preg_replace(
            '/(\$userId\s*=\s*\$_SESSION\[\'user_id\'\]\s*\?\?\s*0;)/',
            "$1\n\nsession_write_close(); // Release session lock for parallel AJAX requests\n",
            $content
        );
        file_put_contents($file, $content);
        echo 'Updated: ' . basename($file) . "\n";
    }
}
