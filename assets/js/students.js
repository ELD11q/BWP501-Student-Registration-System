/*
 * students.js - client side of search, sorting and pagination on view_students.php
 *  - Typing in the search box filters the list live (AJAX, debounced) without reloading.
 *  - Pagination (Previous/Next/numbers) and column sorting links load via AJAX.
 *  - The URL is updated (history API) so refresh/back keep the same page.
 *  - Delete buttons open a Bootstrap confirmation modal.
 * Without JavaScript, every link/form still works with normal page loads (server-side).
 */
(function () {
    'use strict';

    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('search');
    let controller;

    function currentResults() {
        return document.getElementById('results');
    }

    function load(url, push) {
        if (controller) controller.abort();
        controller = new AbortController();
        const ajaxUrl = url + (url.includes('?') ? '&' : '?') + 'ajax=1';
        currentResults().classList.add('loading');

        fetch(ajaxUrl, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => {
                if (res.redirected && res.url.includes('login.html')) {
                    window.location.href = res.url;
                    throw new Error('login');
                }
                return res.text();
            })
            .then(html => {
                currentResults().outerHTML = html;
                if (push) history.pushState(null, '', url);
                syncHiddenInputs(url);
            })
            .catch(err => { if (err.name !== 'AbortError') console.error(err); })
            .finally(() => { const r = currentResults(); if (r) r.classList.remove('loading'); });
    }

    // Keep the search form's sort/order hidden inputs in sync with the URL
    function syncHiddenInputs(url) {
        const p = new URL(url, window.location.href).searchParams;
        if (p.get('sort')) searchForm.elements.sort.value = p.get('sort');
        if (p.get('order')) searchForm.elements.order.value = p.get('order');
    }

    function searchUrl() {
        const params = new URLSearchParams(new FormData(searchForm));
        if (!params.get('search')) params.delete('search');
        params.set('page', '1');
        return 'view_students.php?' + params.toString();
    }

    // Live search (debounced)
    let timer;
    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => load(searchUrl(), true), 350);
    });
    searchForm.addEventListener('submit', function (e) {
        e.preventDefault();
        load(searchUrl(), true);
    });

    // Event delegation: pagination, sort and delete (the table is replaced after each AJAX load)
    document.addEventListener('click', function (e) {
        const link = e.target.closest('#results .page-link, #results .sort-link');
        if (link) {
            e.preventDefault();
            if (link.closest('.page-item.disabled')) return;
            load(link.getAttribute('href'), true);
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        const del = e.target.closest('.btn-delete');
        if (del) {
            e.preventDefault();
            document.getElementById('deleteId').value = del.dataset.id;
            document.getElementById('deleteName').textContent = del.dataset.name;
            document.getElementById('deleteReturn').value =
                'view_students.php' + window.location.search;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteModal')).show();
        }
    });

    // Back / forward buttons
    window.addEventListener('popstate', function () {
        const p = new URLSearchParams(window.location.search);
        searchInput.value = p.get('search') || '';
        load('view_students.php' + window.location.search, false);
    });
})();
