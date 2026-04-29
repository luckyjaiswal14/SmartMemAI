<?php
session_start();
include("db/config.php");

$error = '';
$success = '';

if(isset($_POST['submit'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Split name into first and last name
    $name_parts = explode(' ', trim($name), 2);
    $first_name = $name_parts[0];
    $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

    // Check if email exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        $error = "Email already registered!";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO users(first_name, last_name, email, password) VALUES('$first_name', '$last_name', '$email', '$pass')");
        if($insert){
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Something went wrong.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - HormoFit</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <a href="index.php" class="brand">HormoFit</a>
        <div class="links">
            <a href="login.php">Login</a>
        </div>
    </nav>

    <div class="glass-container">
        <h2>Create an Account 🌺</h2>
        
        <?php if($error): ?> <div class="alert alert-error"><?php echo $error; ?></div> <?php endif; ?>
        <?php if($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Jane Doe" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="jane@example.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="submit" class="btn">Register</button>
        </form>
        <p class="text-center" style="margin-top: 1rem;">
            <a href="login.php" style="color: var(--primary);">Already have an account?</a>
        </p>
    </div>
</body>
</html>
