# SmartMemAI - Rule-Based Memory Optimizer for C Code

## Overview

SmartMemAI is a lightweight static analysis project for C programs. It uses Flex-based lexical analysis and rule-based checks to detect memory-related issues such as unused variables, duplicate declarations, and use before initialization. It then estimates memory impact and adds an AI-assisted severity summary.

The project also includes a simple Flask web interface where a user can:

- analyze code
- optimize simple unused declarations
- restore original code after optimization
- download the report
- save the report as PDF through the browser

## Core Features

- Symbol table generation with variable name, type, usage, initialization, and line number
- Rule-based findings:
  - `R1` Unused Variable
  - `R2` Duplicate Declaration
  - `R3` Use Before Initialization
- Memory analysis:
  - memory before optimization
  - memory after optimization
  - estimated removable memory
- AI severity assessment:
  - risk level
  - optimization priority
  - estimated quality score
  - code category
  - priority order
- Assisted optimization for simple unused variable declarations
- Restore original code after optimization

## Tech Stack

- `Flex` for lexical analysis
- `C` for the analyzer logic
- `Python` with `Flask` for the web backend
- `Python` with `scikit-learn` for AI severity scoring
- `HTML/CSS` for the interface

## Project Files

- `smartmemai.l` - main Flex rule-based analyzer
- `ai_model.py` - AI severity assessment module
- `app.py` - Flask backend
- `index.html` - web interface
- `run.sh` - build and run script
- `.gitignore` - ignores generated files

Generated runtime files:

- `input.c`
- `report.txt`
- `data.txt`
- `names.txt`
- `lex.yy.c`
- `smartmemai`

## Working Flow

1. The user enters C code in the web interface.
2. `app.py` stores the code in `input.c`.
3. `run.sh` runs Flex and compiles the analyzer.
4. `smartmemai.l` scans the code and generates the rule-based report.
5. `ai_model.py` appends the AI severity assessment.
6. The final report is shown in the browser.

## How to Run

### Prerequisites

Ensure `flex` and `gcc` are installed on your system:

**macOS:**

```bash
brew install flex
# gcc is included with Xcode Command Line Tools
xcode-select --install
```

**Linux (Ubuntu/Debian):**

```bash
sudo apt-get install flex gcc
```

**Linux (Fedora/RHEL):**

```bash
sudo yum install flex gcc
```

### Install Python dependencies

```bash
pip3 install flask numpy scikit-learn
```

### Run the web app

Start the Flask development server:

```bash
python3 -m flask --app app run --host 0.0.0.0 --port 5001
```

Then open in your browser:

```text
http://localhost:5001
```

**Features in the web interface:**

- Paste or edit C code for analysis
- Click "Analyze Code" to run the static analyzer
- View detailed findings with line numbers
- See AI-powered severity assessment and memory impact
- Optimize code by removing unused declarations
- Download analysis report as text file
- Clear and restart analysis

### Run the analyzer from command line

First, create a file named `input.c` with your C code:

```bash
./run.sh
cat report.txt
```

The `run.sh` script will:

1. Run Flex on `smartmemai.l` to generate C lexer code
2. Compile the lexer with gcc
3. Execute the analyzer on `input.c`
4. Generate `report.txt` with findings

## Sample Input

```c
int main() {
    int a;
    int b = 10;
    int c = a + b;
    int d = a + b + c;
    return a;
}
```

## Current Limitations

- It is lexer-based, not a full parser-based compiler
- Automatic optimization only removes simple unused declarations safely
- Complex declarations, arrays, and pointers are not auto-optimized
- The AI module is a severity scorer, not a replacement for the rule engine

## Future Scope

- Undeclared variable detection
- Array usage analysis
- Pointer and dynamic memory analysis
- Parser integration using Yacc/Bison
- More advanced optimization suggestions

## Author

Gaurav Singh

## Project Status

✅ **Currently Active & Functional**

- Web interface is fully operational
- Static analyzer working with Flex-based lexical analysis
- AI severity assessment module integrated
- Memory optimization suggestions implemented

## Contributing

Contributions are welcome! Feel free to fork this repository and submit pull requests for any improvements or bug fixes.
