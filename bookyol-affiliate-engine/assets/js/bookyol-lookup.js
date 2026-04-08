(function () {
    'use strict';

    function $(id) { return document.getElementById(id); }

    function setStatus(msg, cls) {
        var el = $('bookyol-lookup-status');
        if (!el) return;
        el.className = cls || '';
        el.textContent = msg || '';
    }

    function escapeHtml(str) {
        return String(str == null ? '' : str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function renderResults(books) {
        var container = $('bookyol-lookup-results');
        container.innerHTML = '';
        container.style.display = 'block';

        if (!books || !books.length) {
            container.style.display = 'none';
            return;
        }

        books.forEach(function (book, idx) {
            var div = document.createElement('div');
            div.className = 'bookyol-lookup-result';
            div.setAttribute('data-index', idx);
            var authors = (book.authors || []).join(', ');
            var meta = [authors, book.publishedDate, (book.pageCount ? book.pageCount + ' pages' : '')].filter(Boolean).join(' · ');
            var thumb = book.thumbnail || '';
            div.innerHTML =
                (thumb ? '<img src="' + escapeHtml(thumb) + '" width="60" alt="">' : '<div style="width:60px;"></div>') +
                '<div><strong>' + escapeHtml(book.title) + '</strong><br><small>' + escapeHtml(meta) + '</small></div>';
            div.addEventListener('click', function () {
                fillForm(book);
            });
            container.appendChild(div);
        });
    }

    function setPostTitle(title) {
        if (window.wp && wp.data && wp.data.dispatch && wp.data.select) {
            var editor = wp.data.dispatch('core/editor');
            if (editor && typeof editor.editPost === 'function') {
                try {
                    editor.editPost({ title: title });
                    return;
                } catch (e) {}
            }
        }
        var classic = document.getElementById('title');
        if (classic) {
            classic.value = title;
            if (typeof classic.dispatchEvent === 'function') {
                classic.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
        var gutTitle = document.querySelector('.editor-post-title__input');
        if (gutTitle && gutTitle.value !== undefined) {
            gutTitle.value = title;
        }
    }

    function setExcerpt(description) {
        if (window.wp && wp.data && wp.data.dispatch) {
            var editor = wp.data.dispatch('core/editor');
            if (editor && typeof editor.editPost === 'function') {
                try {
                    editor.editPost({ excerpt: description });
                } catch (e) {}
            }
        }
        var excerptField = document.getElementById('excerpt');
        if (excerptField) {
            excerptField.value = description;
        }
    }

    function setField(id, value) {
        var el = document.getElementById(id);
        if (el) {
            el.value = value == null ? '' : value;
        }
    }

    function fillForm(book) {
        var title = book.title || '';
        if (book.subtitle) {
            title = title + ': ' + book.subtitle;
        }
        setPostTitle(title);

        var authors = (book.authors || []).join(', ');
        setField('bookyol_book_author', authors);

        var bestThumb = book.thumbnail || '';
        setField('bookyol_cover_url', bestThumb);

        var isbn = book.isbn_13 || book.isbn_10 || '';
        setField('bookyol_isbn', isbn);

        setField('bookyol_pages', book.pageCount || '');

        var categories = (book.categories || []).join(', ');
        setField('bookyol_best_for', categories);

        var description = (book.description || '').replace(/<[^>]*>/g, '');
        if (description.length > 300) {
            description = description.substring(0, 300);
        }
        setExcerpt(description);

        fetchAffiliateLinks(book);

        setStatus('✓ Book data loaded. Review and save.', 'bookyol-status-success');
    }

    function fetchAffiliateLinks(book) {
        var isbn = book.isbn_13 || book.isbn_10 || '';
        var fd = new FormData();
        fd.append('action', 'bookyol_generate_links');
        fd.append('nonce', BookYolLookup.nonce);
        fd.append('isbn', isbn);
        fd.append('title', book.title || '');
        fd.append('author', (book.authors || []).join(', '));

        fetch(BookYolLookup.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        }).then(function (r) { return r.json(); }).then(function (json) {
            if (!json || !json.success || !json.data || !json.data.links) return;
            var links = json.data.links;
            Object.keys(links).forEach(function (platform) {
                var field = document.getElementById('bookyol_link_' + platform);
                if (field && (!field.value || field.value === 'https://')) {
                    field.value = links[platform];
                    var row = field.closest('tr');
                    if (row) {
                        var dot = row.querySelector('.bookyol-status-dot');
                        if (dot) dot.classList.add('bookyol-status-dot--active');
                    }
                }
            });
        }).catch(function () {});
    }

    function doLookup() {
        var input = $('bookyol-lookup-query');
        var btn = $('bookyol-lookup-btn');
        if (!input) return;
        var query = (input.value || '').trim();
        if (!query) {
            setStatus('Please enter a title or ISBN', 'bookyol-status-error');
            return;
        }

        setStatus('Searching Google Books…', 'bookyol-status-loading');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Searching…';
        }

        var formData = new FormData();
        formData.append('action', 'bookyol_lookup_book');
        formData.append('nonce', BookYolLookup.nonce);
        formData.append('query', query);

        fetch(BookYolLookup.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        }).then(function (res) {
            return res.json();
        }).then(function (json) {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Lookup';
            }
            if (json && json.success && json.data && json.data.books) {
                setStatus('Found ' + json.data.books.length + ' result(s). Click one to fill the form.', '');
                renderResults(json.data.books);
            } else {
                var msg = (json && json.data && json.data.message) ? json.data.message : 'No results found';
                setStatus(msg, 'bookyol-status-error');
                renderResults([]);
            }
        }).catch(function () {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Lookup';
            }
            setStatus('API error, try again', 'bookyol-status-error');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var btn = $('bookyol-lookup-btn');
        var input = $('bookyol-lookup-query');
        if (btn) {
            btn.addEventListener('click', doLookup);
        }
        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    doLookup();
                }
            });
        }
    });
})();
