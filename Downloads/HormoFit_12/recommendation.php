<?php
session_start();
include("db/config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$res = mysqli_query($conn, "SELECT * FROM health_data WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1");
$data = mysqli_fetch_assoc($res);

$prediction = null;
$prediction_error = '';
$prediction_saved = '';

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS prediction_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    age FLOAT NOT NULL,
    weight FLOAT NOT NULL,
    height FLOAT NOT NULL,
    cycle_length FLOAT NOT NULL,
    bmi FLOAT NOT NULL,
    probability FLOAT NOT NULL,
    prediction TINYINT(1) NOT NULL,
    accuracy FLOAT NOT NULL,
    risk_level VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

if(isset($_POST['predict_pcos'])){
    $age = isset($_POST['age']) ? floatval($_POST['age']) : 0;
    $weight_input = isset($_POST['weight']) ? floatval($_POST['weight']) : 0;
    $height_input = isset($_POST['height']) ? floatval($_POST['height']) : 0;
    $cycle_input = isset($_POST['cycle_length']) ? floatval($_POST['cycle_length']) : 0;
    $weight_gain = isset($_POST['weight_gain']) ? intval($_POST['weight_gain']) : 0;
    $hair_growth = isset($_POST['hair_growth']) ? intval($_POST['hair_growth']) : 0;
    $skin_darkening = isset($_POST['skin_darkening']) ? intval($_POST['skin_darkening']) : 0;
    $hair_loss = isset($_POST['hair_loss']) ? intval($_POST['hair_loss']) : 0;
    $pimples = isset($_POST['pimples']) ? intval($_POST['pimples']) : 0;
    $fast_food = isset($_POST['fast_food']) ? intval($_POST['fast_food']) : 0;
    $regular_exercise = isset($_POST['regular_exercise']) ? intval($_POST['regular_exercise']) : 0;

    $script = __DIR__ . "/scripts/predict_pcos.py";
    $arguments = escapeshellarg($script) . " " .
        escapeshellarg($age) . " " .
        escapeshellarg($weight_input) . " " .
        escapeshellarg($height_input) . " " .
        escapeshellarg($cycle_input) . " " .
        escapeshellarg($weight_gain) . " " .
        escapeshellarg($hair_growth) . " " .
        escapeshellarg($skin_darkening) . " " .
        escapeshellarg($hair_loss) . " " .
        escapeshellarg($pimples) . " " .
        escapeshellarg($fast_food) . " " .
        escapeshellarg($regular_exercise);

    $python_candidates = ["python", "python3", "py -3", "C:\\Python312\\python.exe", "C:\\Python311\\python.exe"];
    $output = '';

    foreach($python_candidates as $python_command){
        $command = $python_command . " " . $arguments . " 2>&1";
        $output = shell_exec($command);
        $prediction = json_decode($output, true);
        if($prediction && !isset($prediction['error'])){
            break;
        }
    }

    if(!$prediction || isset($prediction['error'])){
        $prediction_error = isset($prediction['error']) ? $prediction['error'] : trim($output);
        $prediction = null;
    } else {
        $risk_level = "Low";
        if(floatval($prediction['probability']) >= 70){
            $risk_level = "High";
        } else if(floatval($prediction['probability']) >= 40){
            $risk_level = "Moderate";
        }

        $bmi_value = floatval($prediction['features']['bmi']);
        $probability = floatval($prediction['probability']);
        $accuracy = floatval($prediction['accuracy']);
        $prediction_value = intval($prediction['prediction']);

        mysqli_query($conn, "INSERT INTO prediction_history(
            user_id, age, weight, height, cycle_length, bmi, probability, prediction, accuracy, risk_level
        ) VALUES(
            '$user_id', '$age', '$weight_input', '$height_input', '$cycle_input', '$bmi_value', '$probability', '$prediction_value', '$accuracy', '$risk_level'
        )");

        $prediction_saved = "Prediction saved to your history.";
    }
}

function field_value($name, $default = ''){
    return isset($_POST[$name]) ? htmlspecialchars($_POST[$name]) : htmlspecialchars($default);
}

function selected_value($name, $value, $default = ''){
    $current = isset($_POST[$name]) ? $_POST[$name] : $default;
    return (string)$current === (string)$value ? 'selected' : '';
}

$prediction_history = mysqli_query($conn, "SELECT * FROM prediction_history WHERE user_id='$user_id' ORDER BY created_at DESC LIMIT 5");
$latest_prediction = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM prediction_history WHERE user_id='$user_id' ORDER BY created_at DESC LIMIT 1"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Coach - HormoFit</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <a href="dashboard.php" class="brand">HormoFit</a>
        <div class="links">
            <a href="dashboard.php">Dashboard</a>
            <a href="track.php">Track Data</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="glass-container full-width">
        <h2>Your Personalized AI Coach</h2>

        <div class="recommendation-box">
            <h3>Random Forest PCOS Prediction</h3>
            <p class="text-center">Enter simple health details to get a Random Forest based PCOS prediction.</p>
            <?php if($prediction_error): ?>
                <div class="alert alert-error">Prediction error: <?php echo htmlspecialchars($prediction_error); ?></div>
            <?php endif; ?>
            <?php if($prediction_saved): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($prediction_saved); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" min="10" max="80" value="<?php echo field_value('age'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Weight (kg)</label>
                    <input type="number" step="0.1" name="weight" min="1" value="<?php echo field_value('weight', $data ? $data['weight'] : ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Height (cm)</label>
                    <input type="number" step="0.1" name="height" min="1" value="<?php echo field_value('height', $data ? $data['height'] : ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Cycle Length (days)</label>
                    <input type="number" name="cycle_length" min="1" value="<?php echo field_value('cycle_length', $data ? $data['cycle_length'] : ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Weight Gain</label>
                    <select name="weight_gain" required>
                        <option value="0" <?php echo selected_value('weight_gain', 0); ?>>No</option>
                        <option value="1" <?php echo selected_value('weight_gain', 1); ?>>Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Excess Hair Growth</label>
                    <select name="hair_growth" required>
                        <option value="0" <?php echo selected_value('hair_growth', 0); ?>>No</option>
                        <option value="1" <?php echo selected_value('hair_growth', 1); ?>>Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Skin Darkening</label>
                    <select name="skin_darkening" required>
                        <option value="0" <?php echo selected_value('skin_darkening', 0); ?>>No</option>
                        <option value="1" <?php echo selected_value('skin_darkening', 1); ?>>Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Hair Loss</label>
                    <select name="hair_loss" required>
                        <option value="0" <?php echo selected_value('hair_loss', 0); ?>>No</option>
                        <option value="1" <?php echo selected_value('hair_loss', 1); ?>>Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pimples</label>
                    <select name="pimples" required>
                        <option value="0" <?php echo selected_value('pimples', 0); ?>>No</option>
                        <option value="1" <?php echo selected_value('pimples', 1); ?>>Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Frequent Fast Food</label>
                    <select name="fast_food" required>
                        <option value="0" <?php echo selected_value('fast_food', 0); ?>>No</option>
                        <option value="1" <?php echo selected_value('fast_food', 1); ?>>Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Regular Exercise</label>
                    <select name="regular_exercise" required>
                        <option value="0" <?php echo selected_value('regular_exercise', 0); ?>>No</option>
                        <option value="1" <?php echo selected_value('regular_exercise', 1); ?>>Yes</option>
                    </select>
                </div>
                <button type="submit" name="predict_pcos" class="btn">Predict PCOS</button>
            </form>

            <?php if($prediction): ?>
                <div class="alert <?php echo $prediction['prediction'] ? 'alert-error' : 'alert-success'; ?> mt-2">
                    <strong>Model Result:</strong>
                    <?php echo $prediction['prediction'] ? 'PCOS likely' : 'PCOS not likely'; ?>
                    <br>
                    PCOS probability: <?php echo htmlspecialchars($prediction['probability']); ?>%
                    <br>
                    Test accuracy: <?php echo htmlspecialchars($prediction['accuracy']); ?>%
                    <br>
                    BMI used by model: <?php echo htmlspecialchars($prediction['features']['bmi']); ?>
                </div>
                <div class="insight-strip">
                    <div class="insight-pill">
                        <span class="pill-label">Risk Level</span>
                        <strong><?php echo floatval($prediction['probability']) >= 70 ? 'High' : (floatval($prediction['probability']) >= 40 ? 'Moderate' : 'Low'); ?></strong>
                    </div>
                    <div class="insight-pill">
                        <span class="pill-label">Cycle Input</span>
                        <strong><?php echo htmlspecialchars($prediction['features']['cycle_length']); ?> days</strong>
                    </div>
                    <div class="insight-pill">
                        <span class="pill-label">Exercise Habit</span>
                        <strong><?php echo intval($prediction['features']['regular_exercise']) === 1 ? 'Active' : 'Needs focus'; ?></strong>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if($latest_prediction): ?>
            <div class="recommendation-box">
                <h3>Prediction History</h3>
                <div class="table-wrap">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Risk</th>
                                <th>Probability</th>
                                <th>BMI</th>
                                <th>Cycle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($history_row = mysqli_fetch_assoc($prediction_history)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($history_row['created_at']))); ?></td>
                                    <td><?php echo htmlspecialchars($history_row['risk_level']); ?></td>
                                    <td><?php echo htmlspecialchars($history_row['probability']); ?>%</td>
                                    <td><?php echo htmlspecialchars($history_row['bmi']); ?></td>
                                    <td><?php echo htmlspecialchars($history_row['cycle_length']); ?> days</td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if(!$data): ?>
            <div class="alert alert-error">No health data found. Please <a href="track.php">log your data</a> to get personalized lifestyle recommendations.</div>
        <?php else: ?>
            <div class="recommendation-grid" style="display: grid; gap: 1rem;">
                <?php
                $weight = $data['weight'];
                $height_m = $data['height'] / 100;
                $bmi = $weight / ($height_m * $height_m);
                $bmi_round = round($bmi, 1);

                echo "<div class='recommendation-box'>";
                echo "<h3>BMI Analysis: " . $bmi_round . "</h3>";

                if($bmi > 25){
                    echo "<p><strong>Status:</strong> Overweight</p>";
                    echo "<p><strong>Diet Plan:</strong> Low-carb, high-protein diet. Incorporate leafy greens, nuts, and seed cycling to support hormonal balance.</p>";
                    echo "<p><strong>Workout Plan:</strong> 45 minutes of moderate cardio 4 times a week, combined with strength training.</p>";
                } else if($bmi < 18.5){
                    echo "<p><strong>Status:</strong> Underweight</p>";
                    echo "<p><strong>Diet Plan:</strong> High-protein, nutrient-dense meals with nuts, lean proteins, and whole grains.</p>";
                    echo "<p><strong>Workout Plan:</strong> Focus on strength training and avoid excessive cardio load.</p>";
                } else {
                    echo "<p><strong>Status:</strong> Normal Weight</p>";
                    echo "<p><strong>Diet Plan:</strong> Maintain a balanced plate rich in fiber, protein, and micronutrients.</p>";
                    echo "<p><strong>Workout Plan:</strong> Maintain a mix of yoga, pilates, and light strength training 3 days a week.</p>";
                }
                echo "</div>";

                echo "<div class='recommendation-box'>";
                echo "<h3>Cycle Insights</h3>";
                if($data['cycle_length'] > 35 || $data['cycle_length'] < 21) {
                    echo "<p><strong>Irregular Cycle Detected (" . $data['cycle_length'] . " days):</strong> Prioritize stress management, symptom journaling, and consistent sleep timing.</p>";
                } else {
                    echo "<p><strong>Normal Cycle Detected (" . $data['cycle_length'] . " days):</strong> Keep reinforcing your routine with sleep stability and regular movement.</p>";
                }
                echo "</div>";

                echo "<div class='recommendation-box'>";
                echo "<h3>Symptom Management: " . htmlspecialchars($data['symptoms']) . "</h3>";
                if(strpos(strtolower($data['symptoms']), 'cramps') !== false) {
                    echo "<p><strong>Actionable Tip:</strong> Increase magnesium-rich foods and use gentle heat for cramp relief.</p>";
                } else if(strpos(strtolower($data['symptoms']), 'acne') !== false) {
                    echo "<p><strong>Actionable Tip:</strong> Reduce refined sugar intake and focus on zinc-rich foods like seeds and legumes.</p>";
                } else if(strpos(strtolower($data['symptoms']), 'mood') !== false) {
                    echo "<p><strong>Actionable Tip:</strong> Support mood with sunlight exposure, breathing exercises, and stable meal timing.</p>";
                } else {
                    echo "<p><strong>Overall Tip:</strong> Stay hydrated and maintain a consistent sleep schedule to keep inflammation lower.</p>";
                }
                echo "</div>";

                echo "<div class='recommendation-box'>";
                echo "<h3>Next 7-Day Focus</h3>";
                if($bmi > 25 || $data['cycle_length'] > 35 || $data['cycle_length'] < 21) {
                    echo "<p><strong>Priority:</strong> Simplify your routine this week. Aim for consistent sleep, 20 to 30 minutes of walking, and lower-sugar meals.</p>";
                } else {
                    echo "<p><strong>Priority:</strong> You're in a stable range. Use this week to protect routine quality with strength work, hydration, and symptom journaling.</p>";
                }
                echo "</div>";
                ?>
            </div>

            <div class="text-center mt-2">
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
