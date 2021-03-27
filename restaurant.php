<?php
// KoMATO
require_once "config.php";
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$new_item_name = $new_item_cost = $new_item_time = $new_item_description = $del_item_id = $status_order_id  = $status_new_status = "";
$new_item_name_err = $new_item_cost_err = $new_item_time_err = $new_item_description_err = $del_item_id_err = $status_order_id_err = $status_new_status_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (!empty($_POST['add_item'])){
        if (empty(trim($_POST["new_item_name"]))){
            $new_item_name_err = "Please enter an item name.";     
        }
        else {
            $new_item_name = trim($_POST["new_item_name"]);
        }
        if (empty(trim($_POST["new_item_cost"]))){
            $new_item_cost_err = "Please enter item cost.";     
        }
        else {
            $new_item_cost = trim($_POST["new_item_cost"]);
        }
        if (empty(trim($_POST["new_item_time"]))){
            $new_item_time_err = "Please enter the time needed to cook the item.";     
        }
        else {
            $new_item_time = trim($_POST["new_item_time"]);
        }
        if (empty(trim($_POST["new_item_description"]))){
            $new_item_description_err = "Please enter item description.";     
        }
        else {
            $new_item_description = trim($_POST["new_item_description"]);
        }
        if(empty($new_item_name_err) && empty($new_item_cost_err) && empty($new_item_time_err) && empty($new_item_description_err)){
            $sql = "INSERT INTO products (r_uname,p_name,p_cost,p_time,p_description) VALUES (?,?,?,?,?)";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ssiis", $param_r_username, $param_p_name, $param_p_cost, $param_p_time, $param_p_description);
                $param_r_username = $_SESSION["username"];
                $param_p_name = $new_item_name;
                $param_p_cost = $new_item_cost;
                $param_p_time = $new_item_time;
                $param_p_description = $new_item_description;
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_close($stmt);
                    $new_item_description_err = "Succesfully added Item to Menu";
                }
                else {
                    echo "Something went wrong. Please try again.";
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
    else if (!empty($_POST['delete_item'])){
        if (empty(trim($_POST["del_item_id"]))){
            $del_item_id_err = "Please enter ID of the item to be deleted.";     
        }
        else {
            $del_item_id = trim($_POST["del_item_id"]);
            $sql = "SELECT * FROM products WHERE p_id = ? and r_uname = ?";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "is", $param_p_id, $param_r_username);
                $param_p_id = $del_item_id;
                $param_r_username = $_SESSION["username"];
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) == 1){
                        mysqli_stmt_close($stmt);
                        $sql = "DELETE FROM products WHERE p_id = ?";
                        if ($stmt = mysqli_prepare($link, $sql)){
                            mysqli_stmt_bind_param($stmt, "i", $param_p_id);
                            $param_p_id = $del_item_id;
                            if (mysqli_stmt_execute($stmt)){
                                mysqli_stmt_close($stmt);
                                $del_item_id_err = "Succesfully deleted Item from Menu";
                            }
                            else {
                                mysqli_stmt_close($stmt);
                                echo "Something went wrong. Please try again.";
                            }
                        }
                    }
                    else {
                        mysqli_stmt_close($stmt);
                        $del_item_id_err = "Food item with this ID does not exist.";
                    }
                }
                else {
                    mysqli_stmt_close($stmt);
                    echo "Something went wrong. Please try again.";
                }
            }
        }
    }
    else if (!empty($_POST['update_order_status'])){
        if (empty(trim($_POST["status_order_id"]))){
            $status_order_id_err = "Please enter the ID of the order whose status is to be uodated"; 
        }
        else {
            $status_order_id = trim($_POST["status_order_id"]);
        }
        if (empty(trim($_POST["new_status"]))){
            $status_new_status_err = "Please enter new status";
        }
        else {
            $status_new_status = $_POST["new_status"];
        }
        if (empty($status_new_status_err) && empty($status_order_id_err)){
            $sql = "SELECT * FROM orders WHERE o_id = ? and r_uname = ?";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "is", $param_o_id, $param_r_username);
                $param_o_id = $status_order_id;
                $param_r_username = $_SESSION["username"];
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) == 1){
                        mysqli_stmt_close($stmt);
                        $sql = "UPDATE orders SET order_status = ? WHERE o_id = ? AND r_uname = ?";
                        if ($stmt = mysqli_prepare($link, $sql)){
                            mysqli_stmt_bind_param($stmt, "sis", $param_o_status, $param_o_id, $param_r_username);
                            $param_o_status = $status_new_status;
                            $param_o_id = $status_order_id;
                            $param_r_username = $_SESSION["username"];
                            if (mysqli_stmt_execute($stmt)){
                                mysqli_stmt_close($stmt);
                                $status_new_status_err = "Succesfully updated status";
                            }
                            else {
                                mysqli_stmt_close($stmt);
                                echo "Something went wrong. Please try again.";
                            }
                        }
                    }
                    else {
                        mysqli_stmt_close($stmt);
                        $status_new_status_err = "Invalid Order ID";
                    }
                }
                else {
                    mysqli_stmt_close($stmt);
                    echo "Something went wrong. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Restaurant</title>
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
                <h3>Add Items</h3>
                <p>Fill the details to add new items to the menu!</p>
                <div class="form-group <?php echo (!empty($new_item_name_err)) ? 'has-error' : ''; ?>">
                    <label>Item Name</label>
                    <input type="text" name="new_item_name" class="form-control" value="<?php echo $new_item_name; ?>">
                    <span class="help-block"><?php echo $new_item_name_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($new_item_cost_err)) ? 'has-error' : ''; ?>">
                    <label>Item Cost (in Rupees)</label>
                    <input type="number" name="new_item_cost" class="form-control" value="<?php echo $new_item_cost; ?>">
                    <span class="help-block"><?php echo $new_item_cost_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($new_item_time_err)) ? 'has-error' : ''; ?>">
                    <label>Time Needed to Cook (in minutes)</label>
                    <input type="number" name="new_item_time" class="form-control" value="<?php echo $new_item_time; ?>">
                    <span class="help-block"><?php echo $new_item_time_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($new_item_description_err)) ? 'has-error' : ''; ?>">
                    <label>Item Description</label>
                    <input type="text" name="new_item_description" class="form-control" value="<?php echo $new_item_description; ?>">
                    <span class="help-block"><?php echo $new_item_description_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" name="add_item" class="btn btn-primary" value="Add">
                    <input type="reset" class="btn btn-default" value="Reset">
                </div>
            </form>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h3>Delete Items</h3>
                <p>Enter the ID of the item to be deleted</p>
                <div class="form-group <?php echo (!empty($del_item_id_err)) ? 'has-error' : ''; ?>">
                    <label>Item ID</label>
                    <input type="text" name="del_item_id" class="form-control" value="<?php echo $del_item_id; ?>">
                    <span class="help-block"><?php echo $del_item_id_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" name="delete_item" class="btn btn-primary" value="Delete">
                    <input type="reset" class="btn btn-default" value="Reset">
                </div>
            </form>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h3>Status Update</h3>
                <p>Enter the ID of the order whose status is to be updated</p>
                <div class="form-group <?php echo (!empty($status_order_id_err)) ? 'has-error' : ''; ?>">
                    <label>Order ID</label>
                    <input type="number" name="status_order_id" class="form-control" value="<?php echo $status_order_id; ?>">
                    <span class="help-block"><?php echo $status_order_id_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($status_new_status_err)) ? 'has-error' : ''; ?>">
                    <label for="newstatus">New Status</label>
                    <select id="newstatus" name="new_status">
                        <option value="Prepared">Prepared</option>
                        <option value="Delayed">Delayed</option>
                    </select>
                    <span class="help-block"><?php echo $status_new_status_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" name="update_order_status" class="btn btn-primary" value="Update">
                    <input type="reset" class="btn btn-default" value="Reset">
                </div>
            </form>
            <p>
                <a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a>
            </p>
        </div>
        <div style="float:right; width: 50%; padding: 40px;">
            <h2><?php echo "Menu"; ?></h2>
            <?php
                $sql = "SELECT p_id, p_name, p_cost, p_time, p_description FROM products WHERE r_uname = ?";
                if ($stmt = mysqli_prepare($link, $sql)){
                    mysqli_stmt_bind_param($stmt, "s", $param_r_username);
                    $param_r_username = $_SESSION["username"];
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
            <h2><?php echo "Orders"; ?></h2>
            <?php
                $sql = "SELECT orders.o_id, orders.total_cost , orders.expected_time, orders.order_status, assigned.da_uname FROM orders, assigned WHERE orders.r_uname = ? AND orders.o_id=assigned.o_id";
                if ($stmt = mysqli_prepare($link, $sql)){
                    mysqli_stmt_bind_param($stmt, "s", $param_r_username);
                    $param_r_username = $_SESSION["username"];
                    if (mysqli_stmt_execute($stmt)){
                        mysqli_stmt_store_result($stmt);
                        if (mysqli_stmt_num_rows($stmt) > 0){
                            echo "<table>";
                            echo "<tr><th>ID</th><th>Cost</th><th>Time for preparation</th><th>Status</th><th>Delivery Agent Username</th></tr>\n";
                            mysqli_stmt_bind_result($stmt, $id, $cost, $time, $status, $da_uname);
                            while (mysqli_stmt_fetch($stmt)){
                                echo "<tr><td>{$id}</td><td>{$cost}</td><td>{$time}</td><td>{$status}</td><td>{$da_uname}</td></tr>\n";
                            }
                            echo "</table>";
                        }
                        else {
                            echo "No orders";
                        }
                        mysqli_stmt_close($stmt);
                    }
                    else {
                        mysqli_stmt_close($stmt);
                        echo "Something went wrong. Please try again.";
                    }
                }
            ?>
            <h2><?php echo "Order Details"; ?></h2>
            <?php
                $sql = "SELECT contains.o_id, contains.p_id, contains.quantity FROM contains,orders WHERE orders.r_uname = ? AND orders.o_id=contains.o_id AND orders.order_status='Preparing'";
                if ($stmt = mysqli_prepare($link, $sql)){
                    mysqli_stmt_bind_param($stmt, "s", $param_r_username);
                    $param_r_username = $_SESSION["username"];
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
                            echo "Order Details not found";
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