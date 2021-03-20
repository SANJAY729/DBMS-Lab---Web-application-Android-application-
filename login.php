<?php
session_start();
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    if ($_SESSION["user_type"] == "Customer"){
        header("location: customer.php");
    }
    if ($_SESSION["user_type"] == "Restaurant"){
        header("location: restaurant.php");
    }
    if ($_SESSION["user_type"] == "Delivery Agent"){
        header("location: delivery_agent.php");
    }
    //exit;
}
require_once "config.php";

$username = $password = "";
$username_err = $password_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if (empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    }
    else {
        $username = trim($_POST["username"]);
    }
    if (empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    }
    else {
        $password = trim($_POST["password"]);
    }
    if (empty($username_err) && empty($password_err)){
        $sql = "SELECT * FROM login_details WHERE uname = ?";
        if ($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            if (mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $username, $valid_password, $user_type);
                    if(mysqli_stmt_fetch($stmt)){
                        if ($password == $valid_password){
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_type"] = $user_type;
                            $_SESSION["username"] = $username;

                            if ($_SESSION["user_type"] == "Customer"){
                                header("location: customer.php");
                            }
                            if ($_SESSION["user_type"] == "Restaurant"){
                                header("location: restaurant.php");
                            }
                            if ($_SESSION["user_type"] == "Delivery Agent"){
                                header("location: delivery_agent.php");
                            }
                        }
                        else {
                            $password_err = "Invalid Password";
                        }
                    }
                }
                else {
                    $username_err = "Invalid Username";
                }
            }
            else {
                echo "Something went wrong. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_close($link);
    }
}
?>
<DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
        <style type="text/css">
            body{ font: 14px sans-serif; }
        </style>
    </head>
    <body>
    <div style="float:left; width: 30%; padding: 40px;">
        <h2>Login</h2>
        <p>Please fill this form to login to your account.</p>
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
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-default" value="Reset">
            </div>
            <p>Don't have an account? <a href="signup.php">Sign Up Now</a>.</p>
        </form>
    </div>
    </body>
</html>
