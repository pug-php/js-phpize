var values = {
    a: 'b',
    c: 'd',
};
var dump = [];
for (var key in values) {
    dump.push(key + ':' + values[key]);
}

return dump.join(',');
