var klass = 'foo';
var settings = {
    element: 'bar',
    modifier: 'biz',
};
var modifier_class = klass.match(new RegExp('^(?!' + settings['element'] + '[A-Za-z0-9])' + settings['modifier'] + '(.+)$'))

return (modifier_class ? 'Y' : 'N') + (klass.match(/[a-z]/) ? 'Y' : 'N');
