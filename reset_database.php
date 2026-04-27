<?php
// Reset database completely and reload with correct data
$conn = new mysqli('localhost', 'root', '', '', 3306);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Dropping existing database...\n";
$conn->query("DROP DATABASE IF EXISTS municipal_kk_profiling");
echo "✓ Database dropped\n\n";

// Run the setup script
echo "Running setup script...\n";
system('php c:/xampp/htdocs/osy_db/setup_kk_system.php');

$conn->close();
