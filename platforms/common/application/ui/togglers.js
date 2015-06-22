"use strict";
var ready = require('elements/domready'),
    $     = require('elements');

var hiddens,
    toggles = function(event, element) {
        if (event.type.match(/^touch/) || event.type == 'click') { event.preventDefault(); }
        if (event.type == 'click') { return false; }
        element = $(element);
        hiddens = element.find('~~ [type=hidden]');

        if (!hiddens) return true;
        hiddens.value(hiddens.value() == '0' ? '1' : '0');

        hiddens.emit('change');
        $('body').emit('change', { target: hiddens });

        return false;
    };

ready(function() {
    ['touchend', 'mouseup', 'click'].forEach(function(event) {
        $('body').delegate(event, '.enabler .toggle', toggles);
    });
});

module.exports = {};
