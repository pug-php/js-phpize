a = [
    2 && null,
    null && 2,
    2 || null,
    null || 2,
    2 && (5 || 3),
    (0 && 6) || 7,
    '' || 'aa'
];

return JSON.stringify(a.map(JSON.stringify).join(','));
