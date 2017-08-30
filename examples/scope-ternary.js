var a = true;
var b = [2];
var c = 5;
var d = 0;

var func = function (e) {
    return [a && !e ? b[d] : {c: c}];
};

var resultA = func(false);
var resultB = func(true);

return resultA[0] + resultB[0].c;
