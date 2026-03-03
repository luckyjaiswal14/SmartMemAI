#include <stdio.h>
#include <stdlib.h>
#include "parser.h"
#include "analyzer.h"
#include "reporter.h"
#include "symbol_table.h"

char* read_file(const char *filename) {
    FILE *f = fopen(filename, "r");
    if (!f) {
        perror("Failed to open file");
        return NULL;
    }
    
    fseek(f, 0, SEEK_END);
    long length = ftell(f);
    fseek(f, 0, SEEK_SET);
    
    char *buffer = malloc(length + 1);
    if (buffer) {
        size_t read_bytes = fread(buffer, 1, length, f);
        buffer[read_bytes] = '\0';
    }
    fclose(f);
    return buffer;
}

int main(int argc, char **argv) {
    if (argc < 2) {
        printf("Usage: %s <source_file.c>\n", argv[0]);
        return 1;
    }
    
    char *source = read_file(argv[1]);
    if (!source) return 1;
    
    symtab_init();
    
    // Parse the code to populate symbol table and track usage
    parser_parse(source);
    
    // Run the analyzer logic
    analyzer_run();
    
    // Generate the report
    reporter_generate_report();
    
    free(source);
    return 0;
}
