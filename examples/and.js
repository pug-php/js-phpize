a = [
    2 && null,
    null && 2,
    2 || null,
    null || 2,
    2 && (5 || 3),
    (0 && 6) || 7,
    '' || 'aa'
];

a.push(6) || a.push(7);

return JSON.stringify(a.map(JSON.stringify).join(','));
