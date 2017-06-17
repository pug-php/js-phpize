function ($base) {
    foreach (array_slice(func_get_args(), 1) as $value) {
        $base = $base - $value;
    }

    return $base;
}
