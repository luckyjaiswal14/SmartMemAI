#include "symbol_table.h"
#include <stdio.h>
#include <string.h>

static Symbol symbol_table[MAX_SYMBOLS];
static int symbol_count = 0;
static int current_scope_level = 0;

void symtab_init() {
  symbol_count = 0;
  current_scope_level = 0;
}

void symtab_enter_scope() { current_scope_level++; }

void symtab_exit_scope() {
  if (current_scope_level > 0) {
    current_scope_level--;
  }
}

int get_type_size(DataType type) {
  switch (type) {
  case TYPE_INT:
    return 4;
  case TYPE_FLOAT:
    return 4;
  case TYPE_CHAR:
    return 1;
  default:
    return 0;
  }
}

bool symtab_add_variable(const char *name, DataType type, int line) {
  if (symbol_count >= MAX_SYMBOLS)
    return false;

  bool is_duplicate = false;
  for (int i = 0; i < symbol_count; i++) {
    // Only count as duplicate if it's in the SAME scope level
    if (strcmp(symbol_table[i].name, name) == 0 &&
        symbol_table[i].scope_level == current_scope_level) {
      is_duplicate = true;
      break;
    }
  }

  Symbol *sym = &symbol_table[symbol_count++];
  strncpy(sym->name, name, MAX_VAR_NAME_LEN - 1);
  sym->name[MAX_VAR_NAME_LEN - 1] = '\0';
  sym->type = type;
  sym->size_bytes = get_type_size(type);
  sym->is_used = false;
  sym->is_duplicate = is_duplicate;
  sym->is_redundant_assignment = false;
  sym->declaration_line = line;
  sym->scope_level = current_scope_level;

  return true;
}

Symbol *symtab_lookup(const char *name) {
  // Search backwards to find the innermost scope variable first
  for (int i = symbol_count - 1; i >= 0; i--) {
    if (strcmp(symbol_table[i].name, name) == 0 &&
        symbol_table[i].scope_level <= current_scope_level) {
      return &symbol_table[i];
    }
  }
  return NULL;
}

void symtab_mark_used(const char *name) {
  Symbol *sym = symtab_lookup(name);
  if (sym) {
    sym->is_used = true;
  }
}

void symtab_mark_redundant_assignment(const char *name) {
  Symbol *sym = symtab_lookup(name);
  if (sym) {
    sym->is_redundant_assignment = true;
  }
}

int symtab_get_count() { return symbol_count; }

Symbol *symtab_get_symbols() { return symbol_table; }
