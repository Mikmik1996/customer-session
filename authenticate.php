<?php
session_start();
$conn = new mysqli("localhost", "root", "", "wiijump_db");
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

$user = $_POST['username'];
$pass = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM staff WHERE username=? AND password=MD5(?)");
$stmt->bind_param("ss", $user, $pass);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // ✅ Successful login
    $_SESSION['staff'] = $row['username'];
    $_SESSION['role'] = $row['role'];
    header("Location: index.php");
    exit;
} else {
    // ❌ Failed login → redirect back with error flag
    header("Location: login.php?error=1");
    exit;
}

$conn->close();
?>
