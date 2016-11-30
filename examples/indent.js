for (i = 9; i; i--) {
  if ((function (tiny) {
    return tiny < 3;
  })(i)) {
    continue;
  }
  break;
}
