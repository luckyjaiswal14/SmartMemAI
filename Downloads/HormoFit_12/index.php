<?php
session_start();
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HormoFit - PCOD Digital Twin</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <a href="index.php" class="brand">HormoFit</a>
        <div class="links">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </nav>

    <div class="glass-container text-center">
        <h1>Welcome to HormoFit 🎀</h1>
        <p>An Intelligent, Personalized PCOD Lifestyle Coach.</p>
        <p style="margin-bottom: 2rem;">Track your health, understand your cycle, and receive AI-guided personalized recommendations for your diet and fitness routine.</p>
        
        <a href="register.php" class="btn" style="margin-bottom: 1rem;">Start Your Journey</a>
        <a href="login.php" class="btn btn-secondary">Already have an account? Login</a>
    </div>
</body>
</html>
