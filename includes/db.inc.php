<?php
// Define the connection constants.
DEFINE ("DB_HOST", "localhost");
DEFINE ("DB_USER", ""); // use your DB username
DEFINE ("DB_PASSWORD", ""); // use your DB password
DEFINE ("DB_NAME", "mini_blog");

// Create the connection.
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check the connection.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the encoding.
$conn->set_charset("utf8");