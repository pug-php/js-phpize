var method = 'foo';
var obj = {
    foo: function () {
        return 'bar';
    },
};

var foo = function () {
    return obj[method]();
};

return foo();
