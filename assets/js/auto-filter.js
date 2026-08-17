/**
 * Client-side reusable auto-filtering utility
 * Filters card grids or table rows using CSS selectors and data attributes.
 */
(function() {
    function initClientFilters(context = document) {
        const filterInputs = context.querySelectorAll('.client-filter');
        
        filterInputs.forEach(input => {
            if (input.dataset.clientFilterBound) return;
            input.dataset.clientFilterBound = 'true';
            
            const eventType = input.tagName === 'SELECT' ? 'change' : 'input';
            
            const debounceFn = (fn, delay) => {
                let timer;
                return function(...args) {
                    clearTimeout(timer);
                    timer = setTimeout(() => fn.apply(this, args), delay);
                };
            };
            
            const handler = debounceFn(() => {
                applyClientFilters(input);
            }, 200);
            
            input.addEventListener(eventType, handler);
        });
    }

    function applyClientFilters(changedInput) {
        const targetSelector = changedInput.dataset.target;
        if (!targetSelector) return;
        
        const items = document.querySelectorAll(targetSelector);
        const container = changedInput.closest('form') || changedInput.closest('.filter-container') || document;
        const filters = container.querySelectorAll(`.client-filter[data-target="${targetSelector}"]`);
        
        items.forEach(item => {
            let matchesAll = true;
            
            filters.forEach(filter => {
                const val = filter.value.trim().toLowerCase();
                if (!val || val === 'all') return;
                
                const filterType = filter.dataset.filterType || 'search';
                
                if (filterType === 'search') {
                    // Match text content
                    const searchSelector = filter.dataset.searchSelector;
                    const searchIn = searchSelector 
                        ? item.querySelector(searchSelector)?.textContent 
                        : (item.dataset.search || item.textContent);
                        
                    if (!searchIn || !searchIn.toLowerCase().includes(val)) {
                        matchesAll = false;
                    }
                } else if (filterType === 'exact') {
                    // Match specific attribute value
                    const attrName = filter.dataset.filterAttr;
                    if (attrName) {
                        const itemVal = item.dataset[attrName] || item.getAttribute(`data-${attrName}`);
                        if (!itemVal || itemVal.toLowerCase() !== val) {
                            matchesAll = false;
                        }
                    }
                }
            });
            
            if (matchesAll) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Export function to window
    window.initClientFilters = initClientFilters;

    // Run automatically
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initClientFilters());
    } else {
        initClientFilters();
    }
})();
