<?php
// Database connection using PDO for PostgreSQL
date_default_timezone_set("Asia/Manila");

$host     = "dpg-d8linii8qa3s73d195f0-a.render-database.com"; 
$port     = "5432";
$dbname   = "customer_session_db"; 
$user     = "customer_session_db_user";
$password = "1fbkfVelQB8XGKrm7jBdOEqzfXL7249v";
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional: remove or comment out this line in production
    // echo "✅ PostgreSQL connection successful!";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>



