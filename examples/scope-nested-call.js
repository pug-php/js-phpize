var method = 'foo';
var objOutside = {
    foo: function () {
        return 'bar';
    },
};

var foo = function () {
    var objInside = objOutside;

    return objInside[method]();
};

return foo();
