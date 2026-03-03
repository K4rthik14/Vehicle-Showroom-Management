<?php
session_start();
define('BASE_URL', '/vsms/');
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'Dashboard';

// Stats
$totalVehicles  = $conn->query("SELECT COUNT(*) FROM VEHICLE")->fetch_row()[0];
$availVehicles  = $conn->query("SELECT COUNT(*) FROM VEHICLE WHERE Status='Available'")->fetch_row()[0];
$totalCustomers = $conn->query("SELECT COUNT(*) FROM CUSTOMER")->fetch_row()[0];
$totalSales     = $conn->query("SELECT COUNT(*) FROM SALE")->fetch_row()[0];
$totalRevenue   = $conn->query("SELECT COALESCE(SUM(Amount),0) FROM SALE")->fetch_row()[0];
$totalStaff     = $conn->query("SELECT COUNT(*) FROM STAFF")->fetch_row()[0];

// Recent sales
$recentSales = $conn->query("
    SELECT s.Sale_ID, s.Sale_Date, s.Amount, s.Payment_Method,
           CONCAT(c.Fname,' ',c.Lname) AS customer,
           v.Model_Name
    FROM SALE s
    JOIN CUSTOMER c ON s.Customer_ID = c.Customer_ID
    JOIN VEHICLE v ON s.VIN = v.VIN
    ORDER BY s.Sale_Date DESC LIMIT 5
");

require_once '../includes/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;">
            <div class="stat-icon" style="background:rgba(255,255,255,0.2);">🚗</div>
            <div class="stat-val"><?= $totalVehicles ?></div>
            <div class="stat-label">Total Vehicles</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;">
            <div class="stat-icon" style="background:rgba(255,255,255,0.2);">✅</div>
            <div class="stat-val"><?= $availVehicles ?></div>
            <div class="stat-label">Available Now</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;">
            <div class="stat-icon" style="background:rgba(255,255,255,0.2);">👥</div>
            <div class="stat-val"><?= $totalCustomers ?></div>
            <div class="stat-label">Customers</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#06b6d4,#0891b2);color:#fff;">
            <div class="stat-icon" style="background:rgba(255,255,255,0.2);">💰</div>
            <div class="stat-val">₹<?= number_format($totalRevenue/100000, 1) ?>L</div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-vsms">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi-receipt me-2 text-primary"></i>Recent Sales</span>
                <a href="sales.php" class="btn btn-sm btn-primary-vsms">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if ($recentSales->num_rows > 0): ?>
                <table class="table table-vsms mb-0">
                    <thead><tr>
                        <th>Sale ID</th><th>Customer</th><th>Vehicle</th><th>Date</th><th>Amount</th><th>Payment</th>
                    </tr></thead>
                    <tbody>
                    <?php while ($row = $recentSales->fetch_assoc()): ?>
                        <tr>
                            <td><span style="font-family:'DM Mono',monospace;color:#6366f1;">#<?= $row['Sale_ID'] ?></span></td>
                            <td><?= htmlspecialchars($row['customer']) ?></td>
                            <td><?= htmlspecialchars($row['Model_Name']) ?></td>
                            <td><?= date('d M Y', strtotime($row['Sale_Date'])) ?></td>
                            <td><strong>₹<?= number_format($row['Amount']) ?></strong></td>
                            <td><span class="badge badge-<?= strtolower($row['Payment_Method']) ?>"><?= $row['Payment_Method'] ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty-state"><i class="bi-receipt"></i><p class="mt-2">No sales yet.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-vsms h-100">
            <div class="card-header"><i class="bi-bar-chart me-2 text-primary"></i>Quick Stats</div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted" style="font-size:.85rem;">Total Sales</span>
                    <strong><?= $totalSales ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted" style="font-size:.85rem;">Staff Members</span>
                    <strong><?= $totalStaff ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted" style="font-size:.85rem;">Vehicles Sold</span>
                    <strong><?= $totalVehicles - $availVehicles ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted" style="font-size:.85rem;">Avg Sale Value</span>
                    <strong>₹<?= $totalSales ? number_format($totalRevenue/$totalSales) : '0' ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
