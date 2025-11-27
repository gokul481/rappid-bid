<?php
session_start();

// Database connection
$servername = "localhost"; 
$username   = "root";       // your DB username
$password   = "";           // your DB password
$dbname     = "auction";    // your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if (isset($_POST['register'])) {
    // Collect form data and sanitize
    $fname    = $conn->real_escape_string($_POST['fname']);
    $lname    = $conn->real_escape_string($_POST['lname']);
    $address  = $conn->real_escape_string($_POST['address']);
    $pincode  = $conn->real_escape_string($_POST['pincode']);
    $phone    = $conn->real_escape_string($_POST['phone']);
    $altphone = $conn->real_escape_string($_POST['altphone']);
    $email    = $conn->real_escape_string($_POST['email']);
    $pass     = $_POST['pass'];
    $cpass    = $_POST['cpass'];

    // Validate passwords
    if ($pass !== $cpass) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    // Hash password for security
    $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);

    // Check if email already exists
    $checkEmail = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($checkEmail->num_rows > 0) {
        echo "<script>alert('Email already registered!'); window.history.back();</script>";
        exit;
    }

    // Insert into database
    $sql = "INSERT INTO users (fname, lname, address, pincode, phone, altphone, email, password) 
            VALUES ('$fname', '$lname', '$address', '$pincode', '$phone', '$altphone', '$email', '$hashed_pass')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Registration Successful! Please login.'); window.location='../index.html';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
