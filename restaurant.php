<?php
require_once "config.php";
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$new_item_name = $new_item_cost = $new_item_time = $new_item_description = $del_item_id = "";
$new_item_name_err = $new_item_cost_err = $new_item_time_err = $new_item_description_err = $del_item_id_err = "";

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
                    //header("location: restaurant.php");
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
                                //header("location: restaurant.php");
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
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Restaurant</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
        <style type="text/css">
            body{ font: 14px sans-serif; }
            table, th, td {
                border: 1px solid black;
            }
        </style>
    </head>
    <body>
        <div style="float:left; width: 30%; padding: 40px;">
            <h2><?php echo $_SESSION["username"]; ?></h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
                </div>
            </form>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <p>Type the ID of the item to be deleted</p>
                <div class="form-group <?php echo (!empty($del_item_id_err)) ? 'has-error' : ''; ?>">
                    <label>Item ID</label>
                    <input type="text" name="del_item_id" class="form-control" value="<?php echo $del_item_id; ?>">
                    <span class="help-block"><?php echo $del_item_id_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" name="delete_item" class="btn btn-primary" value="Delete">
                </div>
            </form>

            <p>
                <a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a>
            </p>
        </div>
        <div style="float:right; width: 50%; padding: 20px;">
            <h3><?php echo "ALLO"; ?></h3>
            <?php
                $sql = "SELECT p_id, p_name, p_cost, p_time, p_description FROM products WHERE r_uname = ?";
                if ($stmt = mysqli_prepare($link, $sql)){
                    mysqli_stmt_bind_param($stmt, "s", $param_r_username);
                    $param_r_username = $_SESSION["username"];
                    if (mysqli_stmt_execute($stmt)){
                        mysqli_stmt_store_result($stmt);
                        if (mysqli_stmt_num_rows($stmt) > 0){
                            echo mysqli_stmt_num_rows($stmt);
                            echo "<table>";
                            echo "<tr><th>ID</th><th>Name</th><th>Cost</th><th>Time</th><th>Description</th></tr>\n";
                            while ($row = mysqli_fetch_assoc($stmt)){
                                // echo "<tr><td>{$row["p_id"]}</td><td>{$row["p_name"]}</td><td>{$row["p_cost"]}</td><td>{$row["p_time"]}</td><td>{$row["p_description"]}</td></tr>\n";
                                echo $row;
                            }
                            echo "</table>";
                        }
                        else {
                            echo "No Food Item";
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