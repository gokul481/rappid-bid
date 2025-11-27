<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo "<script>alert('⚠️ You must be logged in to view this page.'); window.location='login.php';</script>";
    exit();
}

$email = $_SESSION['email'];

date_default_timezone_set("Asia/Kolkata");

// Database connection
$conn = new mysqli("localhost", "root", "", "auction");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch all products won by this user
$sql = "
    SELECT p.id, p.p_name, p.category, w.winning_bid
    FROM products p
    JOIN winners w ON p.id = w.product_id
    WHERE w.winner_name = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Winning Status</title>
<link rel="stylesheet" href="css/winner_status.css">
</head>
<body>

<h2 style="text-align:center;margin-top:50px;">🏆 Your Winning Status</h2>

<div class="tab">
    <table cellpadding="10" cellspacing="5" style="margin:auto;">
        <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Your Winning Bid</th>
            <th>Status</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['p_name']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['winning_bid']) ?></td>
                    <td style="color:green;font-weight:bold;">You Won 🎉</td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align:center; color:black;">❌ You have not won any auctions yet.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<footer>
    <div class="foot">
        <a href="buyer/buying.php"><- Back</a>
    </div>
</footer>

</body>
</html>
<?php $conn->close(); ?>
