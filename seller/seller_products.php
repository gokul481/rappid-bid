<?php
session_start();

// Check if seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header("Location: ../html/sellogin.html");
    exit();
}

$seller_id = $_SESSION['seller_id'];

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "auction";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// -------------------- DELETE PRODUCT --------------------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Delete product if it belongs to the current seller
    $sqlCheck = "SELECT * FROM products WHERE id=$id AND seller_id='$seller_id'";
    $resCheck = $conn->query($sqlCheck);
    if ($resCheck->num_rows > 0) {
        $conn->query("DELETE FROM products WHERE id=$id");

        // Reset IDs after deletion (optional)
        $conn->query("SET @num := 0");
        $conn->query("UPDATE products SET id = (@num := @num + 1) ORDER BY id");
        $conn->query("ALTER TABLE products AUTO_INCREMENT = 1");
    }

    header("Location: seller_products.php");
    exit();
}

// -------------------- UPDATE PRODUCT --------------------
if (isset($_POST['update'])) {
    $id       = intval($_POST['id']);
    $name     = $conn->real_escape_string($_POST['p_name']);
    $desc     = $conn->real_escape_string($_POST['description']);
    $min_bid  = $conn->real_escape_string($_POST['min_bid']);
    $category = $conn->real_escape_string($_POST['category']);
    $t_from   = $conn->real_escape_string($_POST['t_from']);
    $t_to     = $conn->real_escape_string($_POST['t_to']);
    $date     = $conn->real_escape_string($_POST['date']);

    // Only update if product belongs to current seller
    $sqlCheck = "SELECT * FROM products WHERE id=$id AND seller_id='$seller_id'";
    $resCheck = $conn->query($sqlCheck);
    if ($resCheck->num_rows > 0) {

        $updates = [];

        // Handle image uploads
        $targetDir = "../uploads/";
        if (!empty($_FILES['image1']['name'])) {
            $fileName1 = time() . "_1_" . basename($_FILES["image1"]["name"]);
            move_uploaded_file($_FILES["image1"]["tmp_name"], $targetDir . $fileName1);
            $updates[] = "image1='$fileName1'";
        }
        if (!empty($_FILES['image2']['name'])) {
            $fileName2 = time() . "_2_" . basename($_FILES["image2"]["name"]);
            move_uploaded_file($_FILES["image2"]["tmp_name"], $targetDir . $fileName2);
            $updates[] = "image2='$fileName2'";
        }
        if (!empty($_FILES['image3']['name'])) {
            $fileName3 = time() . "_3_" . basename($_FILES["image3"]["name"]);
            move_uploaded_file($_FILES["image3"]["tmp_name"], $targetDir . $fileName3);
            $updates[] = "image3='$fileName3'";
        }

        // Build SQL update
        $sqlUpdate = "UPDATE products 
                      SET p_name='$name', description='$desc', rate='$min_bid', 
                          category='$category', t_from='$t_from', t_to='$t_to', date='$date'";
        if (!empty($updates)) {
            $sqlUpdate .= ", " . implode(", ", $updates);
        }
        $sqlUpdate .= " WHERE id=$id AND seller_id='$seller_id'";

        $conn->query($sqlUpdate);
    }

    header("Location: seller_products.php");
    exit();
}

// -------------------- FETCH PRODUCTS --------------------
$sql = "SELECT * FROM products WHERE seller_id='$seller_id'";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Product Management</title>
    <link rel="stylesheet" href="../css/seller_products.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<header>
    <div class="bx bx-menu" id="menu-icon"></div>
    <div class="logo">RAPPID BID</div>
    <ul class="navlist">
        <li><a href="../index.html">home</a></li>
        <li><a href="../html/about.html">about</a></li>
        <li><a href="../html/contact.html">contact</a></li>
    </ul>
    <div class="nav-right">
        <a href="../log/logout.php" class="btn">Logout</a>
    </div>
</header> 

<h2>Seller Product Management</h2>

<div class="container">
<table id="auctionTable">
    <tr>
        <th>ID</th>
        <th>Images</th>
        <th>Category</th>
        <th>Name</th>
        <th>Description</th>
        <th>Minimum Bid</th>
        <th>Time From</th>
        <th>Time To</th>
        <th>Date</th>
        <th>Actions</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td>
                <?php if(!empty($row['image1'])): ?>
                    <img src="../uploads/<?= $row['image1']; ?>" width="60">
                <?php endif; ?>
              
            </td>
            <td><?= $row['category']; ?></td>
            <td><?= $row['p_name']; ?></td>
            <td><?= $row['description']; ?></td>
            <td><?= $row['rate']; ?></td>
            <td><?= $row['t_from']; ?></td>
            <td><?= $row['t_to']; ?></td>
            <td><?= $row['date']; ?></td>
            <td>
                <button class="btns-edit" 
                    onclick="showEditForm(
                        '<?= $row['id']; ?>',
                        '<?= $row['p_name']; ?>',
                        '<?= $row['rate']; ?>',
                        '<?= $row['description']; ?>',
                        '<?= $row['category']; ?>',
                        '<?= $row['t_from']; ?>',
                        '<?= $row['t_to']; ?>',
                        '<?= $row['date']; ?>'
                    )">Edit</button>
                <a href="?delete=<?= $row['id']; ?>" onclick="return confirm('Delete this product?')">
                    <button class="btns-danger">Delete</button>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="10">No Product Found</td></tr>
    <?php endif; ?>
</table>
</div>

<!-- Edit Form -->
<div id="editForm" class="edit-form" style="display:none;">
    <h3>Edit Product</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" id="editId">

        <label>Product Name:</label><br>
        <input type="text" name="p_name" id="editName" required><br><br>

        <label>Category:</label><br>
       <select id="editCategory" name="category" required>
            <option value="">--Select--</option>
            <option value="electronics">Electronics</option>
            <option value="fashion">Fashion</option>
            <option value="home">Home</option>
            <option value="other">Other</option>
        </select><br><br>

        <label>Description:</label><br>
        <textarea name="description" id="editDesc" required></textarea><br><br>

        <label>Minimum Bid:</label><br>
        <input type="number" name="min_bid" id="editPrice" step="0.01" required><br><br>

        <label>Time From:</label><br>
        <input type="time" name="t_from" id="editTimeFrom" required><br><br>

        <label>Time To:</label><br>
        <input type="time" name="t_to" id="editTimeTo" required><br><br>

        <label>Date:</label><br>
        <input type="date" name="date" id="editDate" required><br><br>

        <label>Change Image 1:</label><br>
        <input type="file" name="image1"><br><br>

        <label>Change Image 2:</label><br>
        <input type="file" name="image2"><br><br>

        <label>Change Image 3:</label><br>
        <input type="file" name="image3"><br><br>

        <button type="submit" name="update" class="btn-edit">Update</button>
        <button type="button" class="btn-danger" onclick="document.getElementById('editForm').style.display='none'">Cancel</button>
    </form>
</div>

<script>
function showEditForm(id, name, price, desc, category, t_from, t_to, date) {
    document.getElementById('editForm').style.display = 'block';
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editPrice').value = price;
    document.getElementById('editDesc').value = desc;
    document.getElementById('editCategory').value = category;
    document.getElementById('editTimeFrom').value = t_from;
    document.getElementById('editTimeTo').value = t_to;
    document.getElementById('editDate').value = date;
}
</script>

<footer>
    <div class="foot">
        <a href="../html/selling.html"><-Back</a>
    </div>
</footer>

</body>
</html>
