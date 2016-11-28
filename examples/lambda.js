a = a => a * 2;
var b = a(2);
a = () => {
    return 5;
};
b += a();
a = () => 3;

return a() + b;
