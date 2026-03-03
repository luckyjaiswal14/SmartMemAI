#include "memory_calc.h"
#include "symbol_table.h"

MemoryStats calculate_memory() {
    MemoryStats stats = {0, 0, 0};
    int count = symtab_get_count();
    Symbol *symbols = symtab_get_symbols();
    
    for (int i = 0; i < count; i++) {
        Symbol *sym = &symbols[i];
        
        // Before optimization: count everything
        stats.total_memory_before += sym->size_bytes;
        
        // After optimization: only keep used, non-duplicate variables
        if (sym->is_used && !sym->is_duplicate) {
            stats.total_memory_after += sym->size_bytes;
        }
    }
    
    stats.memory_saved = stats.total_memory_before - stats.total_memory_after;
    
    return stats;
}
