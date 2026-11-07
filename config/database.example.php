<?php
// Copy this file to config/database.php and fill in your own details.
// config/database.php is ignored by Git so your real credentials are never published.

$host     = "localhost";          // on shared hosting, use the MySQL hostname your host gives you
$username = "root";               // your MySQL username
$password = "";                   // your MySQL password
$database = "el_gato_academy";    // your database name

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
