<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/buying.css">
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
            <li><a href="index.html">home</a></li>
            <li><a href="html/about.html">about</a></li>
            <li><a href="html/contact.html">contact</a></li>
        </ul>
        <div class="nav-right">
            <a href="log/logout.php" class="btn">Logout</a>
         </div>
    </header>
   


  <div class="container">
    <h2>Auction Listings</h2>

    <div class="filter-bar">
      <label for="category">Select Category</label>
      <select id="category" onchange="filterCategory(this.value)">
        <option value="all">All</option>
        <option value="electronics">Electronics</option>
        <option value="fashion">Fashion</option>
        <option value="home">Home</option>
        <option value="other">Other</option>
      </select>

      <label for="sort">Sort by price</label>
      <select id="sort" onchange="sortTable(this.value)">
        <option value="none">None</option>
        <option value="asc">Low to High</option>
        <option value="desc">High to Low</option>
      </select>
    </div>

    <table id="auctionTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Image</th>
          <th>Category</th>
          <th>Name</th>
          <th>Description</th>
          <th>Minimum Bid</th>
          <th>Time</th>
          <th>Date</th>
          <th>more info</th>
        </tr>
      </thead>
      <tbody>
        <!-- PHP data will be inserted here -->
        <?php include '../buyer/fetch_products.php'; ?>
      </tbody>
    </table>
  </div>



   <footer>
        <div class="foot">
      <a href="html/login.html"><-Back</a>
       </div>

        <div class="edit">
      <a href="buyer/winner_status.php">winning bids</a>
       </div>

        <div class="edit">
      <a href="buyer/my_profile.php">Edit Profile</a>
       </div>
       
    </footer>
  
  <script>
    function filterCategory(category) {
      let rows = document.querySelectorAll("#auctionTable tbody tr");
      rows.forEach(row => {
        let cat = row.getAttribute("data-category");
        row.style.display = (category === "all" || category === cat) ? "" : "none";
      });
    }

    function sortTable(order) {
      let tbody = document.querySelector("#auctionTable tbody");
      let rows = Array.from(tbody.rows);

      if (order !== "none") {
        rows.sort((a, b) => {
          let priceA = parseFloat(a.cells[5].innerText); // updated column index for price
          let priceB = parseFloat(b.cells[5].innerText);
          return order === "asc" ? priceA - priceB : priceB - priceA;
        });
      }

      rows.forEach(row => tbody.appendChild(row));
    }
 



   
    let menu=document.querySelector('#menu-icon');
    let navlist=document.querySelector('.navlist');
    menu.onclick=()=>{
        menu.classList.toggle('bx-x');
        navlist.classList.toggle('open')
    }
   </script>  
</body>
</html>