#include <stdio.h>
#include "analyzer.h"
#include "symbol_table.h"

// The Analyzer essentially applies "rules" to the symbol table.
// Rule 1: Declared but never used -> Unused variable.
// Rule 2: Declared more than once -> Duplicate variable.
// Rule 3: Redundant assignment -> assigned but not used (already covered by Rule 1 conceptually for simple types).

void analyzer_run() {
    // In a more complex engine, this could build an AST and traverse it.
    // For this prototype, the parsing phase has done the heavy lifting of flagging 
    // usage and duplicates. The reporter will use these flags.
    // We encapsulate the logic here in case we add more complex inference rules later.
    
    int count = symtab_get_count();
    Symbol *symbols = symtab_get_symbols();
    
    for (int i = 0; i < count; i++) {
        Symbol *sym = &symbols[i];
        // Ensure that a variable declared but not marked used is treated as unused
        if (!sym->is_used) {
            // It's unused. Our flag is already false, so we just acknowledge it.
        }
    }
}
