<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Manila');

try {
    // Database credentials
    $serverName = "f-connect.database.windows.net"; // Change this when deploying (use actual Azure SQL Server name)
    $database = "faconnect_db";
    $username = "fconnect"; 
    $password = "CSTA_2025";

    // Connection options for SQLSRV
    $conn = sqlsrv_connect($serverName, [
        "Database" => $database,
        "UID" => $username,
        "PWD" => $password,
        "TrustServerCertificate" => true
    ]);

    if ($conn === false) {
        throw new Exception(json_encode(sqlsrv_errors())); // Better error handling
    }

} catch (Exception $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}
?>
