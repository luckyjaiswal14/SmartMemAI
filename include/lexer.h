#ifndef LEXER_H
#define LEXER_H

typedef enum {
  TOK_EOF,
  TOK_INT,
  TOK_FLOAT,
  TOK_CHAR,
  TOK_IDENTIFIER,
  TOK_ASSIGN,     // =
  TOK_SEMICOLON,  // ;
  TOK_COMMA,      // ,
  TOK_NUMBER,     // 123, 45.6
  TOK_CHAR_CONST, // 'c'
  TOK_LPAREN,     // (
  TOK_RPAREN,     // )
  TOK_LBRACE,     // {
  TOK_RBRACE,     // }
  TOK_STRING,     // "string"
  TOK_PREPROC,    // #include
  TOK_PLUS,       // +
  TOK_MINUS,      // -
  TOK_STAR,       // *
  TOK_SLASH,      // /
  TOK_UNKNOWN
} TokenType;

typedef struct {
  TokenType type;
  char text[128];
  int line;
} Token;

void lexer_init(const char *source);
Token lexer_next_token();

#endif // LEXER_H
