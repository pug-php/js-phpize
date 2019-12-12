name = 'Bob';

return `Hello ${name}, \${not} can you ${(function (verb) { return verb; })('tell')}?`;
