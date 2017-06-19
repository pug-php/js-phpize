$counter = function () use (&$count) {
  return array( $count++, $count++, $count++ );
};
