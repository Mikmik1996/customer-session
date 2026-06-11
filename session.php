<?php
session_start();
include("db_connect.php");
date_default_timezone_set("Asia/Manila");

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // ✅ Add new session (Customer Registration)
    if ($action === 'checkin') {
        $name     = $conn->real_escape_string($_POST['customer_name']);
        $contact  = $conn->real_escape_string($_POST['contact']);
        $duration = (int)$_POST['duration'];
        $checkIn  = date("Y-m-d H:i:s");

        // Calculate check_out based on duration (minutes)
        if ($duration > 0) {
            $checkOut = date("Y-m-d H:i:s", strtotime("+$duration minutes", strtotime($checkIn)));
        } else {
            $checkOut = null; // unlimited session
        }

        $conn->query("INSERT INTO sessions (customer_name, contact, duration, check_in, check_out, status) 
                      VALUES ('$name', '$contact', '$duration', '$checkIn', " . 
                      ($checkOut ? "'$checkOut'" : "NULL") . ", 'active')");

        // ✅ Redirect back to Customer Registration tab with success flag
        header("Location: index.php?tab=registration&success=1");
        exit();
    }

    // ✅ Remove a single session (Dashboard “Remove” button archives row)
    if ($action === 'remove' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];

        // Archive instead of delete
        $conn->query("UPDATE sessions SET status='archived' WHERE id=$id");

        header("Location: index.php?tab=dashboard");
        exit();
    }

    // ✅ Clear all expired sessions (archive instead of delete)
    if ($action === 'clear_expired') {
        $now = time();

        // Archive sessions that are expired by duration (same logic as Dashboard)
        $conn->query("UPDATE sessions 
                      SET status='archived' 
                      WHERE duration > 0 
                        AND UNIX_TIMESTAMP(check_in) + (duration * 60) < $now");

        header("Location: index.php?tab=dashboard");
        exit();
    }
}
?>



