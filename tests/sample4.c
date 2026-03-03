#include <stdio.h>
#include <string.h>

int global_unused = 0;

int main() {
  int i = 10;
  char *test_str = "This is a test string";

  // Test shadow block scope
  if (i == 10) {
    int i = 20; // newly scoped i, should not be marked duplicate
    i = i + 5;
  }

  // usage of inner scope `i` does not use outer scope `i`
  // but we use outer scope `i` here:
  printf("%d %s\n", i, test_str);

  return 0;
}
