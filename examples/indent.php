for ($i = 9; $i; $i--) {
  if (call_user_func((function ($tiny) {
    return $tiny < 3;
  }), $i)) {
    continue;
  }
  break;
}
