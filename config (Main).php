<?php
require_once __DIR__ . '/vendor/autoload.php'; // Correct path to autoload.php

// Load .env variables from the root directory (Capstone)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); // Path to the root of Capstone
$dotenv->load();

// Check if the environment variables are loaded correctly
if (!isset($_ENV['DB_SERVERNAME'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME'])) {
    die('Environment variables not set correctly.');
}

// Get database credentials from .env
$servername = $_ENV['DB_SERVERNAME'];
$dbuser = $_ENV['DB_USERNAME'];
$dbpass = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];

// Create a new database connection
$conn = new mysqli($servername, $dbuser, $dbpass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
