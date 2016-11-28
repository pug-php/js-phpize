a = {
    b: function () {
        return 42;
    }
};
foo = 5 + 2 * (true ? 2 : 0);
bar = "9";
result = foo == bar ? a.b() : null;

return result
