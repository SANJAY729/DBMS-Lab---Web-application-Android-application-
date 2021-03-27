<?php
// KoMATO
require_once "config.php";

$username = $password = $confirm_password = $user_type = $name = $address = $phone_number = "";
$username_err = $password_err = $confirm_password_err = $user_type_err = $name_err = $address_err = $phone_number_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (empty(trim($_POST["username"]))){
        $username_err = "Enter username";
    }
    else {
        $sql = "SELECT * FROM login_details WHERE uname = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                }
                else {
                    $username = trim($_POST["username"]);
                }
            }
            else {
                echo "Something went wrong. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    if (empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    }
    else {
        $password = trim($_POST["password"]);
    }
    if (empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm your password.";
    }
    else {
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    if (empty(trim($_POST["user_type"]))){
        $user_type_err = "Please enter a usertype.";
    }
    else {
        $user_type = trim($_POST["user_type"]);
    }
    if (empty(trim($_POST["name"]))){
        $name_err = "Please enter a name.";
    }
    else {
        $name = trim($_POST["name"]);
    }
    if (empty(trim($_POST["address"]))){
        $address_err = "Please enter a address.";     
    }
    else {
        $address = trim($_POST["address"]);
    }
    if (empty(trim($_POST["phone_number"]))){
        $phone_number_err = "Please enter a phone number.";     
    }
    else {
        $phone_number = trim($_POST["phone_number"]);
    }
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($user_type_err) && empty($name_err) && empty($address_err) && empty($phone_number_err)){
        $sql = "INSERT INTO login_details (uname, pwd, utype) VALUES (?,?,?)";
        if ($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_password, $param_usertype);
            $param_username = $username;
            $param_password = $password;
            $param_usertype = $user_type;
            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_close($stmt);
                if ($user_type == "Customer"){
                    $sql = "INSERT INTO customers (c_uname,c_name,c_address,c_phno) VALUES (?,?,?,?)";
                }
                if ($user_type == "Restaurant"){
                    $sql = "INSERT INTO restaurants (r_uname,r_name,r_address,r_phno) VALUES (?,?,?,?)";
                }
                if ($user_type == "Delivery Agent"){
                    $sql = "INSERT INTO delivery_agents (da_uname,da_name,da_address,da_phno) VALUES (?,?,?,?)";
                }
                if ($stmt = mysqli_prepare($link, $sql)){
                    mysqli_stmt_bind_param($stmt, "sssi", $param_username, $param_name, $param_address, $param_phone_number);
                    $param_username = $username;
                    $param_name = $name;
                    $param_address = $address;
                    $param_phone_number = $phone_number;
                    if (mysqli_stmt_execute($stmt)){
                        mysqli_stmt_close($stmt);
                        header("location: login.php");
                    }
                    else {
                        mysqli_stmt_close($stmt);
                        echo "Something went wrong. Please try again.";
                    }
                }
            }
            else {
                echo "Something went wrong. Please try again.";
                mysqli_stmt_close($stmt);
            }
            
        }
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Sign Up</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
        <style type="text/css">
            body{
                font: 14px sans-serif;
                text-align: left;
            }
        </style>
    </head>
    <body>
        <div style="float:left; width: 30%; padding: 40px;">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($user_type_err)) ? 'has-error' : ''; ?>">
                <label for="utypes">User Type</label>
                <select id="utypes" name="user_type">
                    <option value="Customer">Customer</option>
                    <option value="Restaurant">Restaurant</option>
                    <option value="Delivery Agent">Delivery Agent</option>
                </select>
                <span class="help-block"><?php echo $user_type_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
                <span class="help-block"><?php echo $name_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($address_err)) ? 'has-error' : ''; ?>">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="<?php echo $address; ?>">
                <span class="help-block"><?php echo $address_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($phone_number_err)) ? 'has-error' : ''; ?>">
                <label>Phone Number</label>
                <input type="number" name="phone_number" class="form-control" value="<?php echo $phone_number; ?>">
                <span class="help-block"><?php echo $phone_number_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-default" value="Reset">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
        </div>
    </body>
</html>