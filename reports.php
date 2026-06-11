<?php
include("db_connect.php");
date_default_timezone_set("Asia/Manila");

// Get filter dates from query string
$startDate = $_GET['start_date'] ?? null;
$endDate   = $_GET['end_date'] ?? null;

$whereClause = "";
if ($startDate && $endDate) {
    $whereClause = "WHERE DATE(check_in) BETWEEN '$startDate' AND '$endDate'";
} elseif ($startDate) {
    $whereClause = "WHERE DATE(check_in) >= '$startDate'";
} elseif ($endDate) {
    $whereClause = "WHERE DATE(check_in) <= '$endDate'";
}

// Totals
$totalCustomers = 0; $activeCustomers = 0; $expiredCustomers = 0;

$totalResult = $conn->query("SELECT COUNT(*) AS total FROM sessions $whereClause");
if ($totalResult) $totalCustomers = $totalResult->fetch_assoc()['total'] ?? 0;

$activeResult = $conn->query("SELECT COUNT(*) AS active FROM sessions " .
    ($whereClause ? "$whereClause AND" : "WHERE") . " status='active'");
if ($activeResult) $activeCustomers = $activeResult->fetch_assoc()['active'] ?? 0;

$expiredResult = $conn->query("SELECT COUNT(*) AS expired FROM sessions " .
    ($whereClause ? "$whereClause AND" : "WHERE") . " status!='active'");
if ($expiredResult) $expiredCustomers = $expiredResult->fetch_assoc()['expired'] ?? 0;

// Package breakdown
$packages = []; $counts = [];
$packageResult = $conn->query("SELECT duration, COUNT(*) AS count FROM sessions $whereClause GROUP BY duration");
if ($packageResult) {
    while ($row = $packageResult->fetch_assoc()) {
        $label = ($row['duration'] == 0) ? "Unlimited" : (
            $row['duration']==30 ? "30 mins" :
            ($row['duration']==60 ? "1 hr" :
            ($row['duration']==90 ? "1.5 hrs" :
            ($row['duration']==120 ? "2 hrs" : $row['duration']." mins")))
        );
        $packages[] = $label;
        $counts[] = $row['count'];
    }
}

// Build response
$response = [
    "packages" => $packages,
    "counts" => $counts,
    "totalCustomers" => $totalCustomers,
    "activeCustomers" => $activeCustomers,
    "expiredCustomers" => $expiredCustomers
];

// Return JSON
header('Content-Type: application/json');
echo json_encode($response);
?>

