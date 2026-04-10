<?php
// Database configuration - Replace with your actual credentials
$host = 'YOUR_DB_HOST'; // e.g., 'localhost'
$dbname = 'YOUR_DB_NAME';
$username = 'YOUR_DB_USERNAME';
$password = 'YOUR_DB_PASSWORD';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get POST data
$postcode = $_POST['postcode'] ?? '';
$huisnummer = $_POST['huisnummer'] ?? '';
$toevoeging = $_POST['toevoeging'] ?? '';
$zonnepanelen = isset($_POST['zonnepanelen']) ? 1 : 0;
$preset = $_POST['preset'] ?? null;
$verbruik = (int)($_POST['verbruik'] ?? 0);
$opwek = (int)($_POST['opwek'] ?? 0);

// Determine data source
$data_source = isset($_POST['preset']) ? 'preset' : 'custom';

// Prepare and execute insert
$stmt = $pdo->prepare("INSERT INTO household_data (postcode, huisnummer, toevoeging, zonnepanelen, preset, verbruik, opwek, data_source, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->execute([$postcode, $huisnummer, $toevoeging, $zonnepanelen, $preset, $verbruik, $opwek, $data_source]);

echo "Data submitted successfully.";
?></content>
<parameter name="filePath">c:\Users\AveryVermaasStichtin\OneDrive - Stichting Oranje Advies\Documents\Rekentool\rekentoolbewoner\submit_form.php