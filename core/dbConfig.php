<?php
// core/dbConfig.php
// Creates the PDO connection. Every page requires this file before anything else.

// The session is used only for one-time messages and form errors.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Connection settings (XAMPP defaults) ---
$host    = 'localhost';
$dbname  = 'swe_job_applications_db';
$dbUser  = 'root';
$dbPass  = '';        // XAMPP's root user has no password by default
$charset = 'utf8mb4';

// Optional override: if core/config.local.php exists, it replaces the values above.
// That file is git-ignored, so real credentials never reach GitHub.
$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require $localConfig;
}

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // SQL problems throw exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows come back keyed by column name
    PDO::ATTR_EMULATE_PREPARES   => false,                  // real prepared statements
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // Technical details go to the Apache error log, not to the visitor.
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check that MySQL is running in XAMPP and that schema.sql has been imported.');
}
