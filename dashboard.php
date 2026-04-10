<?php
session_start();
include("db/config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['user_name'];
$wellness_success = '';

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS wellness_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    mood_level INT,
    stress_level INT,
    anxiety_level INT,
    sleep_hours FLOAT,
    energy_level INT,
    water_glasses INT,
    exercise_minutes INT,
    cravings_level INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

if(isset($_POST['save_wellness'])){
    $mood_level = intval($_POST['mood_level']);
    $stress_level = intval($_POST['stress_level']);
    $anxiety_level = intval($_POST['anxiety_level']);
    $sleep_hours = floatval($_POST['sleep_hours']);
    $energy_level = intval($_POST['energy_level']);
    $water_glasses = intval($_POST['water_glasses']);
    $exercise_minutes = intval($_POST['exercise_minutes']);
    $cravings_level = intval($_POST['cravings_level']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    $insert_wellness = mysqli_query($conn, "INSERT INTO wellness_data(
        user_id, mood_level, stress_level, anxiety_level, sleep_hours, energy_level,
        water_glasses, exercise_minutes, cravings_level, notes
    ) VALUES(
        '$user_id', '$mood_level', '$stress_level', '$anxiety_level', '$sleep_hours', '$energy_level',
        '$water_glasses', '$exercise_minutes', '$cravings_level', '$notes'
    )");

    if($insert_wellness){
        $wellness_success = "Wellness check-in saved successfully.";
    }
}

// Fetch latest health data
$res = mysqli_query($conn, "SELECT * FROM health_data WHERE user_id='$user_id' ORDER BY id DESC LIMIT 5");
$latest_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM health_data WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1"));
$latest_wellness = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM wellness_data WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1"));

// Chart data logic
$weights = [];
if(mysqli_num_rows($res) > 0) {
    mysqli_data_seek($res, 0);
    while($row = mysqli_fetch_assoc($res)){
        $weights[] = $row['weight'];
    }
}
$weights = array_reverse($weights);

$wellness_score = null;
$wellness_message = "Complete your daily check-in to see a wellness summary.";
if($latest_wellness){
    $positive_score = intval($latest_wellness['mood_level']) + intval($latest_wellness['energy_level']);
    $support_score = (6 - intval($latest_wellness['stress_level'])) + (6 - intval($latest_wellness['anxiety_level'])) + (6 - intval($latest_wellness['cravings_level']));
    $wellness_score = $positive_score + $support_score;

    if($wellness_score >= 20){
        $wellness_message = "Your check-in looks balanced today. Keep your sleep, hydration, and movement consistent.";
    } else if($wellness_score >= 14){
        $wellness_message = "Your check-in is moderate today. Try a short walk, steady meals, and a calming break.";
    } else {
        $wellness_message = "Your check-in shows extra stress today. Prioritize rest, hydration, and consider talking to someone you trust.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - HormoFit</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <a href="dashboard.php" class="brand">HormoFit</a>
        <div class="links">
            <a href="track.php">Track Data</a>
            <a href="recommendation.php">AI Coach</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="glass-container full-width">
        <h2>Welcome, <?php echo htmlspecialchars($name); ?> 🌸</h2>
        
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Latest Weight</h3>
                <div class="value"><?php echo $latest_data ? $latest_data['weight'] . ' kg' : '--'; ?></div>
            </div>
            <div class="dashboard-card">
                <h3>Cycle Length</h3>
                <div class="value"><?php echo $latest_data ? $latest_data['cycle_length'] . ' Days' : '--'; ?></div>
            </div>
            <div class="dashboard-card" style="grid-column: span auto; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                <h3>Your Next Action</h3>
                <a href="recommendation.php" class="btn" style="width: auto; padding: 0.5rem 1rem;">View Insights</a>
            </div>
            <div class="dashboard-card">
                <h3>Wellness Score</h3>
                <div class="value"><?php echo $wellness_score !== null ? $wellness_score . '/25' : '--'; ?></div>
            </div>
        </div>

        <?php if(!empty($weights)): ?>
        <h3 class="mt-2 text-center">Weight Trend (Last 5 Entries)</h3>
        <div class="graph-container">
            <?php 
                $max_weight = max($weights) > 0 ? max($weights) : 100;
                foreach($weights as $index => $w): 
                    $height_pct = ($w / $max_weight) * 100;
            ?>
            <div class="bar" style="height: <?php echo $height_pct; ?>%;">
                <span><?php echo $w; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-success mt-2">
            You haven't tracked any data yet. <a href="track.php">Start tracking now!</a>
        </div>
        <?php endif; ?>

        <div class="recommendation-box mt-2">
            <h3>Daily Mental Health Check-In</h3>
            <p class="text-center"><?php echo htmlspecialchars($wellness_message); ?></p>

            <?php if($wellness_success): ?>
                <div class="alert alert-success"><?php echo $wellness_success; ?></div>
            <?php endif; ?>

            <form method="POST" class="wellness-form">
                <div class="form-group">
                    <label>Mood Today (1 low - 5 great)</label>
                    <select name="mood_level" required>
                        <option value="1">1 - Low</option>
                        <option value="2">2 - Okay</option>
                        <option value="3" selected>3 - Neutral</option>
                        <option value="4">4 - Good</option>
                        <option value="5">5 - Great</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stress Level (1 low - 5 high)</label>
                    <select name="stress_level" required>
                        <option value="1">1 - Low</option>
                        <option value="2">2 - Mild</option>
                        <option value="3" selected>3 - Moderate</option>
                        <option value="4">4 - High</option>
                        <option value="5">5 - Very high</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Anxiety Level (1 low - 5 high)</label>
                    <select name="anxiety_level" required>
                        <option value="1">1 - Low</option>
                        <option value="2">2 - Mild</option>
                        <option value="3" selected>3 - Moderate</option>
                        <option value="4">4 - High</option>
                        <option value="5">5 - Very high</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Sleep Hours</label>
                    <input type="number" name="sleep_hours" min="0" max="24" step="0.5" placeholder="e.g. 7.5" required>
                </div>
                <div class="form-group">
                    <label>Energy Level (1 low - 5 high)</label>
                    <select name="energy_level" required>
                        <option value="1">1 - Low</option>
                        <option value="2">2 - Tired</option>
                        <option value="3" selected>3 - Okay</option>
                        <option value="4">4 - Good</option>
                        <option value="5">5 - High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Water Intake (glasses)</label>
                    <input type="number" name="water_glasses" min="0" max="30" placeholder="e.g. 8" required>
                </div>
                <div class="form-group">
                    <label>Exercise or Walk (minutes)</label>
                    <input type="number" name="exercise_minutes" min="0" max="300" placeholder="e.g. 30" required>
                </div>
                <div class="form-group">
                    <label>Cravings Level (1 low - 5 high)</label>
                    <select name="cravings_level" required>
                        <option value="1">1 - Low</option>
                        <option value="2">2 - Mild</option>
                        <option value="3" selected>3 - Moderate</option>
                        <option value="4">4 - High</option>
                        <option value="5">5 - Very high</option>
                    </select>
                </div>
                <div class="form-group full-row">
                    <label>Notes</label>
                    <textarea name="notes" rows="3" placeholder="Any pain, mood changes, food cravings, or stress triggers?"></textarea>
                </div>
                <button type="submit" name="save_wellness" class="btn full-row">Save Check-In</button>
            </form>
        </div>

        <?php if($latest_wellness): ?>
        <div class="recommendation-box">
            <h3>Latest Wellness Entry</h3>
            <div class="wellness-summary">
                <span>Mood: <?php echo htmlspecialchars($latest_wellness['mood_level']); ?>/5</span>
                <span>Stress: <?php echo htmlspecialchars($latest_wellness['stress_level']); ?>/5</span>
                <span>Anxiety: <?php echo htmlspecialchars($latest_wellness['anxiety_level']); ?>/5</span>
                <span>Sleep: <?php echo htmlspecialchars($latest_wellness['sleep_hours']); ?> hrs</span>
                <span>Energy: <?php echo htmlspecialchars($latest_wellness['energy_level']); ?>/5</span>
                <span>Water: <?php echo htmlspecialchars($latest_wellness['water_glasses']); ?> glasses</span>
                <span>Exercise: <?php echo htmlspecialchars($latest_wellness['exercise_minutes']); ?> min</span>
                <span>Cravings: <?php echo htmlspecialchars($latest_wellness['cravings_level']); ?>/5</span>
            </div>
            <?php if(!empty($latest_wellness['notes'])): ?>
                <p><strong>Notes:</strong> <?php echo htmlspecialchars($latest_wellness['notes']); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>
