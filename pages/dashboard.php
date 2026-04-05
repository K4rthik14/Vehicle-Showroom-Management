<?php
session_start();
define('BASE_URL', '/vsms/');
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'Dashboard';

// -------------------------
// Query dashboard statistics
// -------------------------
$totalVehicles = (int)$conn->query("SELECT COUNT(*) FROM VEHICLE")->fetch_row()[0];
$availableVehicles = (int)$conn->query("SELECT COUNT(*) FROM VEHICLE WHERE Status='Available'")->fetch_row()[0];
$totalCustomers = (int)$conn->query("SELECT COUNT(*) FROM CUSTOMER")->fetch_row()[0];
$totalSales = (int)$conn->query("SELECT COUNT(*) FROM SALE")->fetch_row()[0];
$totalRevenue = (float)$conn->query("SELECT COALESCE(SUM(Amount),0) FROM SALE")->fetch_row()[0];
$totalStaff = (int)$conn->query("SELECT COUNT(*) FROM STAFF")->fetch_row()[0];

$recentSales = $conn->query("
    SELECT 
        s.Sale_ID,
        s.Sale_Date,
        s.Amount,
        s.Payment_Method,
        CONCAT(c.Fname, ' ', c.Lname) AS customer,
        v.Model_Name
    FROM SALE s
    JOIN CUSTOMER c ON s.Customer_ID = c.Customer_ID
    JOIN VEHICLE v ON s.VIN = v.VIN
    ORDER BY s.Sale_Date DESC
    LIMIT 5
");

$summaryCards = [
    [
        'icon' => '🚗',
        'label' => 'Total Vehicles',
        'value' => $totalVehicles,
        'color' => '#2563eb',
        'bg' => '#eff6ff',
    ],
    [
        'icon' => '✅',
        'label' => 'Available Now',
        'value' => $availableVehicles,
        'color' => '#16a34a',
        'bg' => '#dcfce7',
    ],
    [
        'icon' => '👥',
        'label' => 'Customers',
        'value' => $totalCustomers,
        'color' => '#0284c7',
        'bg' => '#e0f2fe',
    ],
    [
        'icon' => '💰',
        'label' => 'Total Revenue',
        'value' => '₹' . number_format($totalRevenue),
        'color' => '#d97706',
        'bg' => '#fef3c7',
    ],
];

require_once '../includes/header.php';
?>

<div class="row g-3 mb-4">
    <?php foreach ($summaryCards as $card): ?>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="border-left-color: <?= $card['color']?>;">
            <div class="stat-icon" style="color: <?= $card['color']?>; background: <?= $card['bg']?>;">
                <?= $card['icon']?>
            </div>
            <div class="stat-content">
                <div class="stat-val">
                    <?= $card['value']?>
                </div>
                <div class="stat-label">
                    <?= $card['label']?>
                </div>
            </div>
        </div>
    </div>
    <?php
endforeach; ?>
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
                    <thead>
                        <tr>
                            <th>Sale ID</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($sale = $recentSales->fetch_assoc()): ?>
                        <tr>
                            <td><span style="font-family:'DM Mono',monospace;color:#0f766e;">#
                                    <?= $sale['Sale_ID']?>
                                </span></td>
                            <td>
                                <?= htmlspecialchars($sale['customer'])?>
                            </td>
                            <td>
                                <?= htmlspecialchars($sale['Model_Name'])?>
                            </td>
                            <td>
                                <?= date('d M Y', strtotime($sale['Sale_Date']))?>
                            </td>
                            <td><strong>₹
                                    <?= number_format($sale['Amount'])?>
                                </strong></td>
                            <td><span class="badge badge-<?= strtolower($sale['Payment_Method'])?>">
                                    <?= $sale['Payment_Method']?>
                                </span></td>
                        </tr>
                        <?php
    endwhile; ?>
                    </tbody>
                </table>
                <?php
else: ?>
                <div class="empty-state"><i class="bi-receipt"></i>
                    <p class="mt-2">No sales yet.</p>
                </div>
                <?php
endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-vsms h-100">
            <div class="card-header"><i class="bi-bar-chart me-2 text-primary"></i>Quick Stats</div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted" style="font-size:.85rem;">Total Sales</span>
                    <strong>
                        <?= $totalSales?>
                    </strong>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted" style="font-size:.85rem;">Staff Members</span>
                    <strong>
                        <?= $totalStaff?>
                    </strong>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted" style="font-size:.85rem;">Vehicles Sold</span>
                    <strong>
                        <?= $totalVehicles - $availableVehicles?>
                    </strong>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted" style="font-size:.85rem;">Average Sale Value</span>
                    <strong>₹
                        <?= $totalSales ? number_format($totalRevenue / $totalSales) : '0'?>
                    </strong>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>