<?php
session_start();


// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "auction";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// --- USER MANAGEMENT ---
$users = $conn->query("SELECT * FROM users ORDER BY id ASC");

// --- AUCTION MANAGEMENT ---
$auctions = $conn->query("
    SELECT p.id, p.p_name, p.category, p.approval_status, u.fname, u.lname 
    FROM products p
    LEFT JOIN users u ON p.seller_id = u.id
    ORDER BY p.id ASC
");


// --- BID MONITORING ---
$bids = $conn->query("
    SELECT b.id, b.bid_amount, u.fname, u.lname, p.p_name 
    FROM bids b
    JOIN users u ON b.id = u.id
    JOIN products p ON b.product_id = p.id
    ORDER BY b.id ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="css/admindashboard.css">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<header>
    <div class="bx bx-menu" id="menu-icon"></div>
    <div class="logo">RAPPID BID - Admin</div>
    <ul class="navlist">
        <li><a href="#users">Users</a></li>
        <li><a href="#auctions">Auctions</a></li>
        <li><a href="#bids">Bids</a></li>
    </ul>


      <div class="nav-right">
        <a href="log/logout.php" class="btn">Logout</a>
    </div>
</header>

<div class="container">

    <!-- USER MANAGEMENT -->
    <h2 id="users">User Management</h2>
    <table>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Action</th></tr>
        <?php while($u = $users->fetch_assoc()): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['fname']." ".$u['lname']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['phone']) ?></td>
            <td>
                <a class="btn" href="admin/delete_user.php?id=<?= $u['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- AUCTION MANAGEMENT -->
    <h2 id="auctions">Auction Management</h2>
    <table>
        <tr><th>ID</th><th>Product</th><th>Category</th><th>Seller</th><th>Status</th><th>Actions</th></tr>
        <?php while($a = $auctions->fetch_assoc()): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['p_name']) ?></td>
            <td><?= htmlspecialchars($a['category']) ?></td>
            <td><?= htmlspecialchars($a['fname']." ".$a['lname']) ?></td>
            <td><?= htmlspecialchars($a['approval_status']) ?></td>
            <td>
                <a class="btn" href="admin/approve_auction.php?id=<?= $a['id'] ?>">Approve</a>
                <a class="btn" href="admin/reject_auction.php?id=<?= $a['id'] ?>">Reject</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

   

    <!-- BID MONITORING -->
    <h2 id="bids">Bid Monitoring</h2>
    <table>
        <tr><th>Bid ID</th><th>Bidder</th><th>Product</th><th>Amount</th></tr>
        <?php while($b = $bids->fetch_assoc()): ?>
        <tr>
            <td><?= $b['id'] ?></td>
            <td><?= htmlspecialchars($b['fname']." ".$b['lname']) ?></td>
            <td><?= htmlspecialchars($b['p_name']) ?></td>
            <td><?= htmlspecialchars($b['bid_amount']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

</div>

<footer>
    <div class="foot">
        <a href="index.html"><- Back to Home</a>
    </div>
</footer>

<script>
let menu=document.querySelector('#menu-icon');
let navlist=document.querySelector('.navlist');
menu.onclick=()=>{
    menu.classList.toggle('bx-x');
    navlist.classList.toggle('open');
}
</script>

</body>
</html>
<?php $conn->close(); ?>
