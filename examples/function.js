biz = function biz(str) {
    return str;
};

foo = [1, 2, 3];
bar = 1;

return biz(implode(' - ', array_slice(foo, bar)));
