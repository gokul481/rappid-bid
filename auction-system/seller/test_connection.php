<?php
// Test database connection
$servername = "localhost"; 
$username   = "root";
$password   = "";
$dbname     = "auction";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connection successful!<br>";

// Check if table exists
$table_check = $conn->query("SHOW TABLES LIKE 'add_product'");
if ($table_check->num_rows > 0) {
    echo "Table 'add_product' exists.<br>";
    
    // Show table structure
    $result = $conn->query("DESCRIBE add_product");
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Table 'add_product' does not exist. Please run the database_setup.sql file first.";
}

$conn->close();
?>
