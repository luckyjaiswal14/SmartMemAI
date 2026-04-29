<?php
session_start();
include("db/config.php");
include("config/ai.php");
include("includes/dashboard_helpers.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

foreach([
    "CREATE TABLE IF NOT EXISTS wellness_data (
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
    )",
    "CREATE TABLE IF NOT EXISTS user_goals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        water_goal INT DEFAULT 8,
        sleep_goal FLOAT DEFAULT 8,
        exercise_goal INT DEFAULT 30,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS pcos_assessments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        age INT NOT NULL,
        cycle_pattern VARCHAR(30) NOT NULL,
        skipped_periods VARCHAR(20) NOT NULL,
        facial_hair TINYINT(1) NOT NULL DEFAULT 0,
        persistent_acne TINYINT(1) NOT NULL DEFAULT 0,
        scalp_hair_thinning TINYINT(1) NOT NULL DEFAULT 0,
        unexplained_weight_gain TINYINT(1) NOT NULL DEFAULT 0,
        dark_skin_patches TINYINT(1) NOT NULL DEFAULT 0,
        high_sugar_cravings TINYINT(1) NOT NULL DEFAULT 0,
        family_history TINYINT(1) NOT NULL DEFAULT 0,
        trying_to_conceive TINYINT(1) NOT NULL DEFAULT 0,
        pelvic_pain TINYINT(1) NOT NULL DEFAULT 0,
        activity_level VARCHAR(20) NOT NULL,
        stress_load VARCHAR(20) NOT NULL,
        risk_score INT NOT NULL,
        risk_band VARCHAR(20) NOT NULL,
        ai_recommendation TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"
] as $query){
    mysqli_query($conn, $query);
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['user_name'];
$wellness_success = '';
$goal_success = '';
$assessment_success = '';
$assessment_notice = '';

mysqli_query($conn, "INSERT IGNORE INTO user_goals(user_id) VALUES('$user_id')");

if(isset($_POST['save_wellness'])){
    $wellness = [
        intval($_POST['mood_level']), intval($_POST['stress_level']), intval($_POST['anxiety_level']),
        floatval($_POST['sleep_hours']), intval($_POST['energy_level']), intval($_POST['water_glasses']),
        intval($_POST['exercise_minutes']), intval($_POST['cravings_level']), mysqli_real_escape_string($conn, $_POST['notes'])
    ];
    if(mysqli_query($conn, "INSERT INTO wellness_data(user_id, mood_level, stress_level, anxiety_level, sleep_hours, energy_level, water_glasses, exercise_minutes, cravings_level, notes) VALUES('$user_id', '$wellness[0]', '$wellness[1]', '$wellness[2]', '$wellness[3]', '$wellness[4]', '$wellness[5]', '$wellness[6]', '$wellness[7]', '$wellness[8]')")){
        $wellness_success = "Wellness check-in saved successfully.";
    }
}

if(isset($_POST['save_goals'])){
    $water_goal = max(1, intval($_POST['water_goal']));
    $sleep_goal = max(1, floatval($_POST['sleep_goal']));
    $exercise_goal = max(0, intval($_POST['exercise_goal']));
    if(mysqli_query($conn, "UPDATE user_goals SET water_goal='$water_goal', sleep_goal='$sleep_goal', exercise_goal='$exercise_goal' WHERE user_id='$user_id'")){
        $goal_success = "Daily goals updated.";
    }
}

$latest_data = fetch_one($conn, "SELECT * FROM health_data WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1");
$latest_wellness = fetch_one($conn, "SELECT * FROM wellness_data WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1");

if(isset($_POST['save_assessment'])){
    $answers = [
        'age' => max(12, intval($_POST['age'])),
        'cycle_pattern' => $_POST['cycle_pattern'],
        'skipped_periods' => $_POST['skipped_periods'],
        'activity_level' => $_POST['activity_level'],
        'stress_load' => $_POST['stress_load']
    ];
    foreach(['facial_hair', 'persistent_acne', 'scalp_hair_thinning', 'unexplained_weight_gain', 'dark_skin_patches', 'high_sugar_cravings', 'family_history', 'trying_to_conceive', 'pelvic_pain'] as $key){
        $answers[$key] = isset($_POST[$key]) ? 1 : 0;
    }

    list($risk_score, $risk_band) = build_pcos_risk($answers);
    list($ai_ok, $ai_text) = generate_gemini_recommendation($gemini_api_key, $gemini_models, $gemini_api_versions, $answers, $risk_score, $risk_band, $latest_data, $latest_wellness);
    if(!$ai_ok){
        $assessment_notice = $ai_text;
        $ai_text = fallback_recommendation($answers, $risk_band, $latest_data, $latest_wellness);
    }

    $escaped = array_map(fn($value) => mysqli_real_escape_string($conn, (string)$value), [
        'cycle_pattern' => $answers['cycle_pattern'],
        'skipped_periods' => $answers['skipped_periods'],
        'activity_level' => $answers['activity_level'],
        'stress_load' => $answers['stress_load'],
        'risk_band' => $risk_band,
        'ai_text' => $ai_text
    ]);

    $insert_assessment = mysqli_query($conn, "INSERT INTO pcos_assessments(
        user_id, age, cycle_pattern, skipped_periods, facial_hair, persistent_acne,
        scalp_hair_thinning, unexplained_weight_gain, dark_skin_patches, high_sugar_cravings,
        family_history, trying_to_conceive, pelvic_pain, activity_level, stress_load,
        risk_score, risk_band, ai_recommendation
    ) VALUES(
        '$user_id', '{$answers['age']}', '{$escaped['cycle_pattern']}', '{$escaped['skipped_periods']}', '{$answers['facial_hair']}',
        '{$answers['persistent_acne']}', '{$answers['scalp_hair_thinning']}', '{$answers['unexplained_weight_gain']}',
        '{$answers['dark_skin_patches']}', '{$answers['high_sugar_cravings']}', '{$answers['family_history']}',
        '{$answers['trying_to_conceive']}', '{$answers['pelvic_pain']}', '{$escaped['activity_level']}', '{$escaped['stress_load']}',
        '$risk_score', '{$escaped['risk_band']}', '{$escaped['ai_text']}'
    )");

    if($insert_assessment){
        $assessment_success = "Assessment saved and recommendations updated.";
    }
}

$weights_result = mysqli_query($conn, "SELECT weight FROM health_data WHERE user_id='$user_id' ORDER BY id DESC LIMIT 5");
$latest_data = fetch_one($conn, "SELECT * FROM health_data WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1");
$latest_wellness = fetch_one($conn, "SELECT * FROM wellness_data WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1");
$goals = fetch_one($conn, "SELECT * FROM user_goals WHERE user_id='$user_id' LIMIT 1");
$latest_assessment = fetch_one($conn, "SELECT * FROM pcos_assessments WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1");

$weights = [];
while($weights_result && ($row = mysqli_fetch_assoc($weights_result))){
    $weights[] = $row['weight'];
}
$weights = array_reverse($weights);

$bmi_value = null;
$cycle_status = "No cycle data yet";
if($latest_data && floatval($latest_data['height']) > 0){
    $height_m = floatval($latest_data['height']) / 100;
    $bmi_value = round(floatval($latest_data['weight']) / ($height_m * $height_m), 1);
    $cycle_status = intval($latest_data['cycle_length']) < 21 || intval($latest_data['cycle_length']) > 35 ? "Cycle needs attention" : "Cycle within usual range";
}

$goal_progress = ['water' => false, 'sleep' => false, 'exercise' => false];
$wellness_score = null;
$wellness_message = "Complete your daily check-in to see a wellness summary.";
if($latest_wellness){
    $wellness_score = intval($latest_wellness['mood_level']) + intval($latest_wellness['energy_level']) + (6 - intval($latest_wellness['stress_level'])) + (6 - intval($latest_wellness['anxiety_level'])) + (6 - intval($latest_wellness['cravings_level']));
    $wellness_message = $wellness_score >= 20 ? "Your check-in looks balanced today. Keep your sleep, hydration, and movement consistent." : ($wellness_score >= 14 ? "Your check-in is moderate today. Try a short walk, steady meals, and a calming break." : "Your check-in shows extra stress today. Prioritize rest, hydration, and consider talking to someone you trust.");
    if($goals){
        $goal_progress = [
            'water' => intval($latest_wellness['water_glasses']) >= intval($goals['water_goal']),
            'sleep' => floatval($latest_wellness['sleep_hours']) >= floatval($goals['sleep_goal']),
            'exercise' => intval($latest_wellness['exercise_minutes']) >= intval($goals['exercise_goal'])
        ];
    }
}

$completed_goals = count(array_filter($goal_progress));
$consistency_message = $latest_wellness ? $completed_goals . " of 3 daily goals completed" : "Set your goals and complete a check-in";
$local_assessment_summary = $latest_assessment ? build_local_assessment_summary($latest_assessment, $latest_data, $latest_wellness) : null;
$dashboard_cards = [
    ["Latest Weight", $latest_data ? $latest_data['weight'] . ' kg' : '--', true],
    ["Cycle Length", $latest_data ? $latest_data['cycle_length'] . ' Days' : '--', true],
    ["Latest BMI", $bmi_value !== null ? $bmi_value : '--', true],
    ["Goal Consistency", $latest_wellness ? $completed_goals . '/3' : '--', true],
    ["Wellness Score", $wellness_score !== null ? $wellness_score . '/25' : '--', true],
    ["Cycle Status", $cycle_status, false]
];
$assessment_selects = [
    ['age', 'Age', 'number', ['min' => 12, 'max' => 55], $latest_assessment ? $latest_assessment['age'] : '24'],
    ['cycle_pattern', 'How regular are your periods?', 'select', ['regular' => 'Mostly regular', 'sometimes_irregular' => 'Sometimes irregular', 'irregular' => 'Frequently irregular', 'absent' => 'Often absent'], $latest_assessment ? $latest_assessment['cycle_pattern'] : 'regular'],
    ['skipped_periods', 'How often do you skip periods in a year?', 'select', ['0' => 'Rarely or never', '1_2' => '1 to 2 times', '3_plus' => '3 or more times'], $latest_assessment ? $latest_assessment['skipped_periods'] : '0'],
    ['activity_level', 'How active are you most weeks?', 'select', ['active' => 'Active', 'moderate' => 'Moderate', 'low' => 'Low'], $latest_assessment ? $latest_assessment['activity_level'] : 'moderate'],
    ['stress_load', 'Current stress load', 'select', ['low' => 'Low', 'moderate' => 'Moderate', 'high' => 'High'], $latest_assessment ? $latest_assessment['stress_load'] : 'moderate']
];
$assessment_checks = [
    'facial_hair' => 'Extra facial or body hair',
    'persistent_acne' => 'Persistent acne',
    'scalp_hair_thinning' => 'Scalp hair thinning',
    'unexplained_weight_gain' => 'Unexplained weight gain',
    'dark_skin_patches' => 'Dark skin patches around neck or underarms',
    'high_sugar_cravings' => 'Strong sugar cravings or energy crashes',
    'family_history' => 'Family history of PCOS or diabetes',
    'trying_to_conceive' => 'Trying to conceive',
    'pelvic_pain' => 'Ongoing pelvic pain or heavy discomfort'
];
$summary_chips = $latest_assessment ? [
    "Risk Score: " . $latest_assessment['risk_score'],
    "Age: " . $latest_assessment['age'],
    "Activity: " . ucfirst(str_replace('_', ' ', $latest_assessment['activity_level'])),
    "Stress: " . ucfirst($latest_assessment['stress_load'])
] : [];
$wellness_items = $latest_wellness ? [
    "Mood" => $latest_wellness['mood_level'] . '/5',
    "Stress" => $latest_wellness['stress_level'] . '/5',
    "Anxiety" => $latest_wellness['anxiety_level'] . '/5',
    "Sleep" => $latest_wellness['sleep_hours'] . ' hrs',
    "Energy" => $latest_wellness['energy_level'] . '/5',
    "Water" => $latest_wellness['water_glasses'] . ' glasses',
    "Exercise" => $latest_wellness['exercise_minutes'] . ' min',
    "Cravings" => $latest_wellness['cravings_level'] . '/5'
] : [];
$wellness_fields = [
    ['mood_level', 'Mood Today (1 low - 5 great)', ['1' => '1 - Low', '2' => '2 - Okay', '3' => '3 - Neutral', '4' => '4 - Good', '5' => '5 - Great']],
    ['stress_level', 'Stress Level (1 low - 5 high)', ['1' => '1 - Low', '2' => '2 - Mild', '3' => '3 - Moderate', '4' => '4 - High', '5' => '5 - Very high']],
    ['anxiety_level', 'Anxiety Level (1 low - 5 high)', ['1' => '1 - Low', '2' => '2 - Mild', '3' => '3 - Moderate', '4' => '4 - High', '5' => '5 - Very high']],
    ['energy_level', 'Energy Level (1 low - 5 high)', ['1' => '1 - Low', '2' => '2 - Tired', '3' => '3 - Okay', '4' => '4 - Good', '5' => '5 - High']],
    ['cravings_level', 'Cravings Level (1 low - 5 high)', ['1' => '1 - Low', '2' => '2 - Mild', '3' => '3 - Moderate', '4' => '4 - High', '5' => '5 - Very high']]
];
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
        <h2>Welcome, <?php echo htmlspecialchars($name); ?> - HormoFit Dashboard</h2>

        <div class="dashboard-grid">
            <?php foreach($dashboard_cards as [$title, $value, $is_value]): ?>
                <div class="dashboard-card">
                    <h3><?php echo htmlspecialchars($title); ?></h3>
                    <?php if($is_value): ?><div class="value"><?php echo htmlspecialchars($value); ?></div><?php else: ?><p><?php echo htmlspecialchars($value); ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if(!empty($weights)): ?>
            <h3 class="mt-2 text-center">Weight Trend (Last 5 Entries)</h3>
            <div class="graph-container">
                <?php $max_weight = max($weights) > 0 ? max($weights) : 100; foreach($weights as $w): ?>
                    <div class="bar" style="height: <?php echo ($w / $max_weight) * 100; ?>%;"><span><?php echo $w; ?></span></div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-success mt-2">You haven't tracked any data yet. <a href="track.php">Start tracking now!</a></div>
        <?php endif; ?>

        <div class="recommendation-box mt-2">
            <h3>PCOS Symptom Assessment</h3>
            <p class="text-center assessment-note">These are screening-style questions used to spot patterns. This does not replace a medical diagnosis.</p>
            <?php if($assessment_success): ?><div class="alert alert-success"><?php echo htmlspecialchars($assessment_success); ?></div><?php endif; ?>
            <?php if($assessment_notice): ?><div class="alert alert-error"><?php echo htmlspecialchars($assessment_notice); ?></div><?php endif; ?>

            <form method="POST" class="assessment-form">
                <?php foreach($assessment_selects as [$name, $label, $type, $options, $default]): ?>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars($label); ?></label>
                        <?php if($type === 'number'): ?>
                            <input type="number" name="<?php echo $name; ?>" min="<?php echo $options['min']; ?>" max="<?php echo $options['max']; ?>" value="<?php echo dashboard_value($name, $default); ?>" required>
                        <?php else: ?>
                            <select name="<?php echo $name; ?>" required>
                                <?php foreach($options as $value => $text): ?>
                                    <option value="<?php echo htmlspecialchars($value); ?>" <?php echo dashboard_selected($name, $value, $default); ?>><?php echo htmlspecialchars($text); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="assessment-checks full-row">
                    <?php foreach($assessment_checks as $name => $label): ?>
                        <label class="check-card"><input type="checkbox" name="<?php echo $name; ?>" value="1" <?php echo dashboard_checked($name, $latest_assessment ? $latest_assessment[$name] : 0); ?>> <?php echo htmlspecialchars($label); ?></label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="save_assessment" class="btn full-row">Save Assessment and Generate Recommendations</button>
            </form>
        </div>

        <?php if($latest_assessment): ?>
            <div class="recommendation-box">
                <h3>Latest Assessment Summary</h3>
                <div class="assessment-summary">
                    <?php foreach($summary_chips as $chip): ?><span class="metric-chip"><?php echo htmlspecialchars($chip); ?></span><?php endforeach; ?>
                    <span class="metric-chip risk-<?php echo strtolower($latest_assessment['risk_band']); ?>">Risk Band: <?php echo htmlspecialchars($latest_assessment['risk_band']); ?></span>
                </div>
                <div class="clinical-summary">
                    <p><strong>Assessment:</strong> <?php echo htmlspecialchars($local_assessment_summary['lead']); ?></p>
                    <p><strong>Pattern found:</strong> <?php echo htmlspecialchars($local_assessment_summary['features']); ?></p>
                    <p><strong>Next steps:</strong> <?php echo htmlspecialchars($local_assessment_summary['medical']); ?></p>
                    <p><strong>What to do now:</strong> <?php echo htmlspecialchars($local_assessment_summary['routine']); ?></p>
                </div>
                <div class="ai-recommendation"><?php echo nl2br(htmlspecialchars($latest_assessment['ai_recommendation'])); ?></div>
            </div>
        <?php endif; ?>

        <div class="recommendation-box mt-2">
            <h3>Daily Goals</h3>
            <p class="text-center"><?php echo htmlspecialchars($consistency_message); ?></p>
            <?php if($goal_success): ?><div class="alert alert-success"><?php echo $goal_success; ?></div><?php endif; ?>
            <form method="POST" class="goal-form">
                <div class="form-group">
                    <label>Water Goal (glasses)</label>
                    <input type="number" name="water_goal" min="1" max="30" value="<?php echo htmlspecialchars($goals ? $goals['water_goal'] : 8); ?>" required>
                </div>
                <div class="form-group">
                    <label>Sleep Goal (hours)</label>
                    <input type="number" name="sleep_goal" min="1" max="24" step="0.5" value="<?php echo htmlspecialchars($goals ? $goals['sleep_goal'] : 8); ?>" required>
                </div>
                <div class="form-group">
                    <label>Exercise Goal (minutes)</label>
                    <input type="number" name="exercise_goal" min="0" max="300" value="<?php echo htmlspecialchars($goals ? $goals['exercise_goal'] : 30); ?>" required>
                </div>
                <button type="submit" name="save_goals" class="btn full-row">Save Goals</button>
            </form>
        </div>

        <div class="recommendation-box mt-2">
            <h3>Daily Mental Health Check-In</h3>
            <p class="text-center"><?php echo htmlspecialchars($wellness_message); ?></p>
            <?php if($wellness_success): ?><div class="alert alert-success"><?php echo $wellness_success; ?></div><?php endif; ?>
            <form method="POST" class="wellness-form">
                <?php foreach($wellness_fields as [$name, $label, $options]): ?>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars($label); ?></label>
                        <select name="<?php echo $name; ?>" required>
                            <?php foreach($options as $value => $text): ?><option value="<?php echo $value; ?>" <?php echo $value === '3' ? 'selected' : ''; ?>><?php echo htmlspecialchars($text); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
                <div class="form-group"><label>Sleep Hours</label><input type="number" name="sleep_hours" min="0" max="24" step="0.5" placeholder="e.g. 7.5" required></div>
                <div class="form-group"><label>Water Intake (glasses)</label><input type="number" name="water_glasses" min="0" max="30" placeholder="e.g. 8" required></div>
                <div class="form-group"><label>Exercise or Walk (minutes)</label><input type="number" name="exercise_minutes" min="0" max="300" placeholder="e.g. 30" required></div>
                <div class="form-group full-row"><label>Notes</label><textarea name="notes" rows="3" placeholder="Any pain, mood changes, food cravings, or stress triggers?"></textarea></div>
                <button type="submit" name="save_wellness" class="btn full-row">Save Check-In</button>
            </form>
        </div>

        <?php if($latest_wellness): ?>
            <div class="recommendation-box">
                <h3>Latest Wellness Entry</h3>
                <div class="wellness-summary">
                    <?php foreach($wellness_items as $label => $value): ?><span><?php echo htmlspecialchars($label . ': ' . $value); ?></span><?php endforeach; ?>
                    <?php foreach(['water' => 'Water Goal', 'sleep' => 'Sleep Goal', 'exercise' => 'Exercise Goal'] as $key => $label): ?>
                        <span class="<?php echo $goal_progress[$key] ? 'goal-chip met' : 'goal-chip'; ?>"><?php echo $label . ' ' . ($goal_progress[$key] ? 'Met' : 'Pending'); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php if(!empty($latest_wellness['notes'])): ?><p><strong>Notes:</strong> <?php echo htmlspecialchars($latest_wellness['notes']); ?></p><?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="text-center mt-2"><a href="recommendation.php" class="btn btn-secondary">Open AI Coach</a></div>
    </div>
</body>
</html>
