<?php
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

// Create connection
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$pass");

// Check connection
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
?>

