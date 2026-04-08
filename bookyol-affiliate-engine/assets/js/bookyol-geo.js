(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('bookyol_cover_upload');
        var input = document.getElementById('bookyol_cover_url');
        if (!btn || !input || typeof wp === 'undefined' || !wp.media) {
            return;
        }

        var frame;
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: 'Select Cover Image',
                button: { text: 'Use this image' },
                library: { type: 'image' },
                multiple: false
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                input.value = attachment.url;
            });

            frame.open();
        });
    });
})();
