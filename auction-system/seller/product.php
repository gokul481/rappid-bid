<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo "<script>alert('⚠️ You must be logged in to view this product.'); window.location='../buyer/login.php';</script>";
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "auction";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Get logged-in user info
$bidder_email = $_SESSION['email'];
$sqlUser = "SELECT * FROM users WHERE email=?";
$stmt = mysqli_prepare($conn, $sqlUser);
mysqli_stmt_bind_param($stmt, "s", $bidder_email);
mysqli_stmt_execute($stmt);
$resultUser = mysqli_stmt_get_result($stmt);
$bidder = mysqli_fetch_assoc($resultUser);

if (!$bidder) {
    echo "<script>alert('User not found.'); window.location='login.php';</script>";
    exit();
}

// Handle Join to Bid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_bid'])) {
    $product_id = intval($_POST['product_id']);

    // Prevent duplicate join
    $check = "SELECT * FROM joined_bidders WHERE product_id=? AND bidder_name=?";
    $stmt = mysqli_prepare($conn, $check);
    mysqli_stmt_bind_param($stmt, "is", $product_id, $bidder_email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) == 0) {
        $sqlInsert = "INSERT INTO joined_bidders (product_id, bidder_name) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sqlInsert);
        mysqli_stmt_bind_param($stmt, "is", $product_id, $bidder_email);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('✅ You joined this bid successfully!'); window.location.href='../buyer/bid.php?product_id=$product_id';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Error joining bid.');</script>";
        }
    } else {
        echo "<script>alert('⚠️ You already joined this bid.'); window.location.href='../buyer/bid.php?product_id=$product_id';</script>";
        exit;
    }
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Fetch product
$sqlProduct = "SELECT * FROM products WHERE id=?";
$stmt = mysqli_prepare($conn, $sqlProduct);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$resultProduct = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($resultProduct);

if (!$product) {
    echo "<script>alert('Product not found.'); window.location='../buyer/buying.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $product['p_name']; ?> - RAPPID BID</title>
  <link rel="stylesheet" href="../css/product.css">
</head>
<body>
<header>
  <div class="bx bx-menu" id="menu-icon"></div>
  <div class="logo">RAPPID BID</div>
  <ul class="navlist">
      <li><a href="../index.php/index.html">Home</a></li>
      <li><a href="../html/about.html">About</a></li>
      <li><a href="../html/contact.html">Contact</a></li>
  </ul>
  <div class="nav-right">
     
      <a href="../index.php/logout.php" class="btn">Logout</a>
  </div>
</header>

<div class="container">
  <h1 class="product-title"><?php echo $product['p_name']; ?></h1>

  <!-- Images -->
  <div class="image-gallery">
    <?php for ($i=1; $i<=3; $i++): ?>
      <?php if (!empty($product["image$i"])): ?>
        <a href="../buyer/view_image.php?img=<?php echo $product["image$i"]; ?>&id=<?php echo $product['id']; ?>">
          <img src="../uploads/<?php echo $product["image$i"]; ?>" alt="Product Image">
        </a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>

  <h2>About product</h2>
  <p class="description"><?php echo $product['description']; ?></p>

  <!-- Bidding Section -->
  <form class="bid-form" action="../seller/product.php?id=<?php echo $product['id']; ?>" method="POST">
    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

    <div class="form-row">
      <label>Bid starting price</label>
      <input type="text" value="<?php echo $product['rate']; ?>" readonly>
    </div>
    <div class="form-row">
      <label>Date</label>
      <input type="date" value="<?php echo $product['date']; ?>" readonly>
    </div>
    <div class="form-row">
      <label>Time: From</label>
      <input type="time" value="<?php echo $product['t_from']; ?>" readonly>
    </div>
    <div class="form-row">
      <label>Time: To</label>
      <input type="time" value="<?php echo $product['t_to']; ?>" readonly>
    </div>

    <button id="btn" name="join_bid">Join to bid</button>
  </form>
</div>

<footer>
  <div class="foot">
    <a href="../buyer/buying.php"><- Back</a>
  </div>
</footer>
</body>
</html>
