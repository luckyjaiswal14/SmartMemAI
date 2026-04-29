<?php
session_start();
include("db/config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$success = '';
$user_id = $_SESSION['user_id'];

if(isset($_POST['save'])){
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

$history = mysqli_query($conn, "SELECT * FROM health_data WHERE user_id='$user_id' ORDER BY created_at DESC LIMIT 10");
$latest_entry = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM health_data WHERE user_id='$user_id' ORDER BY created_at DESC LIMIT 1"));
$average_weight_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(weight) AS avg_weight FROM health_data WHERE user_id='$user_id'"));
$average_cycle_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(cycle_length) AS avg_cycle FROM health_data WHERE user_id='$user_id'"));
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

    <div class="glass-container full-width">
        <h2>Log Your Health Data</h2>

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

        <div class="dashboard-grid mt-2 compact-grid">
            <div class="dashboard-card">
                <h3>Latest Entry</h3>
                <div class="value small-value"><?php echo $latest_entry ? htmlspecialchars($latest_entry['weight']) . ' kg' : '--'; ?></div>
            </div>
            <div class="dashboard-card">
                <h3>Average Weight</h3>
                <div class="value small-value"><?php echo $average_weight_row && $average_weight_row['avg_weight'] ? round($average_weight_row['avg_weight'], 1) . ' kg' : '--'; ?></div>
            </div>
            <div class="dashboard-card">
                <h3>Average Cycle</h3>
                <div class="value small-value"><?php echo $average_cycle_row && $average_cycle_row['avg_cycle'] ? round($average_cycle_row['avg_cycle']) . ' d' : '--'; ?></div>
            </div>
        </div>

        <div class="recommendation-box mt-2">
            <h3>Recent Health History</h3>
            <?php if($history && mysqli_num_rows($history) > 0): ?>
                <div class="table-wrap">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Weight</th>
                                <th>Height</th>
                                <th>Cycle</th>
                                <th>Symptoms</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($history)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['created_at']))); ?></td>
                                    <td><?php echo htmlspecialchars($row['weight']); ?> kg</td>
                                    <td><?php echo htmlspecialchars($row['height']); ?> cm</td>
                                    <td><?php echo htmlspecialchars($row['cycle_length']); ?> days</td>
                                    <td><?php echo htmlspecialchars($row['symptoms']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">Your logged entries will appear here once you start tracking.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
