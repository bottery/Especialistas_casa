// Sistema de Autocompletado Inteligente
class SmartAutocomplete {
    constructor(inputSelector, options = {}) {
        this.input = document.querySelector(inputSelector);
        if (!this.input) {
            console.error('Input not found:', inputSelector);
            return;
        }

        this.options = {
            minChars: options.minChars || 2,
            maxResults: options.maxResults || 10,
            data: options.data || [],
            searchKeys: options.searchKeys || ['value'],
            onSelect: options.onSelect || (() => {}),
            placeholder: options.placeholder || 'Buscar...',
            showRecent: options.showRecent !== false,
            storageKey: options.storageKey || 'autocomplete_recent'
        };

        this.recentSearches = this.loadRecent();
        this.currentFocus = -1;
        this.isOpen = false;
        
        this.init();
    }

    init() {
        this.createAutocompleteContainer();
        this.attachEventListeners();
        
        if (this.options.placeholder) {
            this.input.placeholder = this.options.placeholder;
        }
    }

    createAutocompleteContainer() {
        // Wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'autocomplete-wrapper relative';
        this.input.parentNode.insertBefore(wrapper, this.input);
        wrapper.appendChild(this.input);

        // Results container
        const resultsContainer = document.createElement('div');
        resultsContainer.className = 'autocomplete-results absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-64 overflow-y-auto hidden';
        wrapper.appendChild(resultsContainer);

        this.wrapper = wrapper;
        this.resultsContainer = resultsContainer;
    }

    attachEventListeners() {
        // Input events
        this.input.addEventListener('input', () => this.handleInput());
        this.input.addEventListener('focus', () => this.handleFocus());
        this.input.addEventListener('keydown', (e) => this.handleKeydown(e));

        // Click outside
        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target)) {
                this.close();
            }
        });
    }

    handleInput() {
        const query = this.input.value.trim();
        
        if (query.length < this.options.minChars) {
            this.close();
            return;
        }

        const results = this.search(query);
        this.showResults(results, query);
    }

    handleFocus() {
        const query = this.input.value.trim();
        
        if (query.length >= this.options.minChars) {
            const results = this.search(query);
            this.showResults(results, query);
        } else if (this.options.showRecent && this.recentSearches.length > 0) {
            this.showRecentSearches();
        }
    }

    handleKeydown(e) {
        if (!this.isOpen) return;

        const items = this.resultsContainer.querySelectorAll('.autocomplete-item');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            this.currentFocus++;
            if (this.currentFocus >= items.length) this.currentFocus = 0;
            this.setActive(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            this.currentFocus--;
            if (this.currentFocus < 0) this.currentFocus = items.length - 1;
            this.setActive(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (this.currentFocus > -1 && items[this.currentFocus]) {
                items[this.currentFocus].click();
            }
        } else if (e.key === 'Escape') {
            this.close();
        }
    }

    setActive(items) {
        items.forEach((item, index) => {
            if (index === this.currentFocus) {
                item.classList.add('bg-indigo-50', 'dark:bg-gray-700');
            } else {
                item.classList.remove('bg-indigo-50', 'dark:bg-gray-700');
            }
        });
    }

    search(query) {
        const lowerQuery = query.toLowerCase();
        
        return this.options.data
            .map(item => {
                const score = this.calculateScore(item, lowerQuery);
                return { item, score };
            })
            .filter(result => result.score > 0)
            .sort((a, b) => b.score - a.score)
            .slice(0, this.options.maxResults)
            .map(result => result.item);
    }

    calculateScore(item, query) {
        let score = 0;
        
        this.options.searchKeys.forEach(key => {
            const value = String(item[key] || '').toLowerCase();
            
            if (value === query) {
                score += 100; // Coincidencia exacta
            } else if (value.startsWith(query)) {
                score += 50; // Comienza con la query
            } else if (value.includes(query)) {
                score += 25; // Contiene la query
            }
        });

        return score;
    }

    showResults(results, query) {
        if (results.length === 0) {
            this.showNoResults(query);
            return;
        }

        let html = results.map(item => {
            const display = this.formatResult(item, query);
            return `
                <div class="autocomplete-item px-4 py-3 cursor-pointer hover:bg-indigo-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0 transition"
                     data-item='${JSON.stringify(item).replace(/'/g, "&#39;")}'>
                    ${display}
                </div>
            `;
        }).join('');

        this.resultsContainer.innerHTML = html;
        this.resultsContainer.classList.remove('hidden');
        this.isOpen = true;
        this.currentFocus = -1;

        // Attach click events
        this.resultsContainer.querySelectorAll('.autocomplete-item').forEach(el => {
            el.addEventListener('click', () => {
                const item = JSON.parse(el.getAttribute('data-item'));
                this.selectItem(item);
            });
        });
    }

    showRecentSearches() {
        if (this.recentSearches.length === 0) return;

        let html = `
            <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">
                Búsquedas recientes
            </div>
        `;

        html += this.recentSearches.map(item => {
            const display = this.formatResult(item);
            return `
                <div class="autocomplete-item px-4 py-3 cursor-pointer hover:bg-indigo-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0 transition flex items-center gap-2"
                     data-item='${JSON.stringify(item).replace(/'/g, "&#39;")}'>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    ${display}
                </div>
            `;
        }).join('');

        this.resultsContainer.innerHTML = html;
        this.resultsContainer.classList.remove('hidden');
        this.isOpen = true;

        // Attach click events
        this.resultsContainer.querySelectorAll('.autocomplete-item').forEach(el => {
            el.addEventListener('click', () => {
                const item = JSON.parse(el.getAttribute('data-item'));
                this.selectItem(item);
            });
        });
    }

    showNoResults(query) {
        this.resultsContainer.innerHTML = `
            <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p>No se encontraron resultados para "<strong>${query}</strong>"</p>
            </div>
        `;
        this.resultsContainer.classList.remove('hidden');
        this.isOpen = true;
    }

    formatResult(item, query = null) {
        const mainKey = this.options.searchKeys[0];
        let text = String(item[mainKey] || '');

        // Highlight query
        if (query) {
            const regex = new RegExp(`(${query})`, 'gi');
            text = text.replace(regex, '<strong class="text-indigo-600 dark:text-indigo-400">$1</strong>');
        }

        return `<div class="text-gray-900 dark:text-gray-100">${text}</div>`;
    }

    selectItem(item) {
        const mainKey = this.options.searchKeys[0];
        this.input.value = item[mainKey] || '';
        this.close();
        
        // Save to recent
        this.addToRecent(item);
        
        // Callback
        this.options.onSelect(item);
    }

    addToRecent(item) {
        // Remove if already exists
        this.recentSearches = this.recentSearches.filter(
            recent => JSON.stringify(recent) !== JSON.stringify(item)
        );
        
        // Add to beginning
        this.recentSearches.unshift(item);
        
        // Limit to 5 recent
        this.recentSearches = this.recentSearches.slice(0, 5);
        
        // Save to localStorage
        this.saveRecent();
    }

    loadRecent() {
        try {
            const saved = localStorage.getItem(this.options.storageKey);
            return saved ? JSON.parse(saved) : [];
        } catch {
            return [];
        }
    }

    saveRecent() {
        try {
            localStorage.setItem(this.options.storageKey, JSON.stringify(this.recentSearches));
        } catch (e) {
            console.error('Error saving recent searches:', e);
        }
    }

    close() {
        this.resultsContainer.classList.add('hidden');
        this.resultsContainer.innerHTML = '';
        this.isOpen = false;
        this.currentFocus = -1;
    }

    updateData(newData) {
        this.options.data = newData;
    }

    clear() {
        this.input.value = '';
        this.close();
    }
}

// Uso: new SmartAutocomplete('#search-input', { data: [...], searchKeys: ['name', 'email'], onSelect: (item) => {} });
