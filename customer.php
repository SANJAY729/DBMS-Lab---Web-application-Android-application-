<?php
// KoMATO
require_once "config.php";
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
$choose_restaurant = $p_id = $quantity = "";
$choose_restaurant_err = $p_id_err = $quantity_err = $confirm_order_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (!empty($_POST['choose_restaurant'])){
        if (empty(trim($_POST['choose_r_uname']))){
            $choose_restaurant_err = "Please enter a restaurant";
        }
        else {
            $choose_restaurant = trim($_POST["choose_r_uname"]);
            if (isset($_SESSION["restaurant"])){
                $sql = "DELETE FROM contains WHERE contains.o_id = ? AND contains.o_id NOT IN (SELECT orders.o_id FROM orders)";
                if ($stmt = mysqli_prepare($link, $sql)){
                    mysqli_stmt_bind_param($stmt, "i", $param_o_id);
                    $param_o_id = $_SESSION["order_id"];
                    if (mysqli_stmt_execute($stmt)){
                        mysqli_stmt_close($stmt);
                    }
                    else {
                        mysqli_stmt_close($stmt);
                        echo "Something went wrong. Please try again.";
                    }
                }
            }
            $_SESSION["restaurant"] = $choose_restaurant;
            $_SESSION["order_id"] =  rand(1000,9999);
        }
    }
    if (!empty($_POST['add_item'])){
        if (empty(trim($_POST['p_id']))){
            $p_id_err = "Please enter a product id";
        }
        else {
            $p_id = trim($_POST['p_id']);
        }
        if (empty(trim($_POST['quantity']))){
            $quantity_err = "Please enter a quantity";
        }
        else {
            $quantity = trim($_POST['quantity']);
        }
        if (!isset($_SESSION['restaurant'])){
            $quantity_err = "Please select restaurant";
        }
        if (empty($p_id_err) && empty($quantity_err)){
            $sql = "SELECT * FROM products WHERE p_id = ? and r_uname = ?";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "is", $param_p_id, $param_r_username);
                $param_p_id = $p_id;
                $param_r_username = $_SESSION["restaurant"];
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) == 1){
                        mysqli_stmt_close($stmt);
                        $sql = "SELECT * FROM contains WHERE o_id = ? AND p_id = ?";
                        if ($stmt = mysqli_prepare($link, $sql)){
                            mysqli_stmt_bind_param($stmt, "ii", $param_o_id, $param_p_id);
                            $param_o_id = $_SESSION["order_id"];
                            $param_p_id = $p_id;
                            if (mysqli_stmt_execute($stmt)){
                                mysqli_stmt_store_result($stmt);
                                if (mysqli_stmt_num_rows($stmt) == 1){
                                    mysqli_stmt_close($stmt);
                                    $p_id_err = "Already added to order. Please delete to modify quantity";
                                }
                                else {
                                    mysqli_stmt_close($stmt);
                                    $sql = "INSERT INTO contains (o_id,p_id,quantity) VALUES (?,?,?)";
                                    if ($stmt = mysqli_prepare($link, $sql)){
                                        mysqli_stmt_bind_param($stmt, "iii", $param_o_id, $param_p_id, $param_quantity);
                                        $param_o_id = $_SESSION["order_id"];
                                        $param_p_id = $p_id;
                                        $param_quantity = $quantity;
                                        if (mysqli_stmt_execute($stmt)){
                                            mysqli_stmt_close($stmt);
                                            $quantity_err = "Succesfully added Item to Order";
                                        }
                                        else {
                                            echo "Something went wrong. Please try again.";
                                            mysqli_stmt_close($stmt);
                                        }
                                    }
                                }
                            }
                        }
                        
                    }
                    else {
                        mysqli_stmt_close($stmt);
                        $p_id_err = "Invalid product id";
                    }
                }
                else {
                    echo "Something went wrong. Please try again.";
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
    if (!empty($_POST['delete_item'])){
        if (empty(trim($_POST['p_id']))){
            $p_id_err = "Please enter a product id";
        }
        else {
            $p_id = trim($_POST['p_id']);
            $sql = "DELETE FROM contains WHERE o_id = ? AND p_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ii", $param_o_id, $param_p_id);
                $param_o_id = $_SESSION["order_id"];
                $param_p_id = $p_id;
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_close($stmt);
                    $quantity_err = "Succesfully deleted Item from Order";
                }
                else {
                    echo "Something went wrong. Please try again.";
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
    if (!empty($_POST['confirm_order'])){
        if (!isset($_SESSION['restaurant'])){
            $confirm_order_err = "Please choose a restaurant and add items to it before confirming";
        }
        else {
            $sql = "SELECT * FROM contains WHERE o_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "i", $param_o_id);
                $param_o_id = $_SESSION["order_id"];
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) == 0){
                        $confirm_order_err = "Please add items before confirming an order";
                    }
                }
                else {
                    echo "Something went wrong. Please try again.";
                    mysqli_stmt_close($stmt);
                }
            }
        }
        if (empty($confirm_order_err)){
            $sql = "SELECT products.p_time FROM products,contains WHERE products.p_id=contains.p_id AND contains.o_id = ? ORDER BY products.p_time DESC LIMIT 1";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "i", $param_o_id);
                $param_o_id = $_SESSION["order_id"];
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) > 0){
                        mysqli_stmt_bind_result($stmt,$prep_time);
                        mysqli_stmt_fetch($stmt);
                        mysqli_stmt_close($stmt);
                    }
                    else{
                        mysqli_stmt_close($stmt);
                        echo "Something went wrong. Please try again.";
                    }
                }
            }
            
            $sql = "SELECT sum(contains.quantity*products.p_cost) FROM products,contains WHERE products.p_id=contains.p_id AND contains.o_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "i", $param_o_id);
                $param_o_id = $_SESSION["order_id"];
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) > 0){
                        mysqli_stmt_bind_result($stmt,$total_cost);
                        mysqli_stmt_fetch($stmt);
                        mysqli_stmt_close($stmt);
                    }
                    else{
                        mysqli_stmt_close($stmt);
                        echo "Something went wrong. Please try again.";
                    }
                }
            }

            $sql = "INSERT INTO orders(o_id,r_uname,total_cost,expected_time) VALUES (?,?,?,?)";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "isii", $param_o_id, $param_r_username, $param_total_cost, $param_expected_time);
                $param_o_id = $_SESSION["order_id"];
                $param_r_username = $_SESSION["restaurant"];
                $param_total_cost = $total_cost;
                $param_expected_time = $prep_time;
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_close($stmt);
                    $confirm_order_err = "Succesfully placed order";
                }
                else{
                    mysqli_stmt_close($stmt);
                    echo "Something went wrong. Please try again.";
                }
            }

            $sql = "SELECT delivery_agents.da_uname FROM delivery_agents 
            WHERE delivery_agents.da_uname NOT IN (SELECT assigned.da_uname FROM assigned,orders 
            WHERE orders.o_id=assigned.o_id AND (orders.order_status='Preparing' OR orders.order_status='Prepared')) LIMIT 1";
            if ($stmt = mysqli_prepare($link, $sql)){
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) > 0){
                        mysqli_stmt_bind_result($stmt,$alloted_da);
                        mysqli_stmt_fetch($stmt);
                        mysqli_stmt_close($stmt);
                    }
                    else {
                        mysqli_stmt_close($stmt);
                        $sql = "SELECT assigned.da_uname, COUNT(*) as cnt FROM assigned,orders 
                        WHERE orders.o_id=assigned.o_id AND (orders.order_status='Preparing' OR orders.order_status='Prepared') 
                        GROUP BY assigned.da_uname ORDER BY cnt LIMIT 1";
                        if ($stmt = mysqli_prepare($link, $sql)){
                            if (mysqli_stmt_execute($stmt)){
                                mysqli_stmt_store_result($stmt);
                                if (mysqli_stmt_num_rows($stmt) > 0){
                                    mysqli_stmt_bind_result($stmt,$alloted_da, $current);
                                    mysqli_stmt_fetch($stmt);
                                    mysqli_stmt_close($stmt);
                                }
                            }
                            else {
                                mysqli_stmt_close($stmt);
                                echo "Something went wrong. Please try again.";
                            }
                        }
                    }
                }
                else {
                    mysqli_stmt_close($stmt);
                    echo "Something went wrong. Please try again.";
                }
            }
            
            $sql = "INSERT INTO assigned(o_id,da_uname) VALUES (?,?)";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "is", $param_o_id, $param_da_uname);
                $param_o_id = $_SESSION["order_id"];
                $param_da_uname = $alloted_da;
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_close($stmt);
                }
                else{
                    mysqli_stmt_close($stmt);
                    echo "Something went wrong. Please try again.";
                }
            }

            $sql = "INSERT INTO places(o_id,c_uname,total_time) VALUES (?,?,?)";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "isi", $param_o_id, $param_c_uname, $param_total_time);
                $param_o_id = $_SESSION["order_id"];
                $param_c_uname = $_SESSION["username"];
                $param_total_time = $prep_time + 15;
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_close($stmt);
                }
                else{
                    mysqli_stmt_close($stmt);
                    echo "Something went wrong. Please try again.";
                }
            }
        }
    }
    if (!empty($_POST['logout'])){
        $sql = "SELECT * FROM orders WHERE o_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $param_o_id);
            if (isset($_SESSION['order_id'])){
                $param_o_id = $_SESSION['order_id'];
            }
            else {
                $param_o_id = -1;
            }
            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_close($stmt);
                }
                else {
                    mysqli_stmt_close($stmt);
                    $sql = "DELETE FROM contains WHERE o_id = ?";
                    if ($stmt = mysqli_prepare($link, $sql)){
                        mysqli_stmt_bind_param($stmt, "i", $param_o_id);
                        if (isset($_SESSION['order_id'])){
                            $param_o_id = $_SESSION['order_id'];
                        }
                        else {
                            $param_o_id = -1;
                        }
                        if (mysqli_stmt_execute($stmt)){
                            mysqli_stmt_close($stmt);
                        }
                        else {
                            mysqli_stmt_close($stmt);
                            echo "Something went wrong. Please try again.";
                        }
                    }
                }
            
            }
            else {
                mysqli_stmt_close($stmt);
                echo "Something went wrong. Please try again.";
            }
        }
        header("location: logout.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Customer</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
        <style type="text/css">
            body{
                font: 14px sans-serif;
                text-align: left;
            }
            table, th, td ,tr{
                border: 1px solid black;
                align: center;
                padding: 2%;
                width:500px;
            }
        </style>
    </head>
    <body>
        <div style="float:left; width: 30%; padding: 40px;">
            <h2><?php echo $_SESSION["username"]; ?></h2>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h3>Choose Restaurant</h3>
                <p>Enter the restaurant from which you would like to order. (Changing this would discard your current order if present)</p>
                <div class="form-group <?php echo (!empty($choose_restaurant_err)) ? 'has-error' : ''; ?>">
                    <label>Restaurant Username</label>
                    <input type="text" name="choose_r_uname" class="form-control" value="<?php echo $choose_restaurant; ?>">
                    <span class="help-block"><?php echo $choose_restaurant_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" name="choose_restaurant" class="btn btn-primary" value="Submit">
                </div>
            </form>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h3>Modify Order</h3>
                <p>Enter the Product ID and Quantity which you would like to add/remove from your order</p>
                <div class="form-group <?php echo (!empty($p_id_err)) ? 'has-error' : ''; ?>">
                    <label>Product ID</label>
                    <input type="number" name="p_id" class="form-control" value="<?php echo $p_id; ?>">
                    <span class="help-block"><?php echo $p_id_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($quantity_err)) ? 'has-error' : ''; ?>">
                    <label>Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="<?php echo $quantity; ?>">
                    <span class="help-block"><?php echo $quantity_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" name="add_item" class="btn btn-primary" value="Add item">
                    <input type="submit" name="delete_item" class="btn btn-primary" value="Delete item">
                </div>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <p>You cannot modify your order after confirmation</p>
                <div class="form-group">
                    <input type="submit" name="confirm_order" class="btn btn-primary" value="Confirm Order">
                    <span class="help-block"><?php echo $confirm_order_err; ?></span>
                </div>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <input type="submit" name="logout" class="btn btn-danger" value="Sign Out of Your Account">
                </div>
            </form>
        </div>
        <div style="float:right; width: 50%; padding: 40px;">
            <h2><?php echo "Restaurants"; ?></h2>
            <?php
                $sql = "SELECT r_uname, r_name, r_address, r_phno FROM restaurants";
                if ($stmt = mysqli_prepare($link, $sql)){
                    if (mysqli_stmt_execute($stmt)){
                        mysqli_stmt_store_result($stmt);
                        if (mysqli_stmt_num_rows($stmt) > 0){
                            echo "<table>";
                            echo "<tr><th>Username</th><th>Name</th><th>Address</th><th>Phone Number</th></tr>\n";
                            mysqli_stmt_bind_result($stmt, $uname, $name, $address, $phno);
                            while (mysqli_stmt_fetch($stmt)){
                                echo "<tr><td>{$uname}</td><td>{$name}</td><td>{$address}</td><td>{$phno}</td></tr>\n";
                            }
                            echo "</table>";
                        }
                        else {
                            echo "No Restaurants";
                        }
                        mysqli_stmt_close($stmt);
                    }
                    else {
                        mysqli_stmt_close($stmt);
                        echo "Something went wrong. Please try again.";
                    }
                }
            ?>
            <h2><?php echo "Menu"; ?></h2>
            <?php
                $sql = "SELECT p_id, p_name, p_cost, p_time, p_description FROM products WHERE r_uname = ?";
                if ($stmt = mysqli_prepare($link, $sql)){
                    mysqli_stmt_bind_param($stmt, "s", $param_r_username);
                    if (isset($_SESSION["restaurant"])){
                        $param_r_username = $_SESSION["restaurant"];
                    }
                    else {
                        $param_r_username = "";
                    }
                    if (mysqli_stmt_execute($stmt)){
                        mysqli_stmt_store_result($stmt);
                        if (mysqli_stmt_num_rows($stmt) > 0){
                            echo "<table>";
                            echo "<tr><th>ID</th><th>Name</th><th>Cost</th><th>Time (in min)</th><th>Description</th></tr>\n";
                            mysqli_stmt_bind_result($stmt, $id, $name, $cost, $time, $description);
                            while (mysqli_stmt_fetch($stmt)){
                                echo "<tr><td>{$id}</td><td>{$name}</td><td>{$cost}</td><td>{$time}</td><td>{$description}</td></tr>\n";
                            }
                            echo "</table>";
                        }
                        else {
                            echo "No Food Items Added";
                        }
                        mysqli_stmt_close($stmt);
                    }
                    else {
                        mysqli_stmt_close($stmt);
                        echo "Something went wrong. Please try again.";
                    }
                }
            ?>
            <h2><?php echo "Order Details";?></h2>
            <?php
                $sql = "SELECT orders.o_id, orders.r_uname, orders.total_cost, places.total_time, orders.order_status FROM orders,places WHERE places.c_uname = ? AND places.o_id = orders.o_id";
                if ($stmt = mysqli_prepare($link, $sql)){
                    mysqli_stmt_bind_param($stmt, "s", $param_c_uname);
                    $param_c_uname = $_SESSION["username"];
                    if (mysqli_stmt_execute($stmt)){
                        mysqli_stmt_store_result($stmt);
                        if (mysqli_stmt_num_rows($stmt) > 0){
                            echo "<table>";
                            echo "<tr><th>Order ID</th><th>Restaurant Name</th><th>Cost</th><th>Time (in min)</th><th>Status</th></tr>\n";
                            mysqli_stmt_bind_result($stmt, $id, $name, $cost, $time, $status);
                            while (mysqli_stmt_fetch($stmt)){
                                echo "<tr><td>{$id}</td><td>{$name}</td><td>{$cost}</td><td>{$time}</td><td>{$status}</td></tr>\n";
                            }
                            echo "</table>";
                        }
                        else {
                            echo "Order Details Unavailable";
                        }
                        mysqli_stmt_close($stmt);
                    }
                    else {
                        mysqli_stmt_close($stmt);
                        echo "Something went wrong. Please try again.";
                    }
                }
            ?>
            <h2><?php echo "Order Contents";?></h2>
            <?php
                $sql = "SELECT contains.o_id, contains.p_id, contains.quantity FROM contains,places,orders WHERE places.o_id=contains.o_id AND places.c_uname = ? AND orders.o_id=places.o_id AND NOT orders.order_status = 'Completed'";
                if ($stmt = mysqli_prepare($link, $sql)){
                    mysqli_stmt_bind_param($stmt, "s", $param_c_uname);
                    $param_c_uname = $_SESSION["username"];
                    if (mysqli_stmt_execute($stmt)){
                        mysqli_stmt_store_result($stmt);
                        if (mysqli_stmt_num_rows($stmt) > 0){
                            echo "<table>";
                            echo "<tr><th>Order ID</th><th>Product ID</th><th>Quantity</th></tr>\n";
                            mysqli_stmt_bind_result($stmt, $o_id, $p_id, $quantity);
                            while (mysqli_stmt_fetch($stmt)){
                                echo "<tr><td>{$o_id}</td><td>{$p_id}</td><td>{$quantity}</td></tr>\n";
                            }
                            echo "</table>";
                        }
                        else {
                            echo "Order Contents Unavailable";
                        }
                        mysqli_stmt_close($stmt);
                    }
                    else {
                        mysqli_stmt_close($stmt);
                        echo "Something went wrong. Please try again.";
                    }
                }
            ?>
        </div>
    </body>
</html>