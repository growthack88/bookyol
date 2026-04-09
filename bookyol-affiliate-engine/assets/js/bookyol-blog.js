(function () {
    'use strict';

    // ═══ READING PROGRESS BAR ═══
    var progress = document.getElementById('bookyol-progress');
    if (progress) {
        var updateProgress = function () {
            var winScroll = document.documentElement.scrollTop || document.body.scrollTop;
            var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            var scrolled = height > 0 ? (winScroll / height) * 100 : 0;
            progress.style.width = scrolled + '%';
        };
        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();
    }

    // ═══ TABLE OF CONTENTS ═══
    var content = document.getElementById('bookyol-content');
    var tocContainer = document.getElementById('bookyol-toc');
    var tocList = document.getElementById('bookyol-toc-list');

    if (content && tocContainer && tocList) {
        var headings = content.querySelectorAll('h2, h3');
        if (headings.length >= 3) {
            tocContainer.style.display = 'block';
            var ul = document.createElement('ul');

            headings.forEach(function (heading, index) {
                // Only use existing ID or add one
                if (!heading.id) {
                    heading.id = 'section-' + index;
                }
                var id = heading.id;

                var li = document.createElement('li');
                if (heading.tagName === 'H3') {
                    li.style.paddingLeft = '16px';
                }

                var a = document.createElement('a');
                a.href = '#' + id;
                a.textContent = heading.textContent;
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    // Update URL hash without jumping
                    if (history.pushState) {
                        history.pushState(null, null, '#' + id);
                    }
                });

                li.appendChild(a);
                ul.appendChild(li);
            });

            tocList.appendChild(ul);
        }

        // Wire the TOC toggle button.
        var tocToggle = tocContainer.querySelector('.bookyol-blog__toc-toggle');
        if (tocToggle) {
            tocToggle.addEventListener('click', function () {
                tocContainer.classList.toggle('collapsed');
                tocToggle.textContent = tocContainer.classList.contains('collapsed') ? '+' : '−';
            });
        }
    }

    // ═══ BACK TO TOP ═══
    var backTop = document.getElementById('bookyol-back-top');
    if (backTop) {
        var toggleBackTop = function () {
            if (window.scrollY > 500) {
                backTop.classList.add('visible');
            } else {
                backTop.classList.remove('visible');
            }
        };
        window.addEventListener('scroll', toggleBackTop, { passive: true });
        toggleBackTop();

        backTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ═══ COPY LINK BUTTONS ═══
    var copyButtons = document.querySelectorAll('.bookyol-blog__share-btn--copy');
    copyButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-copy-url') || window.location.href;
            var done = function () {
                var original = btn.textContent;
                btn.textContent = '✓';
                setTimeout(function () { btn.textContent = '🔗'; }, 2000);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done).catch(function () {
                    // Fallback: prompt the user
                    window.prompt('Copy this link:', url);
                });
            } else {
                // Legacy fallback — use execCommand.
                var ta = document.createElement('textarea');
                ta.value = url;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); done(); }
                catch (e) { window.prompt('Copy this link:', url); }
                document.body.removeChild(ta);
            }
        });
    });
})();
