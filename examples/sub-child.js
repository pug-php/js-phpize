a = {
    b: {
        c: function () {
            return {
                d: [null, null, {
                    foo: function () {
                        return 'bar';
                    }
                }]
            }
        }
    }
};

return a.b['c']().d[2].foo();
