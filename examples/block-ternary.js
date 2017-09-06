var b;
var foo = function () {
    b = 2;

    return []
        ? 9
        : b;
};
var a = (foo)
    ('foo');

return a;
