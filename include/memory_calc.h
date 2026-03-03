#ifndef MEMORY_CALC_H
#define MEMORY_CALC_H

typedef struct {
    int total_memory_before;
    int total_memory_after;
    int memory_saved;
} MemoryStats;

MemoryStats calculate_memory();

#endif // MEMORY_CALC_H
