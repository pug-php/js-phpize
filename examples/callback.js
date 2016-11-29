a = b => b * 2;
b = function (c, d, e) {
    return c + d(2) + e();
};

return b(2, a, function () {
    return 8;
});
