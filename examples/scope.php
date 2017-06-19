$count = 1;
$counter = function () use (&$count) {
  return $count;
};
