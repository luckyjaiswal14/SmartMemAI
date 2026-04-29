<?php
function dashboard_value($key, $default = ''){
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : htmlspecialchars($default);
}

function dashboard_selected($key, $value, $default = ''){
    $current = isset($_POST[$key]) ? $_POST[$key] : $default;
    return (string)$current === (string)$value ? 'selected' : '';
}

function dashboard_checked($key, $default = 0){
    $current = isset($_POST[$key]) ? intval($_POST[$key]) : intval($default);
    return $current === 1 ? 'checked' : '';
}

function fetch_one($conn, $query){
    return mysqli_fetch_assoc(mysqli_query($conn, $query));
}

function build_pcos_risk($answers){
    $score = 0;
    $score += ['regular' => 0, 'sometimes_irregular' => 2, 'irregular' => 4, 'absent' => 5][$answers['cycle_pattern']] ?? 0;
    $score += ['0' => 0, '1_2' => 1, '3_plus' => 3][$answers['skipped_periods']] ?? 0;
    $score += ['active' => 0, 'moderate' => 1, 'low' => 2][$answers['activity_level']] ?? 0;
    $score += ['low' => 0, 'moderate' => 1, 'high' => 2][$answers['stress_load']] ?? 0;

    foreach(['facial_hair', 'persistent_acne', 'scalp_hair_thinning', 'unexplained_weight_gain', 'dark_skin_patches'] as $key){
        $score += intval($answers[$key]) ? 2 : 0;
    }
    foreach(['high_sugar_cravings', 'family_history', 'trying_to_conceive', 'pelvic_pain'] as $key){
        $score += intval($answers[$key]) ? 1 : 0;
    }

    return [$score, $score >= 12 ? "High" : ($score >= 7 ? "Moderate" : "Low")];
}

function fallback_recommendation($answers, $risk_band, $latest_data, $latest_wellness){
    $lines = [[
        "High" => "Assessment: Your answers are strongly consistent with a PCOS symptom pattern. This does not confirm a diagnosis, but the pattern is strong enough to justify medical follow-up.",
        "Moderate" => "Assessment: Your answers are moderately consistent with a PCOS symptom pattern. This is enough to justify closer tracking and a medical review if symptoms persist.",
        "Low" => "Assessment: Your current answers show a lower-risk symptom pattern. Continue tracking because PCOS screening depends on symptom trends over time."
    ][$risk_band]];

    $lines[] = "Next steps: Book a gynecologist or endocrinology review if your cycles stay irregular, you keep missing periods, or acne, hair growth, and weight changes continue.";
    $lines[] = "Ask about: hormone blood tests, blood sugar or HbA1c, cholesterol, blood pressure, and an ultrasound if your clinician thinks it fits.";

    if($latest_data){
        if(intval($latest_data['cycle_length']) < 21 || intval($latest_data['cycle_length']) > 35){
            $lines[] = "Track: Your logged cycle length is outside the usual range, so record each period start date and cycle length for the next 3 months.";
        }
        if(floatval($latest_data['height']) > 0){
            $height_m = floatval($latest_data['height']) / 100;
            if(floatval($latest_data['weight']) / ($height_m * $height_m) >= 25){
                $lines[] = "Daily plan: Build meals around protein, fiber, and lower-refined-carb foods, and aim for regular walking or strength work most weeks.";
            }
        }
    }

    if($latest_wellness && intval($latest_wellness['stress_level']) >= 4){
        $lines[] = "Stress plan: Your recent stress score is high, so prioritize sleep timing and choose steady exercise over overly intense routines.";
    }
    if(intval($answers['dark_skin_patches']) === 1 || intval($answers['high_sugar_cravings']) === 1){
        $lines[] = "Metabolic follow-up: Because you reported dark skin patches or strong sugar cravings, ask specifically about insulin resistance and glucose screening.";
    }

    return implode(" ", $lines);
}

function build_local_assessment_summary($assessment, $latest_data, $latest_wellness){
    $symptoms = $assessment['cycle_pattern'] !== 'regular' ? ['irregular cycles'] : [];
    foreach([
        'persistent_acne' => 'persistent acne',
        'facial_hair' => 'excess hair growth',
        'scalp_hair_thinning' => 'scalp hair thinning',
        'unexplained_weight_gain' => 'weight gain',
        'dark_skin_patches' => 'dark skin patches'
    ] as $key => $label){
        if($assessment[$key]){
            $symptoms[] = $label;
        }
    }

    $routine = "What to do now: track cycle dates, keep exercise regular, center meals on protein and fiber, reduce refined sugar, and protect sleep.";
    if($latest_data && (intval($latest_data['cycle_length']) < 21 || intval($latest_data['cycle_length']) > 35)){
        $routine .= " Your logged cycle length is already outside the usual range, so keep a month-by-month cycle record.";
    }
    if($latest_wellness && intval($latest_wellness['stress_level']) >= 4){
        $routine .= " Your stress score is elevated, so keep the plan simple and consistent rather than intense.";
    }

    return [
        'lead' => [
            "High" => "Your answers are strongly consistent with a higher-likelihood PCOS pattern.",
            "Moderate" => "Your answers are moderately consistent with a possible PCOS pattern.",
            "Low" => "Your answers are less consistent with PCOS right now, but the symptoms still need tracking if they continue."
        ][$assessment['risk_band']],
        'features' => !empty($symptoms) ? "Key pattern seen here: " . implode(", ", array_slice($symptoms, 0, 4)) . "." : "You did not report many classic PCOS symptoms in this screening.",
        'medical' => "What you need to do next: book a gynecologist or endocrinologist visit if symptoms continue, and discuss hormone tests, blood sugar or HbA1c, cholesterol, blood pressure, and whether an ultrasound is needed.",
        'routine' => $routine
    ];
}

function generate_gemini_recommendation($api_key, $models, $api_versions, $answers, $risk_score, $risk_band, $latest_data, $latest_wellness){
    if(empty($api_key)){
        return [false, "AI recommendations are not configured yet."];
    }
    if(!function_exists('curl_init')){
        return [false, "The cURL extension is not enabled on this PHP setup."];
    }

    $prompt = "You are writing for a production health app. Based on the structured assessment below, write a direct PCOS screening recommendation. Do not diagnose with certainty, but be decisive. Start with a sentence like 'Your answers are strongly consistent with a PCOS symptom pattern' or 'Your answers are moderately consistent with a PCOS symptom pattern' depending on risk. Then explain what the user needs to do next. Use plain language. Do not use emotional lead-ins. Mention the specific findings that matter, then include: 1) the likely pattern, 2) what medical review or tests to ask for, 3) what to do over the next 4 to 8 weeks. Return 3 short sections with labels: Assessment, Next steps, Daily plan. Keep it between 170 and 230 words.\n\n" .
        "Assessment answers: " . json_encode($answers) . "\n" .
        "Risk score: " . $risk_score . "\n" .
        "Risk band: " . $risk_band . "\n" .
        "Recent health data: " . ($latest_data ? json_encode(['weight' => $latest_data['weight'], 'height' => $latest_data['height'], 'cycle_length' => $latest_data['cycle_length'], 'symptoms' => $latest_data['symptoms']]) : "No recent health data") . "\n" .
        "Recent wellness data: " . ($latest_wellness ? json_encode(['sleep_hours' => $latest_wellness['sleep_hours'], 'stress_level' => $latest_wellness['stress_level'], 'energy_level' => $latest_wellness['energy_level'], 'exercise_minutes' => $latest_wellness['exercise_minutes'], 'water_glasses' => $latest_wellness['water_glasses']]) : "No recent wellness data");

    $payload = json_encode([
        'contents' => [[ 'parts' => [[ 'text' => $prompt ]] ]],
        'generationConfig' => ['temperature' => 0.5, 'maxOutputTokens' => 320]
    ]);

    $last_error = "Gemini returned an unexpected response.";
    foreach($api_versions as $api_version){
        foreach($models as $model){
            $ch = curl_init("https://generativelanguage.googleapis.com/" . $api_version . "/models/" . rawurlencode($model) . ":generateContent");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "x-goog-api-key: " . $api_key]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_TIMEOUT, 25);
            $response = curl_exec($ch);
            $curl_error = curl_error($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if($response === false){
                $last_error = "Gemini request failed: " . $curl_error;
                continue;
            }

            $decoded = json_decode($response, true);
            if($status_code < 400 && isset($decoded['candidates'][0]['content']['parts'][0]['text'])){
                return [true, trim($decoded['candidates'][0]['content']['parts'][0]['text'])];
            }
            $last_error = isset($decoded['error']['message']) ? $decoded['error']['message'] : "Gemini returned HTTP " . $status_code . " for model " . $model . " on " . $api_version . ".";
        }
    }

    return [false, $last_error];
}
