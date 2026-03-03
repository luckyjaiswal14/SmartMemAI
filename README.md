# SmartMemAI

A C-based static analysis prototype for optimizing memory by detecting unused and duplicate variables.

SmartMemAI optimizes your C codebase by analyzing variable usage patterns locally across blocks. Featuring a hand-built lexer, parser, and block-scoped symbol table, it scans your C files tracking variables within `scope levels { ... }` alongside assignments, finding variables that were declared but never used in execution to calculate memory saving. 

## Features

- **Lexer/Parser**: Accurately traverses C codes including nested function scopes `{ }`, ignored `<strings>`, and ignored preprocessor directives `#include...`. 
- **Block-Aware Symbol Table**: Tracks individual definitions and shadows intelligently avoiding variable definition false-positives!
- **Memory Math**: Predicts potential saved bytes locally by identifying completely ignored assignments across code bases.

## Build and Run

1. Make sure you have `gcc` and `make` installed.
2. Build the project using `make clean all` inside the root of this folder.
3. Pass a source C file to the created runtime `.bin/smartmemai <path>`

```bash
make clean all
./bin/smartmemai tests/sample4.c
```
