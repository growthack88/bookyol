(function () {
    'use strict';

    var STORAGE_KEY = 'bookyol_homepage_active_tab';

    var CATEGORY_COLORS = ['biz', 'psy', 'self', 'prod', 'mkt', 'fin', 'lead', 'bio', 'sci', 'phil', 'his', 'cre'];
    var GRADIENTS = ['1', '2', '3', '4', '5', '6'];

    function $(sel, ctx) { return (ctx || document).querySelector(sel); }
    function $$(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

    function escapeHtml(str) {
        return String(str == null ? '' : str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // --- Tabs ---
    function activateTab(tabName) {
        $$('.bookyol-tabs .nav-tab').forEach(function (tab) {
            var isActive = tab.getAttribute('data-tab') === tabName;
            tab.classList.toggle('nav-tab-active', isActive);
        });
        $$('.bookyol-tab-pane').forEach(function (pane) {
            pane.style.display = (pane.getAttribute('data-pane') === tabName) ? '' : 'none';
        });
        try { localStorage.setItem(STORAGE_KEY, tabName); } catch (e) {}
    }

    function initTabs() {
        $$('.bookyol-tabs .nav-tab').forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                activateTab(tab.getAttribute('data-tab'));
            });
        });
        var saved = null;
        try { saved = localStorage.getItem(STORAGE_KEY); } catch (e) {}
        if (saved && $('.bookyol-tabs .nav-tab[data-tab="' + saved + '"]')) {
            activateTab(saved);
        }
    }

    // --- Repeaters ---
    function readJson(textareaId) {
        var el = document.getElementById(textareaId);
        if (!el || !el.value) return [];
        try {
            var v = JSON.parse(el.value);
            return Array.isArray(v) ? v : [];
        } catch (e) {
            return [];
        }
    }

    function writeJson(textareaId, data) {
        var el = document.getElementById(textareaId);
        if (el) el.value = JSON.stringify(data);
    }

    function colorSelectHtml(selected) {
        var html = '<select class="bookyol-field" data-field="color_class">';
        CATEGORY_COLORS.forEach(function (c) {
            html += '<option value="' + c + '"' + (c === selected ? ' selected' : '') + '>' + c + '</option>';
        });
        html += '</select>';
        return html;
    }

    function gradientSelectHtml(selected) {
        var html = '<select class="bookyol-field" data-field="gradient">';
        GRADIENTS.forEach(function (g) {
            html += '<option value="' + g + '"' + (String(g) === String(selected) ? ' selected' : '') + '>' + g + '</option>';
        });
        html += '</select>';
        return html;
    }

    function renderCategoriesRow(row) {
        row = row || { icon: '', name: '', url: '', color_class: 'biz' };
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td><input type="text" class="bookyol-field" data-field="icon" value="' + escapeHtml(row.icon) + '"></td>' +
            '<td><input type="text" class="bookyol-field" data-field="name" value="' + escapeHtml(row.name) + '"></td>' +
            '<td><input type="text" class="bookyol-field" data-field="url" value="' + escapeHtml(row.url) + '"></td>' +
            '<td>' + colorSelectHtml(row.color_class || 'biz') + '</td>' +
            '<td><button type="button" class="bookyol-repeater-remove">Remove</button></td>';
        return tr;
    }

    function renderCollectionsRow(row) {
        row = row || { emoji: '', title: '', count: '', url: '', gradient: '1' };
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td><input type="text" class="bookyol-field" data-field="emoji" value="' + escapeHtml(row.emoji) + '"></td>' +
            '<td><input type="text" class="bookyol-field" data-field="title" value="' + escapeHtml(row.title) + '"></td>' +
            '<td><input type="text" class="bookyol-field" data-field="count" value="' + escapeHtml(row.count) + '"></td>' +
            '<td><input type="text" class="bookyol-field" data-field="url" value="' + escapeHtml(row.url) + '"></td>' +
            '<td>' + gradientSelectHtml(row.gradient || '1') + '</td>' +
            '<td><button type="button" class="bookyol-repeater-remove">Remove</button></td>';
        return tr;
    }

    function serializeTable(tableId) {
        var rows = $$('#' + tableId + ' tbody tr');
        return rows.map(function (tr) {
            var obj = {};
            $$('.bookyol-field', tr).forEach(function (el) {
                obj[el.getAttribute('data-field')] = el.value;
            });
            return obj;
        });
    }

    function attachRepeater(tableId, textareaId, renderFn) {
        var tbody = $('#' + tableId + ' tbody');
        if (!tbody) return;

        var initialData = readJson(textareaId);
        if (initialData.length) {
            initialData.forEach(function (row) {
                tbody.appendChild(renderFn(row));
            });
        }

        function sync() {
            writeJson(textareaId, serializeTable(tableId));
        }

        tbody.addEventListener('input', function (e) {
            if (e.target.classList.contains('bookyol-field')) sync();
        });
        tbody.addEventListener('change', function (e) {
            if (e.target.classList.contains('bookyol-field')) sync();
        });
        tbody.addEventListener('click', function (e) {
            if (e.target.classList.contains('bookyol-repeater-remove')) {
                e.preventDefault();
                var tr = e.target.closest('tr');
                if (tr) tr.remove();
                sync();
            }
        });

        var addBtn = document.querySelector('.bookyol-repeater-add[data-target="' + tableId + '"]');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                tbody.appendChild(renderFn());
                sync();
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initTabs();
        attachRepeater('bookyol-categories-repeater', 'bookyol_categories_json', renderCategoriesRow);
        attachRepeater('bookyol-collections-repeater', 'bookyol_collections_json', renderCollectionsRow);
    });
})();
