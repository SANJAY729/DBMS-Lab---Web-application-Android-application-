<?php
// KoMATO
require_once "config.php";
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
$time_order_id = $time_new_time = $status_order_id  = $status_new_status = "";
$time_order_id_err = $time_new_time_err = $status_order_id_err = $status_new_status_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (!empty($_POST['time_order'])){
        if (empty(trim($_POST["order_id"]))){
            $time_order_id_err = "Please enter Order ID.";     
        }
        else {
            $time_order_id = trim($_POST["order_id"]);
        }
        if (empty(trim($_POST["new_time"]))){
            $time_new_time_err = "Please enter new time";
        }
        else {
            $time_new_time = $_POST["new_time"];
        }
        if (empty($time_new_time_err) && empty($time_order_id_err)){
            $sql = "UPDATE assigned SET delivery_time = ? WHERE o_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ii", $param_time, $param_o_id);
                $param_time = $time_new_time;
                $param_o_id = $time_order_id;
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_close($stmt);
                }
                else {
                    echo "Something went wrong. Please try again.";
                    mysqli_stmt_close($stmt);
                }
            }

            $sql = "UPDATE places,assigned,orders SET places.total_time = assigned.delivery_time + orders.expected_time 
            WHERE places.o_id = assigned.o_id AND assigned.o_id = orders.o_id AND places.o_id = ?;";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "i", $param_o_id);
                $param_o_id = $time_order_id;
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_close($stmt);
                    $time_new_time_err = "Succesfully updated delivery time";
                }
                else {
                    echo "Something went wrong. Please try again.";
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
    if (!empty($_POST['update_order_status'])){
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
            $sql = "SELECT * FROM assigned WHERE o_id = ? and da_uname = ?";
            if ($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "is", $param_o_id, $param_da_username);
                $param_o_id = $status_order_id;
                $param_da_username = $_SESSION["username"];
                if (mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) == 1){
                        mysqli_stmt_close($stmt);
                        $sql = "UPDATE orders SET order_status = ? WHERE o_id = ?";
                        if ($stmt = mysqli_prepare($link, $sql)){
                            mysqli_stmt_bind_param($stmt, "si", $param_o_status, $param_o_id);
                            $param_o_status = $status_new_status;
                            $param_o_id = $status_order_id;
                            if (mysqli_stmt_execute($stmt)){
                                mysqli_stmt_close($stmt);
                                if ($status_new_status == "Completed"){
                                    /*$sql = "DELETE FROM assigned WHERE o_id=?";
                                    if ($stmt = mysqli_prepare($link, $sql)){
                                        mysqli_stmt_bind_param($stmt, "i", $param_o_id);
                                        $param_o_id = $status_order_id;
                                        if (mysqli_stmt_execute($stmt)){
                                            mysqli_stmt_close($stmt);
                                        }
                                        else {
                                            mysqli_stmt_close($stmt);
                                            echo "Something went wrong. Please try again.";
                                        }
                                    }
                                    $sql = "DELETE FROM contains WHERE o_id=?";
                                    if ($stmt = mysqli_prepare($link, $sql)){
                                        mysqli_stmt_bind_param($stmt, "i", $param_o_id);
                                        $param_o_id = $status_order_id;
                                        if (mysqli_stmt_execute($stmt)){
                                            mysqli_stmt_close($stmt);
                                        }
                                        else {
                                            mysqli_stmt_close($stmt);
                                            echo "Something went wrong. Please try again.";
                                        }
                                    }*/
                                }
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
        <title>Delivery Agent</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
        <style type="text/css">
            body{
                font: 14px sans-serif;
                text-align: left;
                background-image: url('https://media.istockphoto.com/photos/light-blue-paper-color-with-texture-for-background-picture-id1095286208?k=6&m=1095286208&s=612x612&w=0&h=YRLtyfrpIsNzmuWxNYOwboXCipAWV8zM-NMScsCT2TQ=');
                background-repeat: no-repeat;
                background-attachment: fixed;
                background-size: 100% 100%;
            }
            table, th, td ,tr{
                border: 1px solid black;
                align: center;
                padding: 2%;
                width: 600px;
            }
        </style>
    </head>
    <body>
        <header style = "margin-top: 40px; text-align: center;">
            <h1>KoMATO</h1>
        </header>
        <div style="float:left; width: 35%; padding: 60px;">
            <h2><?php echo "Welcome, ", $_SESSION["username"]; ?></h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h3>Expected Time for Delivery</h3>
                <p>Enter the Order ID</p>
                <div class="form-group <?php echo (!empty($time_order_id_err)) ? 'has-error' : ''; ?>">
                    <label>Order ID</label>
                    <input type="number" name="order_id" class="form-control" value="<?php echo $time_order_id; ?>">
                    <span class="help-block"><?php echo $time_order_id_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($time_new_time_err)) ? 'has-error' : ''; ?>">
                    <label>Time Required (in min)</label>
                    <input type="number" name="new_time" class="form-control" value="<?php echo $time_new_time; ?>">
                    <span class="help-block"><?php echo $time_new_time_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" name="time_order" class="btn btn-primary" value="Update Required Time">
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
                        <option value="Out for Delivery">Out for Delivery</option>
                        <option value="Completed">Completed</option>
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
        <div style="float:right; width: 50%; padding: 60px;">
            <h2><?php echo "Order Details"; ?></h2>
            <?php
                $sql = "SELECT assigned.o_id, restaurants.r_name, restaurants.r_address, customers.c_name, customers.c_address, orders.order_status FROM assigned, restaurants, customers, orders, places 
                WHERE assigned.da_uname = ? AND assigned.o_id=orders.o_id AND orders.r_uname=restaurants.r_uname AND places.o_id=orders.o_id AND places.c_uname=customers.c_uname";
                if ($stmt = mysqli_prepare($link, $sql)){
                    mysqli_stmt_bind_param($stmt, "s", $param_da_username);
                    $param_da_username = $_SESSION["username"];
                    if (mysqli_stmt_execute($stmt)){
                        mysqli_stmt_store_result($stmt);
                        if (mysqli_stmt_num_rows($stmt) > 0){
                            echo "<table>";
                            echo "<tr><th>Order ID</th><th>Restaurant Name</th><th>Restaurant Address</th><th>Customer Name</th><th>Customer Address</th><th>Status</th></tr>\n";
                            mysqli_stmt_bind_result($stmt, $o_id, $r_name, $r_uname, $c_name, $c_uname, $status);
                            while (mysqli_stmt_fetch($stmt)){
                                echo "<tr><td>{$o_id}</td><td>{$r_name}</td><td>{$r_uname}</td><td>{$c_name}</td><td>{$c_uname}</td><td>{$status}</td></tr>\n";
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