<?php
session_start();
include("db_connect.php");
date_default_timezone_set("Asia/Manila");

// Capture active tab
$activeTab = $_GET['tab'] ?? 'registration';

// Filters
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

// Totals
$totalCustomers = 0; $activeCustomers = 0; $expiredCustomers = 0;
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM sessions $statusFilter");
if ($totalResult) $totalCustomers = $totalResult->fetch_assoc()['total'] ?? 0;

$activeResult = $conn->query("SELECT COUNT(*) AS active FROM sessions " .
    ($whereClause ? "$whereClause AND" : "WHERE") . " status='active'");
if ($activeResult) $activeCustomers = $activeResult->fetch_assoc()['active'] ?? 0;

$expiredResult = $conn->query("SELECT COUNT(*) AS expired FROM sessions " .
    ($whereClause ? "$whereClause AND" : "WHERE") . " (status='expired' OR status='archived')");
if ($expiredResult) $expiredCustomers = $expiredResult->fetch_assoc()['expired'] ?? 0;

// Breakdown
$packages = []; $counts = [];
$packageResult = $conn->query("SELECT duration, COUNT(*) AS count FROM sessions $statusFilter GROUP BY duration");
if ($packageResult) {
    while ($row = $packageResult->fetch_assoc()) {
        $label = ($row['duration'] == 0) ? "Unlimited" : ($row['duration']==30 ? "30 mins" :
                 ($row['duration']==60 ? "1 hr" : ($row['duration']==90 ? "1.5 hrs" :
                 ($row['duration']==120 ? "2 hrs" : $row['duration']." mins"))));
        $packages[] = $label; $counts[] = $row['count'];
    }
}

// Dashboard
$dashboardResult = $conn->query("SELECT id, customer_name, contact, duration, check_in, check_out, status 
                                 FROM sessions 
                                 WHERE status IN ('active','expired')
                                 ORDER BY check_in DESC");


?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Session Tracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>


.registration-card .form-group input,
.registration-card .form-group select {
    width: 90%;     padding: 14px;    border: 1px solid #ccc;
    border-radius: 8px;
    text-align: center;
    font-size: 16px; 
    box-sizing: border-box; 
}


.registration-card .form-group {
    margin-bottom: 22px;
    display: flex;
    flex-direction: column;
    align-items: center;
}


.registration-card .form-group label {
    font-weight: 700;
    font-size: 16px;
    color: #007BFF; 
    margin-bottom: 10px;
}


.clear-expired-container {
    text-align: center;    padding: 15px 0;
}

.clear-expired-container .btn {
    display: inline-block;}


.reports-header {
    position: sticky;
    top: 0;
    background: #fff;
    padding: 10px;
    margin: 0;
    text-align: center;
    font-size: 1.5em;
    color: #007BFF;
    border-bottom: 2px solid #007BFF;
    z-index: 10;
}

        body { font-family: 'Segoe UI', Arial, sans-serif; margin:0; padding:0; display:flex; background:#f4f6f9; }
        .sidebar { width:220px; background:#007BFF; color:#fff; height:100vh; padding-top:30px; position:fixed; }
        .sidebar a { display:block; padding:12px 20px; color:#fff; text-decoration:none; font-weight:bold; margin-bottom:5px; }
        .sidebar a:hover, .sidebar a.active { background:#0056b3; }
        .main { margin-left:220px; padding:30px; flex:1; }
        h2 { margin-bottom:15px; color:#007BFF; text-align:center; }
        .tab-content { display:none; }
        .tab-content.active { display:block; }
        .btn { padding:8px 14px; cursor:pointer; background:#007BFF; color:#fff; border:none; border-radius:6px; }
        .btn:hover { background:#0056b3; }
        table { width:100%; border-collapse:collapse; margin-top:15px; }
        th, td { border:1px solid #ccc; padding:10px; text-align:center; }
        th { background:#007BFF; color:#fff; }
        .expired { background-color:#f8d7da; color:#721c24; }
        .registration-card { max-width:400px; margin:40px auto; background:#fff; padding:40px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.15); text-align:center; }
        .registration-card h2 { margin-top:0; margin-bottom:20px; color:#007BFF; font-size:1.4em; }
        .registration-card .form-group { margin-bottom:20px; display:flex; flex-direction:column; align-items:center; }
        .registration-card .form-group label { margin-bottom:8px; font-weight:bold; color:#333; text-align:center; }
        .registration-card .form-group input,
        .registration-card .form-group select { width:80%; padding:8px; border:1px solid #ccc; border-radius:6px; text-align:center; }
        .filter-btn, .download-btn { background:none; color:#007BFF; border:none; font-weight:bold; cursor:pointer; text-decoration:underline; }
        .filter-btn:hover, .download-btn:hover { text-decoration:none; color:#0056b3; }


.registration-card {
    max-width: 420px;
    margin: 50px auto;
    background: #fff;
    padding: 50px; /* spacious padding */
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    text-align: center;
    animation: fadeIn 0.6s ease;
}


.registration-card h2 {
    color: #007BFF;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-bottom: 25px;
}


.form-group label {
    font-weight: 600;
    font-size: 15px;
    color: #333;
    display: block;
    margin-bottom: 8px;
}


.form-group input,
.form-group select {
    width: 80%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    text-align: center;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #007BFF;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    outline: none;
}


.form-group {
    margin-bottom: 20px;
}


.btn {
    margin-top: 15px;
    padding: 12px 24px;
    background-color: #007BFF;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.btn:hover {
    background-color: #0056b3;
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

body {
    background: linear-gradient(135deg, #e8f0ff, #f8fbff);
}


    </style>
</head>
<body>
    <div class="sidebar">
        <a href="index.php?tab=registration" class="<?= $activeTab=='registration' ? 'active' : '' ?>">Customer Registration</a>
        <a href="index.php?tab=dashboard" class="<?= $activeTab=='dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="index.php?tab=reports" class="<?= $activeTab=='reports' ? 'active' : '' ?>">Reports</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">
        <!-- Registration -->
<div id="registration" class="tab-content <?= $activeTab=='registration' ? 'active' : '' ?>">
    <div class="registration-card">
        <h2>Customer Registration</h2>

        <!-- ✅ Success message -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div id="success-message" 
                 style="background:#e8f0ff; color:#007BFF; padding:10px; border-radius:5px; margin-bottom:15px;">
                ✅ Session added successfully!
            </div>
        <?php endif; ?>

        <form action="session.php" method="POST">
            <input type="hidden" name="action" value="checkin">
            <div class="form-group">
                <label for="customer_name">Name:</label>
                <input type="text" id="customer_name" name="customer_name" required>
            </div>
            <div class="form-group">
                <label for="contact">Contact:</label>
                <input type="text" id="contact" name="contact">
            </div>
            <div class="form-group">
                <label for="duration">Package:</label>
                <select id="duration" name="duration" required>
                    <option value="30">30 mins</option>
                    <option value="60">1 hr</option>
                    <option value="90">1.5 hrs</option>
                    <option value="120">2 hrs</option>
                    <option value="0">Unlimited</option>
                </select>
            </div>
            <button type="submit" class="btn">Add Session</button>
        </form>
    </div>
</div>


<!-- Dashboard -->
<div id="dashboard" class="tab-content <?= $activeTab=='dashboard' ? 'active' : '' ?>">
    <div class="card-container">
        <h2>Dashboard</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Package</th>
                <th>Start</th>
                <th>End</th>
                <th>Time Remaining</th>
                <th>Action</th>
            </tr>
            <?php 
                $dashboardResult = $conn->query("SELECT * FROM sessions WHERE status!='archived' ORDER BY id DESC");
                while ($row = $dashboardResult->fetch_assoc()): 
                    $start = strtotime($row['check_in']);
                    $end   = ($row['duration'] == 0) ? null : $start + ($row['duration'] * 60);
            ?>
            <tr class="<?= ($end && $end < time()) ? 'expired' : '' ?>">
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= $row['duration'] == 0 ? "Unlimited" : $row['duration']." mins" ?></td>
                <td><?= date("h:i A", $start) ?></td>
                <td><?= $end ? date("h:i A", $end) : "Unlimited" ?></td>
                <td class="remaining" 
                    data-end="<?= $end ? $end : '' ?>" 
                    data-duration="<?= $row['duration'] ?>">
                    <?= $row['duration'] == 0 ? "Unlimited" : (($end && $end < time()) ? "Expired" : "") ?>
                </td>
                <td>
                    <form action="session.php" method="POST">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <!-- ✅ Center-aligned Clear All Expired Button -->
        <div class="clear-expired-container">
            <form action="session.php" method="POST">
                <input type="hidden" name="action" value="clear_expired">
                <button type="submit" class="btn">Clear All Expired</button>
            </form>
        </div>
    </div>
</div>

<!-- ✅ JavaScript live countdown -->
<script>
function updateCountdowns() {
    const now = Math.floor(Date.now() / 1000); // current time in seconds
    document.querySelectorAll('.remaining').forEach(cell => {
        const end = parseInt(cell.dataset.end);
        const duration = parseInt(cell.dataset.duration);

        if (duration === 0) {
            cell.textContent = "Unlimited";
            return;
        }
        if (!end || end < now) {
            cell.textContent = "Expired";
            cell.parentElement.classList.add('expired');
            return;
        }

        const diff = end - now;
        const hours = Math.floor(diff / 3600);
        const minutes = Math.floor((diff % 3600) / 60);
        const seconds = diff % 60;

        let text = "";
        if (hours > 0) text += hours + " hr ";
        if (minutes > 0 || hours > 0) text += minutes + " min ";
        text += seconds + " sec";

        cell.textContent = text;
    });
}

// Run immediately and every second
updateCountdowns();
setInterval(updateCountdowns, 1000);
</script>

               <!-- Reports -->
<div id="reports" class="tab-content <?= $activeTab=='reports' ? 'active' : '' ?>">
    <div class="card-container">
        <h2 class="reports-header">Reports</h2>
        <form method="GET" action="index.php">
            <input type="hidden" name="tab" value="reports">
            <label>Start Date:</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate ?? '') ?>">
            <label>End Date:</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate ?? '') ?>">
            <button type="submit" class="filter-btn">Filter</button>
        </form>

        <!-- Chart -->
        <canvas id="packageChart"></canvas>
        <script>
            const ctx = document.getElementById('packageChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($packages) ?>,
                    datasets: [{
                        label: 'Number of Customers',
                        data: <?= json_encode($counts) ?>,
                        backgroundColor: '#007BFF'
                    }]
                },
                options: { 
                    responsive:true, 
                    plugins:{ 
                        legend:{display:false}, 
                        title:{display:true,text:'Customers by Package'} 
                    }
                }
            });
        </script>

        <!-- Download link -->
        <div style="text-align:center; margin-top:20px;">
            <a href="download.php?start_date=<?= urlencode($startDate ?? '') ?>&end_date=<?= urlencode($endDate ?? '') ?>" class="download-btn">Download Data</a>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const msg = document.getElementById("success-message");
    if (msg) {
        setTimeout(() => {
            msg.style.transition = "opacity 1s ease";
            msg.style.opacity = "0";
            setTimeout(() => msg.remove(), 1000); // remove after fade
        }, 3000); // show for 3 seconds before fading
    }
});
</script>


</body>
</html>
