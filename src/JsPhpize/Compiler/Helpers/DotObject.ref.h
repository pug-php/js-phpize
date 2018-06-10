function (&$base) {
    $getFromArray = function (&$base, $key) {
        return isset($base[$key])
            ? $base[$key]
            : null;
    };
    $getCallable = function (&$base, $key) use ($getFromArray) {
        if (is_callable(array($base, $key))) {
            return array($base, $key);
        }
        if ($base instanceof \ArrayAccess) {
            return $getFromArray($base, $key);
        }
    };
    $getRegExp = function ($value) {
        return isset($value->isRegularExpression) && $value->isRegularExpression ? $value->regExp : null;
    };
    $fallbackDot = function (&$base, $key) use ($getCallable, $getRegExp) {
        if (is_string($base)) {
            if (preg_match('/^[-+]?\d+$/', strval($key))) {
                return substr($base, intval($key), 1);
            }
            if ($key === 'length') {
                return strlen($base);
            }
            if ($key === 'substr' || $key === 'slice') {
                return function ($start, $length = null) use ($base) {
                    return func_num_args() === 1 ? substr($base, $start) : substr($base, $start, $length);
                };
            }
            if ($key === 'charAt') {
                return function ($pos) use ($base) {
                    return substr($base, $pos, 1);
                };
            }
            if ($key === 'indexOf') {
                return function ($needle) use ($base) {
                    $pos = strpos($base, $needle);

                    return $pos === false ? -1 : $pos;
                };
            }
            if ($key === 'toUpperCase') {
                return function () use ($base) {
                    return strtoupper($base);
                };
            }
            if ($key === 'toLowerCase') {
                return function () use ($base) {
                    return strtolower($base);
                };
            }
            if ($key === 'match') {
                return function ($search) use ($base, $getRegExp) {
                    if (!$getRegExp($search)) {
                        $search = '/' . preg_quote($search) . '/';
                    }

                    return preg_match($search, $base);
                };
            }
            if ($key === 'split') {
                return function ($delimiter) use ($base, $getRegExp) {
                    if ($regExp = $getRegExp($delimiter)) {
                        return preg_split($regExp, $base);
                    }

                    return explode($delimiter, $base);
                };
            }
            if ($key === 'replace') {
                return function ($from, $to) use ($base, $getRegExp) {
                    if ($regExp = $getRegExp($from)) {
                        return preg_replace($regExp, $to, $base);
                    }

                    return str_replace($from, $to, $base);
                };
            }
        }

        return $getCallable($base, $key);
    };
    $crawler = &$base;
    $result = $base;
    foreach (array_slice(func_get_args(), 1) as $key) {
        $result = is_array($crawler)
            ? $getFromArray($crawler, $key)
            : (is_object($crawler)
                ? (method_exists($crawler, $method = "get" . ucfirst($key))
                    ? $crawler->$method()
                    : (method_exists($crawler, $key)
                        ? array($crawler, $key)
                        : $crawler->$key
                    )
                )
                : $fallbackDot($crawler, $key)
            );
        $crawler = &$result;
    }

    return $result;
}
