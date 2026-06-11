<?php
include("db_connect.php");
date_default_timezone_set("Asia/Manila");


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


$statusFilter = ($whereClause ? "$whereClause AND" : "WHERE") . " (status='active' OR status='expired' OR status='archived')";

$query = "SELECT customer_name, contact, duration, check_in, check_out, status 
          FROM sessions $statusFilter 
          ORDER BY check_in DESC";

$result = $conn->query($query);


header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=session_data.csv');


$output = fopen('php://output', 'w');


fputcsv($output, ['Date', 'Name', 'Contact', 'Duration', 'Check In', 'Check Out', 'Status']);


if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $durationLabel = ($row['duration'] == 0) ? "Unlimited" : $row['duration']." mins";


        $dateCol   = date("Y-m-d", strtotime($row['check_in']));
        $checkIn   = date("h:i A", strtotime($row['check_in']));
        $checkOut  = $row['check_out'] ? date("h:i A", strtotime($row['check_out'])) : "";

        fputcsv($output, [
            $dateCol,
            $row['customer_name'],
            $row['contact'],
            $durationLabel,
            $checkIn,
            $checkOut,
            $row['status']
        ]);
    }
}

fclose($output);
exit();
?>
