<?php

date_default_timezone_set("Asia/Kolkata"); // adjust to your region

session_start();

if (!isset($_SESSION['email'])) {
    echo "<script>alert('⚠️ You must be logged in to view this product.'); window.location='login.php';</script>";
    exit();
}
$loggedInEmail = $_SESSION['email'];


$host = "localhost";
$user = "root";
$pass = "";
$db   = "auction";
$conn = mysqli_connect($host, $user, $pass, $db);
if(!$conn){ die("Connection failed: " . mysqli_connect_error()); }

$product_id = $_GET['product_id'] ?? null;
if(!$product_id){ die("❌ No product selected."); }

// Handle bid submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_bid'])) {
    $bidder_name = $_POST['bidder_name'] ?? null;
    $amount      = floatval($_POST['bid_amount']);

    // Only allow bid if auction is active
    $check_time = "SELECT date, t_from, t_to, rate FROM products WHERE id=?";
    $stmt = mysqli_prepare($conn, $check_time);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $timedata = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    $auction_start = strtotime($timedata['date'] . " " . $timedata['t_from']);
    $auction_end   = strtotime($timedata['date'] . " " . $timedata['t_to']);

    // Get current highest bid
    $sql = "SELECT bidder_name, bid_amount FROM bids WHERE product_id=? ORDER BY bid_amount DESC, id DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $current_bid_result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $current_price = $current_bid_result['bid_amount'] ?? floatval($timedata['rate']);

    $now = time();

    if ($now >= $auction_start && $now <= $auction_end) {

        if ($amount <= $current_price) {
            $msg = "⚠️ Your bid must be higher than the current price (Rs.$current_price).";
        } else {
            // Insert new bid
            $sql = "INSERT INTO bids (product_id, bidder_name, bid_amount) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "isd", $product_id, $bidder_name, $amount);

            if(mysqli_stmt_execute($stmt)){
                // Update product base price in add_product table
               /* $update = "UPDATE add_product SET rate=? WHERE id=?";
                $stmt_update = mysqli_prepare($conn, $update);
                mysqli_stmt_bind_param($stmt_update, "di", $amount, $product_id);
                mysqli_stmt_execute($stmt_update);*/

                // ----------------- Update winner table in real-time -----------------
                $check = "SELECT * FROM winners WHERE product_id=?";
                $stmt_check = mysqli_prepare($conn, $check);
                mysqli_stmt_bind_param($stmt_check, "i", $product_id);
                mysqli_stmt_execute($stmt_check);
                $exists = mysqli_num_rows(mysqli_stmt_get_result($stmt_check));

                if ($exists == 0) {
                    $insert = "INSERT INTO winners (product_id, winner_name, winning_bid) VALUES (?, ?, ?)";
                    $stmt_insert = mysqli_prepare($conn, $insert);
                    mysqli_stmt_bind_param($stmt_insert, "isd", $product_id, $bidder_name, $amount);
                    mysqli_stmt_execute($stmt_insert);
                } else {
                    $update_winner = "UPDATE winners SET winner_name=?, winning_bid=? WHERE product_id=?";
                    $stmt_update_winner = mysqli_prepare($conn, $update_winner);
                    mysqli_stmt_bind_param($stmt_update_winner, "sdi", $bidder_name, $amount, $product_id);
                    mysqli_stmt_execute($stmt_update_winner);
                }
                // -------------------------------------------------------------------

                $msg = "✅ Bid of Rs.$amount placed by $bidder_name";
            } else {
                $msg = "❌ Error: " . mysqli_error($conn);
            }
        }

    } elseif($now >= $auction_end)
    {
        $msg="⚠️auction ended you cannot place a bid";
    }
    else{
        $msg="⚠️auction not started";
    }
}

// Fetch product
$sql = "SELECT * FROM products WHERE id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if(!$product){ die("❌ Product not found."); }

// Fetch joined bidders
$sql = "SELECT * FROM joined_bidders WHERE product_id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$joined = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// ----------------- Fetch Winner -----------------
$winner = null;
$sql = "SELECT winner_name, winning_bid FROM winners WHERE product_id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$winner = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Update current price for display
$current_price = $winner['winning_bid'] ?? $product['rate'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/bid.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>

<header>
     <div class="bx bx-menu" id="menu-icon"></div>
    <div class="logo">RAPPID BID</div>
    <ul class="navlist">
        <li><a href="../index.php/index.html">home</a></li>
        <li><a href="../html/about.html">about</a></li>
        <li><a href="../html/contact.html">contact</a></li>
    </ul>
    <div class="nav-right">
        <a href="../index.php/logout.php" class="btn">Logout</a>
     </div>
</header>

<div class="auction-time">
    Auction Date: <?php echo $product['date']; ?> |
    From: <?php echo $product['t_from']; ?> |
    To: <?php echo $product['t_to']; ?>
</div>

<div>
    <img src="../uploads/<?php echo $product['image1']; ?>" width="250"><br>
    <h2><?php echo $product['p_name']; ?></h2>
    <p>Current Price: Rs.<?php echo $current_price; ?></p>
</div>



<?php if ($winner): ?>
    <div class="winner-box">
        🎉 <u class="ul">Current Highest Bid</u> <br>
        <?php 
        // Escape the email safely
        $email = $conn->real_escape_string($winner['winner_name']);
        $sql = "SELECT email FROM users WHERE email='$email'";
        $result = $conn->query($sql);

        $username = "Unknown";
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $username = $row['email'];
        }
        ?>
        <b>Current winner:</b> <span style="color:red;"><?php echo htmlspecialchars($username); ?></span><br>
        <b>Bid:</b> Rs.<?php echo htmlspecialchars($winner['winning_bid']); ?>
    </div>
<?php endif; ?>


<?php if(isset($msg)) echo "<p>$msg</p>"; ?>

<div>
   <?php 
$current_bidder = $_SESSION['bidder_name'] ?? null; 
$joined_current = array_filter($joined, function($jb) use ($current_bidder) {
    return $jb['bidder_name'] === $current_bidder;
});
?>

<?php



$current_bidder = $_SESSION['email'] ?? null;

if ($current_bidder) {
    // Check if current bidder has already joined
    $stmt = $conn->prepare("SELECT * FROM joined_bidders WHERE product_id=? AND bidder_name=?");
    $stmt->bind_param("is", $product_id, $current_bidder);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows;

    // If not joined, insert into joined_bidders
    if ($exists == 0) {
        $stmt_insert = $conn->prepare("INSERT INTO joined_bidders (product_id, bidder_name) VALUES (?, ?)");
        $stmt_insert->bind_param("is", $product_id, $current_bidder);
        $stmt_insert->execute();
    }
?>
    <div class="bidder-box">
        <h3><?php echo htmlspecialchars($current_bidder); ?></h3>
        <form method="post">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="bidder_name" value="<?php echo htmlspecialchars($current_bidder); ?>">
            <div class="my-input">
                <input type="number" name="bid_amount" step="10" placeholder="Enter amount" required>
            </div>
            <br>
            <div class="my-button">
                <button type="submit" name="place_bid">Place Bid</button>
            </div>
        </form>
    </div>
<?php
} else {
    echo "<p>Please login to place a bid.</p>";
}
?>


</div>

<footer>
    <div class="foot">
        <a href="../seller/product.php"><-Back</a>
    </div>
</footer>

<script type="text/javascript">
    let menu=document.querySelector('#menu-icon');
    let navlist=document.querySelector('.navlist');
    menu.onclick=()=>{
        menu.classList.toggle('bx-x');
        navlist.classList.toggle('open')
    }
</script>  
</body>
</html>

<?php mysqli_close($conn); ?>
