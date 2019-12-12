name = 'Bob';

return `Hello ${name}, can you ${(function (verb) { return verb; })('tell')}?`;
