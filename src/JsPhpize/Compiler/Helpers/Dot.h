function ($base) {
    $getCallable = function ($base, $key) {
        if (is_callable(array($base, $key))) {
            return array($base, $key);
        }
    };
    foreach (array_slice(func_get_args(), 1) as $key) {
        $base = is_array($base)
            ? (isset($base[$key])
                ? $base[$key]
                : null
             )
            : (is_object($base)
                ? (isset($base->$key)
                    ? $base->$key
                    : (method_exists($base, $method = "get" . ucfirst($key))
                        ? $base->$method()
                        : (method_exists($base, $key)
                            ? array($base, $key)
                            : $getCallable($base, $key)
                        )
                    )
                )
                : $getCallable($base, $key)
            );
    }

    return $base;
}
