#include <stdio.h>
#include "reporter.h"
#include "symbol_table.h"
#include "memory_calc.h"

void reporter_generate_report() {
    printf("==========================================\n");
    printf("       SmartMemAI Optimization Report     \n");
    printf("==========================================\n\n");
    
    int count = symtab_get_count();
    Symbol *symbols = symtab_get_symbols();
    
    printf("Detected Issues:\n");
    printf("----------------\n");
    
    int issues_found = 0;
    for (int i = 0; i < count; i++) {
        Symbol *sym = &symbols[i];
        if (sym->is_duplicate) {
            printf("[Warning] Duplicate variable '%s' declared at line %d.\n", sym->name, sym->declaration_line);
            issues_found++;
        }
        else if (!sym->is_used) {
            printf("[Warning] Variable '%s' declared at line %d but never used.\n", sym->name, sym->declaration_line);
            issues_found++;
        }
    }
    
    if (issues_found == 0) {
        printf("No memory optimization issues detected!\n");
    }
    
    printf("\nOptimization Suggestions:\n");
    printf("-------------------------\n");
    for (int i = 0; i < count; i++) {
        Symbol *sym = &symbols[i];
        if (sym->is_duplicate) {
            printf("- Remove duplicate declaration of '%s'.\n", sym->name);
        }
        else if (!sym->is_used) {
            printf("- Remove unused variable '%s'.\n", sym->name);
        }
    }
    
    printf("\nMemory Estimation:\n");
    printf("------------------\n");
    MemoryStats stats = calculate_memory();
    printf("Memory before optimization: %d bytes\n", stats.total_memory_before);
    printf("Memory after optimization:  %d bytes\n", stats.total_memory_after);
    printf("Total memory saved:         %d bytes\n", stats.memory_saved);
    printf("==========================================\n");
}
