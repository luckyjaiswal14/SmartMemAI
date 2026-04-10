<?php
session_start();
include("db/config.php");

$error = '';

if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $res = mysqli_query($conn, "SELECT id, name, password FROM users WHERE email='$email'");
    
    if(mysqli_num_rows($res) > 0){
        $user = mysqli_fetch_assoc($res);
        if(password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - HormoFit</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <a href="index.php" class="brand">HormoFit</a>
        <div class="links">
            <a href="register.php">Register</a>
        </div>
    </nav>

    <div class="glass-container">
        <h2>Welcome Back ✨</h2>
        
        <?php if($error): ?> <div class="alert alert-error"><?php echo $error; ?></div> <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn">Login</button>
        </form>
        <p class="text-center" style="margin-top: 1rem;">
            <a href="register.php" style="color: var(--primary);">Don't have an account?</a>
        </p>
    </div>
</body>
</html>
