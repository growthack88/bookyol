(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('bookyol-regenerate-btn');
        var status = document.getElementById('bookyol-regenerate-status');
        if (!btn) return;

        btn.addEventListener('click', function () {
            if (!confirm(BookYolSettings.i18n.confirmRun)) {
                return;
            }
            var overwrite = confirm(BookYolSettings.i18n.confirmOverwrite);

            btn.disabled = true;
            btn.textContent = BookYolSettings.i18n.regenerating;
            if (status) status.textContent = '';

            var body = new URLSearchParams();
            body.append('action', 'bookyol_regenerate_all_links');
            body.append('nonce', BookYolSettings.nonce);
            body.append('overwrite', overwrite ? 'true' : 'false');

            fetch(BookYolSettings.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(function (r) { return r.json(); }).then(function (json) {
                btn.disabled = false;
                btn.textContent = BookYolSettings.i18n.regenerateLabel;
                if (json && json.success && json.data && json.data.message) {
                    if (status) status.textContent = json.data.message;
                } else {
                    var msg = (json && json.data && json.data.message) ? json.data.message : BookYolSettings.i18n.failed;
                    if (status) status.textContent = msg;
                }
            }).catch(function () {
                btn.disabled = false;
                btn.textContent = BookYolSettings.i18n.regenerateLabel;
                if (status) status.textContent = BookYolSettings.i18n.failed;
            });
        });
    });
})();
