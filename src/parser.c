#include "parser.h"
#include "lexer.h"
#include "symbol_table.h"
#include <stdio.h>
#include <string.h>

static void parse_declaration(Token *t);

static void parse_expression(Token *t);

void parser_parse(const char *source) {
  lexer_init(source);

  Token t = lexer_next_token();
  while (t.type != TOK_EOF) {
    if (t.type == TOK_INT || t.type == TOK_FLOAT || t.type == TOK_CHAR) {
      parse_declaration(&t);
    } else if (t.type == TOK_IDENTIFIER) {
      // Could be assignment or function call or just an expression.
      // Let's peek the next token to see if it's '='. Since we don't have peek,
      // we will hold this token and check the next.
      Token next = lexer_next_token();
      if (next.type == TOK_ASSIGN) {
        symtab_mark_used(t.text);
        parse_expression(&next);
        t = next;
      } else {
        symtab_mark_used(t.text);
        symtab_mark_used(t.text);
        t = next;
        continue; // don't grab another token at the bottom of the loop
      }
    } else if (t.type == TOK_LBRACE) {
      symtab_enter_scope();
    } else if (t.type == TOK_RBRACE) {
      symtab_exit_scope();
    } else if (t.type == TOK_STRING || t.type == TOK_PREPROC) {
      // safely ignored
    } else {
      // For any other token, if it's not handled, we just skip it or
      // process it if it contains identifiers.
    }

    t = lexer_next_token();
  }
}

static void parse_expression(Token *t) {
  // Read until semicolon
  *t = lexer_next_token();
  while (t->type != TOK_EOF && t->type != TOK_SEMICOLON) {
    if (t->type == TOK_IDENTIFIER) {
      symtab_mark_used(t->text);
    }
    *t = lexer_next_token();
  }
}

static void parse_declaration(Token *t) {
  DataType current_type = TYPE_UNKNOWN;
  if (t->type == TOK_INT)
    current_type = TYPE_INT;
  else if (t->type == TOK_FLOAT)
    current_type = TYPE_FLOAT;
  else if (t->type == TOK_CHAR)
    current_type = TYPE_CHAR;

  while (t->type != TOK_EOF && t->type != TOK_SEMICOLON) {
    *t = lexer_next_token();

    if (t->type == TOK_IDENTIFIER) {
      symtab_add_variable(t->text, current_type, t->line);

      // Look for optional initialization
      Token next = lexer_next_token();

      // If next is a parenthesis, it's a function declaration, not a variable.
      if (next.type == TOK_LPAREN) {
        // Skip till RPAREN, or just let the main parsing loop advance past it
        // We will not add this identifier as a true variable usage.
        // Actually, we already added it. We should remove it or not add it
        // first. But symtab doesn't have a remove. For now, we can just skip
        // marking it as unused by implicitly marking it used so it doesn't get
        // flagged.
        symtab_mark_used(t->text);

        // Skip to end of arguments
        while (next.type != TOK_EOF && next.type != TOK_RPAREN) {
          next = lexer_next_token();
        }
        *t = next;
        continue;
      }

      if (next.type == TOK_ASSIGN) {
        // Initialization
        *t = lexer_next_token();
        while (t->type != TOK_EOF && t->type != TOK_COMMA &&
               t->type != TOK_SEMICOLON) {
          if (t->type == TOK_IDENTIFIER) {
            symtab_mark_used(t->text);
          }
          *t = lexer_next_token();
        }
        if (t->type == TOK_SEMICOLON)
          break;
      } else {
        *t = next;
        if (t->type == TOK_SEMICOLON)
          break;
      }
    }
  }
}
