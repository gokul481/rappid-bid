<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['seller_email'])) {
    header("Location: ../seller/sellogin.html");
    exit();
}

$loggedInEmail = $_SESSION['seller_email'];

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "auction";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch current user details
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInEmail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user) {
    die("User not found!");
}

$message = "";

// Handle profile update
if (isset($_POST['update'])) {
    $fname    = $conn->real_escape_string($_POST['fname']);
    $lname    = $conn->real_escape_string($_POST['lname']);
    $address  = $conn->real_escape_string($_POST['address']);
    $pincode  = $conn->real_escape_string($_POST['pincode']);
    $phone    = $conn->real_escape_string($_POST['phone']);
    $altphone = $conn->real_escape_string($_POST['altphone']);
    $email    = $conn->real_escape_string($_POST['email']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    // Validation
    if (!preg_match("/^[1-9][0-9]{5}$/", $pincode)) {
        $message = "❌ Invalid pincode. Data not saved.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Invalid email. Data not saved.";
    } else {
        // Check if email already exists (and not same as logged-in one)
        $checkEmailSql = "SELECT * FROM users WHERE email=? AND email!=?";
        $stmt = $conn->prepare($checkEmailSql);
        $stmt->bind_param("ss", $email, $loggedInEmail);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "❌ Email already exists. Please choose another one.";
        } else {
            // Update query
            if ($password) {
                $updateSql = "UPDATE users 
                              SET fname=?, lname=?, address=?, pincode=?, phone=?, altphone=?, email=?, password=? 
                              WHERE email=?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("sssssssss", $fname, $lname, $address, $pincode, $phone, $altphone, $email, $password, $loggedInEmail);
            } else {
                $updateSql = "UPDATE users 
                              SET fname=?, lname=?, address=?, pincode=?, phone=?, altphone=?, email=? 
                              WHERE email=?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("ssssssss", $fname, $lname, $address, $pincode, $phone, $altphone, $email, $loggedInEmail);
            }

            if ($stmt->execute()) {
                $_SESSION['seller_email'] = $email; // update session
                echo "<script>alert('✅ Profile updated successfully!'); window.location='../seller/selling.html';</script>";
                exit();
            } else {
                $message = "❌ Error updating profile: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../css/my_profile.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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

<div class="container">
  <h2>Edit Profile</h2>

  <?php if (!empty($message)): ?>
      <p style="color:red;"><?= $message ?></p>
  <?php endif; ?>

  <form method="POST">
    <label>First Name</label>
    <input type="text" name="fname" value="<?= htmlspecialchars($user['fname']) ?>" required>

    <label>Last Name</label>
    <input type="text" name="lname" value="<?= htmlspecialchars($user['lname']) ?>" required>

    <label>Address</label>
    <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" required>

    <label>Pincode</label>
    <input type="text" name="pincode" value="<?= htmlspecialchars($user['pincode']) ?>" required>

    <label>Phone</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>

    <label>Alternate Phone</label>
    <input type="text" name="altphone" value="<?= htmlspecialchars($user['altphone']) ?>">

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

    <label>New Password (leave blank to keep current)</label>
    <input type="password" name="password">

    <button type="submit" name="update">Update</button>
  </form>
</div>

<footer>
    <div class="foot">
        <a href="../html/selling.html"><-Back</a>
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
