<?php
session_start();
include("db/config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$success = '';

if(isset($_POST['save'])){
    $user_id = $_SESSION['user_id'];
    $weight = floatval($_POST['weight']);
    $height = floatval($_POST['height']);
    $cycle = intval($_POST['cycle']);
    $symptoms = mysqli_real_escape_string($conn, $_POST['symptoms']);

    $insert = mysqli_query($conn, "INSERT INTO health_data(user_id, weight, height, cycle_length, symptoms)
    VALUES('$user_id', '$weight', '$height', '$cycle', '$symptoms')");

    if($insert){
        $success = "Data logged successfully! Check your new recommendations.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Health - HormoFit</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <a href="dashboard.php" class="brand">HormoFit</a>
        <div class="links">
            <a href="dashboard.php">Dashboard</a>
            <a href="recommendation.php">AI Coach</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="glass-container">
        <h2>Log Your Health Data 📝</h2>
        
        <?php if($success): ?> 
            <div class="alert alert-success"><?php echo $success; ?></div> 
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Weight (kg)</label>
                <input type="number" step="0.1" name="weight" placeholder="e.g. 65.5" required>
            </div>
            <div class="form-group">
                <label>Height (cm)</label>
                <input type="number" step="0.1" name="height" placeholder="e.g. 165" required>
            </div>
            <div class="form-group">
                <label>Cycle Length (Days)</label>
                <input type="number" name="cycle" placeholder="e.g. 28" required>
            </div>
            <div class="form-group">
                <label>Symptoms (Optional)</label>
                <select name="symptoms">
                    <option value="None">None</option>
                    <option value="Mild Cramps, Fatigue">Mild Cramps, Fatigue</option>
                    <option value="Severe Cramps, Acne">Severe Cramps, Acne</option>
                    <option value="Mood Swings, Bloating">Mood Swings, Bloating</option>
                    <option value="Irregular periods, Hair loss">Irregular periods, Hair loss</option>
                </select>
            </div>
            <button type="submit" name="save" class="btn">Log Data</button>
        </form>
    </div>
</body>
</html>
