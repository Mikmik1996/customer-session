<?php
session_start();
include("db_connect.php");
date_default_timezone_set("Asia/Manila");

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // ✅ Add new session (Customer Registration)
    if ($action === 'checkin') {
        $name     = $_POST['customer_name'];
        $contact  = $_POST['contact'];
        $duration = (int)$_POST['duration'];
        $checkIn  = date("Y-m-d H:i:s");

        // Calculate check_out based on duration (minutes)
        if ($duration > 0) {
            $checkOut = date("Y-m-d H:i:s", strtotime("+$duration minutes", strtotime($checkIn)));
        } else {
            $checkOut = null; // unlimited session
        }

        // Insert using PDO prepared statement
        $stmt = $conn->prepare("INSERT INTO sessions 
            (customer_name, contact, duration, check_in, check_out, status) 
            VALUES (:name, :contact, :duration, :check_in, :check_out, 'active')");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':contact', $contact);
        $stmt->bindParam(':duration', $duration);
        $stmt->bindParam(':check_in', $checkIn);
        $stmt->bindParam(':check_out', $checkOut);

        $stmt->execute();

        // ✅ Redirect back to Registration tab with success flag
        header("Location: index.php?tab=registration&success=1");
        exit();
    }

    // ✅ Remove a single session (Dashboard “Remove” button archives row)
    if ($action === 'remove' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];

        $stmt = $conn->prepare("UPDATE sessions SET status='archived' WHERE id=:id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header("Location: index.php?tab=dashboard");
        exit();
    }

    // ✅ Clear all expired sessions (archive instead of delete)
    if ($action === 'clear_expired') {
        $now = time();

        // Archive sessions that are expired by duration
        $stmt = $conn->prepare("UPDATE sessions 
            SET status='archived' 
            WHERE duration > 0 
              AND EXTRACT(EPOCH FROM check_in) + (duration * 60) < :now");
        $stmt->bindParam(':now', $now);
        $stmt->execute();

        header("Location: index.php?tab=dashboard");
        exit();
    }
}
?>




