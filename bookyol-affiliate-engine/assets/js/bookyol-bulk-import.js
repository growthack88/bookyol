(function () {
    'use strict';

    var lookupResults = [];

    function escapeHtml(str) {
        return String(str == null ? '' : str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function sleep(ms) {
        return new Promise(function (r) { setTimeout(r, ms); });
    }

    function lookupOne(query) {
        var fd = new FormData();
        fd.append('action', 'bookyol_lookup_book');
        fd.append('nonce', BookYolBulk.lookupNonce);
        fd.append('query', query);
        return fetch(BookYolBulk.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        }).then(function (r) { return r.json(); }).then(function (json) {
            if (json && json.success && json.data && json.data.books && json.data.books.length) {
                return json.data.books[0];
            }
            return null;
        }).catch(function () { return null; });
    }

    function renderTable() {
        var container = document.getElementById('bookyol-bulk-results');
        if (!container) return;

        if (!lookupResults.length) {
            container.innerHTML = '';
            document.getElementById('bookyol-bulk-import-row').style.display = 'none';
            return;
        }

        var html = '<table class="bookyol-bulk-table widefat striped"><thead><tr>' +
            '<th style="width:30px;"><input type="checkbox" id="bookyol-bulk-checkall" checked></th>' +
            '<th>Cover</th><th>Title</th><th>Author</th><th>Pages</th><th>ISBN</th>' +
            '</tr></thead><tbody>';

        lookupResults.forEach(function (row, idx) {
            var cls = row.book ? '' : 'not-found';
            var book = row.book;
            var checked = book ? 'checked' : '';
            var disabled = book ? '' : 'disabled';
            var cover = (book && book.thumbnail) ? '<img src="' + escapeHtml(book.thumbnail) + '" width="40">' : '—';
            var title = book ? escapeHtml(book.title || '') : escapeHtml(row.query);
            var author = book ? escapeHtml((book.authors || []).join(', ')) : BookYolBulk.i18n.notFound;
            var pages = book ? (book.pageCount || '—') : '—';
            var isbn = book ? escapeHtml(book.isbn_13 || book.isbn_10 || '—') : '—';

            html += '<tr class="' + cls + '">' +
                '<td><input type="checkbox" class="bookyol-bulk-row" data-index="' + idx + '" ' + checked + ' ' + disabled + '></td>' +
                '<td>' + cover + '</td>' +
                '<td>' + title + '</td>' +
                '<td>' + author + '</td>' +
                '<td>' + escapeHtml(String(pages)) + '</td>' +
                '<td>' + isbn + '</td>' +
                '</tr>';
        });

        html += '</tbody></table>';
        container.innerHTML = html;

        var checkAll = document.getElementById('bookyol-bulk-checkall');
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                var boxes = container.querySelectorAll('.bookyol-bulk-row:not([disabled])');
                boxes.forEach(function (b) { b.checked = checkAll.checked; });
            });
        }

        document.getElementById('bookyol-bulk-import-row').style.display = '';
    }

    function runLookup() {
        var textarea = document.getElementById('bookyol-bulk-input');
        var raw = textarea.value || '';
        var lines = raw.split('\n').map(function (l) { return l.trim(); }).filter(Boolean);
        if (lines.length > 20) {
            lines = lines.slice(0, 20);
        }
        if (!lines.length) {
            return;
        }

        lookupResults = [];
        var progress = document.getElementById('bookyol-bulk-progress');
        var btn = document.getElementById('bookyol-bulk-lookup');
        btn.disabled = true;

        (async function () {
            for (var i = 0; i < lines.length; i++) {
                progress.textContent = BookYolBulk.i18n.looking + ' (' + (i + 1) + '/' + lines.length + ')';
                var book = await lookupOne(lines[i]);
                lookupResults.push({ query: lines[i], book: book });
                renderTable();
                if (i < lines.length - 1) {
                    await sleep(500);
                }
            }
            progress.textContent = BookYolBulk.i18n.done;
            btn.disabled = false;
        })();
    }

    function runImport() {
        var container = document.getElementById('bookyol-bulk-results');
        var checked = container.querySelectorAll('.bookyol-bulk-row:checked');
        if (!checked.length) {
            alert(BookYolBulk.i18n.noSelection);
            return;
        }

        var books = [];
        checked.forEach(function (cb) {
            var idx = parseInt(cb.getAttribute('data-index'), 10);
            if (lookupResults[idx] && lookupResults[idx].book) {
                books.push(lookupResults[idx].book);
            }
        });

        if (!books.length) {
            alert(BookYolBulk.i18n.noSelection);
            return;
        }

        var btn = document.getElementById('bookyol-bulk-import-btn');
        var summary = document.getElementById('bookyol-bulk-summary');
        btn.disabled = true;
        summary.textContent = BookYolBulk.i18n.importing;

        var fd = new FormData();
        fd.append('action', 'bookyol_bulk_import');
        fd.append('nonce', BookYolBulk.importNonce);
        fd.append('books', JSON.stringify(books));

        fetch(BookYolBulk.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        }).then(function (r) { return r.json(); }).then(function (json) {
            btn.disabled = false;
            if (json && json.success && json.data) {
                var d = json.data;
                var msg = d.imported + ' book(s) imported as drafts. ' + d.skipped + ' skipped (duplicate). ' + d.failed + ' failed.';
                summary.innerHTML = escapeHtml(msg) + ' <a href="' + escapeHtml(d.list_url) + '">Review drafts →</a>';
            } else {
                var m = (json && json.data && json.data.message) ? json.data.message : 'Import failed';
                summary.textContent = m;
            }
        }).catch(function () {
            btn.disabled = false;
            summary.textContent = 'Import failed';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var lookupBtn = document.getElementById('bookyol-bulk-lookup');
        if (lookupBtn) lookupBtn.addEventListener('click', runLookup);

        var importBtn = document.getElementById('bookyol-bulk-import-btn');
        if (importBtn) importBtn.addEventListener('click', runImport);
    });
})();
