int global_var = 100;
float pi = 3.14; // unused

int main() {
  int x1 = 10;
  int x2; // unused

  x1 = 20; // duplicate

  char c = 'a';

  x1 = global_var + 5;
  c = 'b';
}
