var randomOk = true;
var changed = false;
var lastNumber = null;

for (var i = 0; i < 50; i++) {
    var rand = Math.random();
    if (lastNumber && lastNumber !== rand) {
        changed = true;
    }
    lastNumber = rand;
    if (rand < 0 || rand >= 1) {
        randomOk = false;
    }
}

return (randomOk && changed) ? (Math.round(Math.min(25, 38) / Math.max(4, 10)) + Math.floor(4.6) - Math.ceil(4.6)) : -1;
