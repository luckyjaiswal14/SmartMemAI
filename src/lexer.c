#include "lexer.h"
#include <ctype.h>
#include <stdio.h>
#include <string.h>

static const char *src;
static int current_line;
static int pos;

void lexer_init(const char *source) {
  src = source;
  current_line = 1;
  pos = 0;
}

static void skip_whitespace() {
  while (src[pos] != '\0') {
    if (src[pos] == ' ' || src[pos] == '\t' || src[pos] == '\r') {
      pos++;
    } else if (src[pos] == '\n') {
      current_line++;
      pos++;
    } else if (src[pos] == '/' && src[pos + 1] == '/') {
      // single line comment
      while (src[pos] != '\0' && src[pos] != '\n')
        pos++;
    } else if (src[pos] == '/' && src[pos + 1] == '*') {
      // multi line comment
      pos += 2;
      while (src[pos] != '\0' && !(src[pos] == '*' && src[pos + 1] == '/')) {
        if (src[pos] == '\n')
          current_line++;
        pos++;
      }
      if (src[pos] != '\0')
        pos += 2; // skip */
    } else {
      break;
    }
  }
}

Token lexer_next_token() {
  skip_whitespace();
  Token tok;
  tok.line = current_line;
  tok.text[0] = '\0';

  if (src[pos] == '\0') {
    tok.type = TOK_EOF;
    strcpy(tok.text, "EOF");
    return tok;
  }

  char c = src[pos];

  // Preprocessor directives
  if (c == '#') {
    int start = pos;
    while (src[pos] != '\0' && src[pos] != '\n') {
      pos++;
    }
    int len = pos - start;
    if (len > 127)
      len = 127;
    strncpy(tok.text, &src[start], len);
    tok.text[len] = '\0';
    tok.type = TOK_PREPROC;
    return tok;
  }

  // Identifiers and Keywords
  if (isalpha(c) || c == '_') {
    int start = pos;
    while (isalnum(src[pos]) || src[pos] == '_') {
      pos++;
    }
    int len = pos - start;
    if (len > 127)
      len = 127;
    strncpy(tok.text, &src[start], len);
    tok.text[len] = '\0';

    if (strcmp(tok.text, "int") == 0)
      tok.type = TOK_INT;
    else if (strcmp(tok.text, "float") == 0)
      tok.type = TOK_FLOAT;
    else if (strcmp(tok.text, "char") == 0)
      tok.type = TOK_CHAR;
    else
      tok.type = TOK_IDENTIFIER;

    return tok;
  }

  // Numbers
  if (isdigit(c)) {
    int start = pos;
    while (isdigit(src[pos]) || src[pos] == '.') {
      pos++;
    }
    int len = pos - start;
    if (len > 127)
      len = 127;
    strncpy(tok.text, &src[start], len);
    tok.text[len] = '\0';
    tok.type = TOK_NUMBER;
    return tok;
  }

  // Character constants
  if (c == '\'') {
    int start = pos;
    pos++; // skip '
    if (src[pos] != '\0')
      pos++; // character
    if (src[pos] == '\'')
      pos++; // skip '

    int len = pos - start;
    if (len > 127)
      len = 127;
    strncpy(tok.text, &src[start], len);
    tok.text[len] = '\0';
    tok.type = TOK_CHAR_CONST;
    return tok;
  }

  // String literals
  if (c == '"') {
    int start = pos;
    pos++; // skip "
    while (src[pos] != '\0' && src[pos] != '"') {
      pos++;
    }
    if (src[pos] == '"')
      pos++; // skip "

    int len = pos - start;
    if (len > 127)
      len = 127;
    strncpy(tok.text, &src[start], len);
    tok.text[len] = '\0';
    tok.type = TOK_STRING;
    return tok;
  }

  // Operators and Punctuation
  pos++;
  tok.text[0] = c;
  tok.text[1] = '\0';

  switch (c) {
  case '=':
    tok.type = TOK_ASSIGN;
    break;
  case ';':
    tok.type = TOK_SEMICOLON;
    break;
  case ',':
    tok.type = TOK_COMMA;
    break;
  case '(':
    tok.type = TOK_LPAREN;
    break;
  case ')':
    tok.type = TOK_RPAREN;
    break;
  case '{':
    tok.type = TOK_LBRACE;
    break;
  case '}':
    tok.type = TOK_RBRACE;
    break;
  case '+':
    tok.type = TOK_PLUS;
    break;
  case '-':
    tok.type = TOK_MINUS;
    break;
  case '*':
    tok.type = TOK_STAR;
    break;
  case '/':
    tok.type = TOK_SLASH;
    break;
  default:
    tok.type = TOK_UNKNOWN;
    break;
  }

  return tok;
}
