<style>
    /* Live Search Autocomplete Styles */
    .live-search-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        width: 100%;
        min-width: 320px;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.16), 0 2px 8px rgba(0, 0, 0, 0.06);
        border: 1px solid var(--od-border, #E5DFC9);
        z-index: 1060;
        display: none;
        overflow: hidden;
        animation: searchFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        text-align: left;
    }

    @media (max-width: 768px) {
        .live-search-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            width: 100%;
            min-width: unset;
        }
    }

    @keyframes searchFadeIn {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .search-results-scroll {
        max-height: 400px;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .search-results-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .search-results-scroll::-webkit-scrollbar-thumb {
        background: #d4ccb8;
        border-radius: 4px;
    }

    .search-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        text-decoration: none;
        color: #212529;
        transition: background-color 0.15s ease, transform 0.1s ease;
        border-bottom: 1px solid #f6f3eb;
    }

    .search-item:last-child {
        border-bottom: none;
    }

    .search-item:hover,
    .search-item.active-focus {
        background-color: #FAF7F0;
        color: var(--od-dark-green, #1F3A27);
    }

    .search-item .item-thumb {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        object-fit: cover;
        background: #EDE8D0;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .search-section-header {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        color: #8C827A;
        padding: 8px 16px 4px 16px;
        background: #FAF8F5;
        border-bottom: 1px solid #EFEAE1;
    }

    .search-highlight {
        font-weight: 700;
        color: #1F3A27;
        background-color: #FFF2B2;
        padding: 0 2px;
        border-radius: 3px;
    }

    .search-spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(47, 93, 58, 0.25);
        border-radius: 50%;
        border-top-color: #2F5D3A;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const AUTOCOMPLETE_URL = '{{ route('api.search.autocomplete') }}';
    const searchInputs = document.querySelectorAll('.live-search-input');

    searchInputs.forEach(input => {
        const form = input.closest('form') || input.parentElement;
        if (!form) return;

        // Ensure parent form has relative positioning
        form.classList.add('position-relative');

        // Create dropdown container and append directly to form
        let dropdown = form.querySelector('.live-search-dropdown');
        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.className = 'live-search-dropdown shadow-lg';
            form.appendChild(dropdown);
        }

        let debounceTimer = null;
        let cache = {};
        let activeIndex = -1;

        // Debounce input event
        input.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            clearTimeout(debounceTimer);

            if (query.length < 1) {
                hideDropdown(dropdown);
                return;
            }

            showLoading(dropdown, query);

            if (cache[query]) {
                renderResults(dropdown, cache[query], query, form);
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`${AUTOCOMPLETE_URL}?q=${encodeURIComponent(query)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) throw new Error('Search failed');
                    const data = await res.json();
                    cache[query] = data;
                    renderResults(dropdown, data, query, form);
                } catch (err) {
                    console.error('Search error:', err);
                    renderError(dropdown, query);
                }
            }, 240);
        });

        // Focus & Click triggers
        input.addEventListener('focus', () => {
            const query = input.value.trim();
            if (query.length >= 1 && cache[query]) {
                renderResults(dropdown, cache[query], query, form);
            }
        });

        // Keyboard navigation
        input.addEventListener('keydown', (e) => {
            const items = dropdown.querySelectorAll('.search-item');
            if (!dropdown.style.display || dropdown.style.display === 'none' || items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                updateActiveItem(items, activeIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                updateActiveItem(items, activeIndex);
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && items[activeIndex]) {
                    e.preventDefault();
                    items[activeIndex].click();
                }
            } else if (e.key === 'Escape') {
                hideDropdown(dropdown);
            }
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!form.contains(e.target)) {
                hideDropdown(dropdown);
            }
        });
    });

    function showLoading(dropdown, query) {
        dropdown.innerHTML = `
            <div class="p-3 text-center text-muted small d-flex align-items-center justify-content-center gap-2">
                <span class="search-spinner"></span>
                <span>Searching Himalayan dairy for "<strong>${escapeHtml(query)}</strong>"...</span>
            </div>
        `;
        dropdown.style.display = 'block';
    }

    function renderResults(dropdown, data, query, form) {
        const { products = [], categories = [], sellers = [], total = 0 } = data;

        if (products.length === 0 && categories.length === 0 && sellers.length === 0) {
            dropdown.innerHTML = `
                <div class="p-4 text-center">
                    <div class="rounded-circle bg-light text-muted d-inline-flex p-3 mb-2">
                        <i class="bi bi-search fs-3 text-gold"></i>
                    </div>
                    <h6 class="font-serif fw-bold text-dark mb-1">No Dairy Products Found</h6>
                    <p class="small text-muted mb-3">We couldn't find matches for "<strong>${escapeHtml(query)}</strong>".</p>
                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                        <span class="badge bg-light text-dark border cursor-pointer hover-gold px-2.5 py-1" onclick="quickFillSearch('${form.id || ''}', 'Yak Milk')">Yak Milk</span>
                        <span class="badge bg-light text-dark border cursor-pointer hover-gold px-2.5 py-1" onclick="quickFillSearch('${form.id || ''}', 'Desi Ghee')">Desi Ghee</span>
                        <span class="badge bg-light text-dark border cursor-pointer hover-gold px-2.5 py-1" onclick="quickFillSearch('${form.id || ''}', 'Artisan Cheese')">Artisan Cheese</span>
                        <span class="badge bg-light text-dark border cursor-pointer hover-gold px-2.5 py-1" onclick="quickFillSearch('${form.id || ''}', 'Bilona Butter')">Bilona Butter</span>
                    </div>
                </div>
            `;
            dropdown.style.display = 'block';
            return;
        }

        let html = '<div class="search-results-scroll">';

        // 1. Categories Suggestions
        if (categories.length > 0) {
            html += `<div class="search-section-header"><i class="bi bi-tags me-1"></i> Categories</div>`;
            categories.forEach(cat => {
                html += `
                    <a href="${cat.url}" class="search-item py-2">
                        <div class="item-thumb bg-success-subtle text-success" style="width: 34px; height: 34px; border-radius: 8px;">
                            <i class="bi bi-grid-fill fs-6"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="small fw-bold text-dark text-truncate">${highlightMatch(cat.name, query)}</div>
                            <div class="text-xs text-muted">${cat.products_count} Available Products</div>
                        </div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                `;
            });
        }

        // 2. Products List
        if (products.length > 0) {
            html += `<div class="search-section-header"><i class="bi bi-box-seam me-1"></i> Fresh Mountain Dairy</div>`;
            products.forEach(prod => {
                const imgHtml = prod.image_url 
                    ? `<img src="${prod.image_url}" alt="${escapeHtml(prod.name)}" class="w-100 h-100 object-fit-cover">`
                    : `<i class="bi bi-cup-hot fs-5 text-success"></i>`;
                
                const stockBadge = prod.in_stock 
                    ? `<span class="badge bg-success-subtle text-success border border-success-subtle text-xs px-1.5 py-0.5">In Stock</span>`
                    : `<span class="badge bg-danger-subtle text-danger border border-danger-subtle text-xs px-1.5 py-0.5">Sold Out</span>`;

                html += `
                    <a href="${prod.url}" class="search-item">
                        <div class="item-thumb">
                            ${imgHtml}
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center justify-content-between gap-1 mb-0.5">
                                <span class="small fw-bold text-dark text-truncate">${highlightMatch(prod.name, query)}</span>
                                <span class="small fw-bold text-dark-green font-monospace flex-shrink-0">${prod.formatted_price}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 text-xs text-muted">
                                <span class="text-truncate">🏠 ${escapeHtml(prod.farm_name)}</span>
                                <span>&bull;</span>
                                ${stockBadge}
                            </div>
                        </div>
                    </a>
                `;
            });
        }

        // 3. Farms / Farmers
        if (sellers.length > 0) {
            html += `<div class="search-section-header"><i class="bi bi-geo-alt me-1"></i> Mountain Farms</div>`;
            sellers.forEach(seller => {
                html += `
                    <a href="${seller.url}" class="search-item py-2">
                        <div class="item-thumb bg-warning-subtle text-warning-emphasis" style="width: 34px; height: 34px; border-radius: 8px;">
                            <i class="bi bi-shop fs-6"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="small fw-bold text-dark text-truncate">${highlightMatch(seller.farm_name, query)}</div>
                            <div class="text-xs text-muted"><i class="bi bi-pin-map me-1"></i>${escapeHtml(seller.location)}</div>
                        </div>
                        <i class="bi bi-arrow-up-right text-muted small"></i>
                    </a>
                `;
            });
        }

        html += '</div>';

        // Bottom Footer Button to submit form
        html += `
            <div class="p-2.5 bg-light border-top d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-sm btn-darkgreen rounded-pill px-3 py-1.5 text-xs fw-semibold w-100">
                    <i class="bi bi-search me-1"></i> View all ${total} results for "<strong>${escapeHtml(query)}</strong>" &rarr;
                </button>
            </div>
        `;

        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
    }

    function renderError(dropdown, query) {
        dropdown.innerHTML = `
            <div class="p-3 text-center text-muted small">
                <i class="bi bi-exclamation-circle text-danger me-1"></i> Could not fetch suggestions. Press Enter to search normally.
            </div>
        `;
        dropdown.style.display = 'block';
    }

    function hideDropdown(dropdown) {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
    }

    function updateActiveItem(items, index) {
        items.forEach((it, i) => {
            if (i === index) {
                it.classList.add('active-focus');
                it.scrollIntoView({ block: 'nearest' });
            } else {
                it.classList.remove('active-focus');
            }
        });
    }

    function highlightMatch(text, query) {
        if (!text || !query) return escapeHtml(text || '');
        const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        return escapeHtml(text).replace(regex, '<span class="search-highlight">$1</span>');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    window.quickFillSearch = function(formId, term) {
        let input;
        if (formId) {
            const form = document.getElementById(formId);
            if (form) input = form.querySelector('.live-search-input');
        }
        if (!input) input = document.querySelector('.live-search-input');
        
        if (input) {
            input.value = term;
            input.focus();
            input.dispatchEvent(new Event('input'));
        }
    };
});
</script>
