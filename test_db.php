<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=municipal_kk_profiling', 'root', '');
    echo "MySQL is running and database exists!";
} catch (Exception $e) {
    echo "MySQL connection failed: " . $e->getMessage();
}
