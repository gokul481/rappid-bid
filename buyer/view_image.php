<?php

// Get image and product ID
$image = $_GET['img'] ?? '';
$product_id = $_GET['id'] ?? 1;

// Security check
$image = basename($image);
$imagePath = "../uploads/" . $image;

if(!file_exists($imagePath)){
    die("Image not found.");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/view_image.css">
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
            <li><a href="../index.html">home</a></li>
            <li><a href="../html/about.html">about</a></li>
            <li><a href="../html/contact.html">contact</a></li>
        </ul>
        <div class="nav-right">
            <a href="../index.php/logout.php" class="btn">Logout</a>
         </div>
    </header>


    <div class="images">
         <img src="<?php echo $imagePath; ?>" alt="Product Image">
    </div>

    
    
     <footer>
        <div class="foot">
       <a href="../seller/product.php?id=<?php echo $product_id; ?>" class="back-btn"><- Back</a>
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