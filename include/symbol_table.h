#ifndef SYMBOL_TABLE_H
#define SYMBOL_TABLE_H

#include <stdbool.h>

#define MAX_VAR_NAME_LEN 64
#define MAX_SYMBOLS 1024

typedef enum { TYPE_INT, TYPE_FLOAT, TYPE_CHAR, TYPE_UNKNOWN } DataType;

typedef struct {
  char name[MAX_VAR_NAME_LEN];
  DataType type;
  int size_bytes;
  bool is_used;
  bool is_duplicate;
  bool is_redundant_assignment;
  int declaration_line;
  int scope_level;
} Symbol;

void symtab_init();
bool symtab_add_variable(const char *name, DataType type, int line);
Symbol *symtab_lookup(const char *name);
void symtab_mark_used(const char *name);
Symbol *symtab_lookup(const char *name);
void symtab_mark_used(const char *name);
void symtab_mark_redundant_assignment(const char *name);
void symtab_enter_scope();
void symtab_exit_scope();
int symtab_get_count();
Symbol *symtab_get_symbols();

int get_type_size(DataType type);

#endif // SYMBOL_TABLE_H
