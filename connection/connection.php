<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Manila');

try {
    // Database credentials
    $serverName = "DESKTOP-SQ1PDO1";       // Your local SQL Server hostname (e.g., ".", "localhost", or "J5L")
    $database = "faconnect_db"; // Your database name
    $username = "sa";           // SQL Server username
    $password = "root12345";    // SQL Server password

    // Connection options for SQL Server Authentication
    $connectionOptions = [
        "Database" => $database,
        "UID" => $username,       // Username
        "PWD" => $password,       // Password
        "TrustServerCertificate" => true, // Trust server certificate
    ];

    // Establish the connection
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    // Check if the connection was successful
    if ($conn === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    // Success message
    // echo "Connection established successfully!<br>";

} catch (Exception $e) {
    // Log the error and show a user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}
?>
