<?php
session_start();
date_default_timezone_set("Asia/Kolkata");

// Check if seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header("Location: html/sellogin.html");
    exit();
}

// Get current seller info from session
$seller_id    = $_SESSION['seller_id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "auction");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch products for the current seller
$sql = "SELECT * FROM products WHERE seller_id='$seller_id'";
$result = $conn->query($sql);

// Function to calculate product status dynamically
function getProductStatus($conn, $product) {
    // First check admin approval
    if ($product['approval_status'] === 'rejected') {
        return "❌ Rejected by Admin";
    } elseif ($product['approval_status'] === 'pending') {
        return "⏳ Waiting for Approval";
    }

    // If approved, then calculate auction-based status
    $productId   = $product['id'];
    $baseRate    = $product['rate'];
    $auctionDate = $product['date'];
    $t_from      = $product['t_from'];
    $t_to        = $product['t_to'];

    $auction_start = strtotime($auctionDate . " " . $t_from);
    $auction_end   = strtotime($auctionDate . " " . $t_to);
    $now           = time();

    // Count joined bidders
    $joinCheck = $conn->query("SELECT COUNT(*) as total FROM joined_bidders WHERE product_id=$productId");
    $joinedCount = $joinCheck->fetch_assoc()['total'] ?? 0;

    // Get highest bid
    $bidCheck = $conn->query("SELECT MAX(bid_amount) as bid_amount FROM bids WHERE product_id=$productId");
    $highestBid = $bidCheck->fetch_assoc()['bid_amount'] ?? 0;

    // Determine status dynamically
    if ($joinedCount == 0 && $now < $auction_end) {
        return "Pending";
    } elseif ($now < $auction_end) {
        return ($highestBid > $baseRate) ? "Active" : "Pending";
    } else {
        return ($joinedCount > 0 && $highestBid >= $baseRate) ? "Sold" : "Unsold";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Product Status</title>
    <link rel="stylesheet" href="../css/seller_status.css">
</head>
<body>
    <header>
        <div class="logo">RAPPID BID</div>
        <ul class="navlist">
            <li><a href="index.html">home</a></li>
            <li><a href="html/about.html">about</a></li>
            <li><a href="html/contact.html">contact</a></li>
        </ul>
        <div class="nav-right">
            <a href="log/logout.php" class="btn">Logout</a>
        </div>
    </header>

    <h2 style="text-align:center;">Your Product Status</h2>

    <div class="tab">
        <table>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Status</th>
            </tr>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= htmlspecialchars($row['p_name']); ?></td>
                        <td><?= htmlspecialchars($row['category']); ?></td>
                        <td class="status-<?= strtolower(getProductStatus($conn, $row)); ?>">
                            <?= getProductStatus($conn, $row); ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No products added yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <footer>
        <div class="foot">
            <a href="html/selling.html"><-Back</a>
        </div>
    </footer>
</body>
</html>
