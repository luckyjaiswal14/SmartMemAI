#!/usr/bin/env python3
import json
import sys
from pathlib import Path

import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score


DATASET_PATH = Path(__file__).resolve().parents[1] / "data" / "PCOS_data.csv"
TARGET_COLUMN = "PCOS (Y/N)"
FEATURE_COLUMNS = [
    " Age (yrs)",
    "Weight (Kg)",
    "Height(Cm) ",
    "BMI",
    "Cycle length(days)",
    "Weight gain(Y/N)",
    "hair growth(Y/N)",
    "Skin darkening (Y/N)",
    "Hair loss(Y/N)",
    "Pimples(Y/N)",
    "Fast food (Y/N)",
    "Reg.Exercise(Y/N)",
]


def clean_numeric(series):
    return pd.to_numeric(series.astype(str).str.strip(), errors="coerce")


def main():
    if len(sys.argv) != 12:
        raise ValueError("Expected age, weight, height, cycle length, and seven yes/no values.")

    age = float(sys.argv[1])
    weight = float(sys.argv[2])
    height = float(sys.argv[3])
    cycle_length = float(sys.argv[4])
    bmi = weight / ((height / 100) ** 2)
    input_values = [
        age,
        weight,
        height,
        bmi,
        cycle_length,
        float(sys.argv[5]),
        float(sys.argv[6]),
        float(sys.argv[7]),
        float(sys.argv[8]),
        float(sys.argv[9]),
        float(sys.argv[10]),
        float(sys.argv[11]),
    ]

    df = pd.read_csv(DATASET_PATH)
    for column in FEATURE_COLUMNS:
        df[column] = clean_numeric(df[column])
    df[TARGET_COLUMN] = clean_numeric(df[TARGET_COLUMN])
    df = df.dropna(subset=FEATURE_COLUMNS + [TARGET_COLUMN])

    x = df[FEATURE_COLUMNS]
    y = df[TARGET_COLUMN].astype(int)

    x_train, x_test, y_train, y_test = train_test_split(
        x,
        y,
        test_size=0.2,
        random_state=42,
        stratify=y,
    )

    model = RandomForestClassifier(
        n_estimators=150,
        random_state=42,
        class_weight="balanced",
    )
    model.fit(x_train, y_train)

    input_df = pd.DataFrame([input_values], columns=FEATURE_COLUMNS)
    prediction = int(model.predict(input_df)[0])
    probability = float(model.predict_proba(input_df)[0][1])
    accuracy = float(accuracy_score(y_test, model.predict(x_test)))

    print(json.dumps({
        "prediction": prediction,
        "probability": round(probability * 100, 2),
        "accuracy": round(accuracy * 100, 2),
        "features": {
            "age": age,
            "weight": weight,
            "height": height,
            "bmi": round(bmi, 2),
            "cycle_length": cycle_length,
            "weight_gain": input_values[5],
            "hair_growth": input_values[6],
            "skin_darkening": input_values[7],
            "hair_loss": input_values[8],
            "pimples": input_values[9],
            "fast_food": input_values[10],
            "regular_exercise": input_values[11],
        },
    }))


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(json.dumps({"error": str(exc)}))
        sys.exit(1)
