{{-- resources/views/components/data-table.blade.php --}}
@props([
    'data' => [],
    'columns' => [],
    'actions' => [],
    'searchable' => true,
    'sortable' => true,
    'pagination' => true,
    'perPage' => 10,
    'tableId' => 'dataTable',
    'title' => '',
    'addButton' => null,
    'headerButtons' => [],
    // When true, column filters navigate via ?f[ColumnKey]=value (full dataset).
    'serverFilters' => false,
    // Distinct values per filterable column key (from full dataset / lookups).
    'filterOptions' => [],
    // Currently applied server filters: ['ColumnKey' => 'value']
    'activeServerFilters' => [],
])

<div class="bg-white dark:bg-slate-900 shadow-lg rounded-lg overflow-hidden border border-transparent dark:border-slate-800" x-data="dataTable({
    data: @js($data),
    columns: @js($columns),
    actions: @js($actions),
    searchable: @js($searchable),
    sortable: @js($sortable),
    pagination: @js($pagination),
    perPage: @js($perPage),
    title: @js($title),
    addButton: @js($addButton),
    headerButtons: @js($headerButtons),
    tableId: @js($tableId),
    serverFilters: @js((bool) $serverFilters),
    filterOptions: @js($filterOptions),
    activeServerFilters: @js($activeServerFilters)
})" x-init="init()">

    <!-- Header: Title, Search, Add Button -->
    <div class="p-4 bg-gray-50 dark:bg-slate-800/80 border-b dark:border-slate-700">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <!-- Add Button -->
            <div class="order-1 flex flex-wrap items-center gap-2">
                <a x-show="addButton" :href="addButton ? addButton.route : '#'"
                    :class="addButton ? addButton.cssClass : ''"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                    x-text="addButton ? addButton.label : ''">
                </a>

                <template x-for="button in headerButtons" :key="button.label">
                    <a :href="button.route || '#'"
                        :class="button.cssClass ||
                            'bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200'"
                        x-text="button.label">
                    </a>
                </template>
            </div>

            <!-- Title -->
            <div class="order-2 flex-1 text-center">
                <h2 x-show="title" x-text="title" class="text-xl font-bold text-gray-900 dark:text-slate-50"></h2>
            </div>

            <!-- Search -->
            <div x-show="searchable" class="order-3">
                <div class="relative">
                    <input type="text" x-model="searchTerm" @input.debounce.300ms="search()" placeholder="{{ __('Search...') }}"
                        class="w-full sm:w-64 pr-10 pl-4 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-teal-500/40 focus:border-transparent">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d=" M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div x-show="filterableColumns.length > 0" x-data="{ open: false }" class="border-b border-gray-200 dark:border-slate-700">
        <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-4 py-3 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors duration-200">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                </svg>
                <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ __('Filter') }}</span>

                <span x-show="hasActiveFilters()" x-text="Object.keys(activeFilters).length"
                    class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-emerald-600 rounded-full">
                </span>
            </div>

            <div class="flex items-center gap-3">
                <span x-show="hasActiveFilters()" @click.stop="clearAllFilters()"
                    class="text-xs text-red-500 hover:text-red-700 underline cursor-pointer">{{ __('Clear all') }}</span>

                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 transition-transform duration-200" :class="{ 'rotate-180': open }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1" class="px-4 py-4 bg-white dark:bg-slate-900">

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <template x-for="col in filterableColumns" :key="col.key">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500 dark:text-slate-400" x-text="col.label"></label>

                        <div class="relative">
                            <select @change="setFilter(col.key, $event.target.value)"
                                :value="activeFilters[col.key] || '__all__'"
                                :class="activeFilters[col.key] ?
                                    'border-emerald-400 ring-1 ring-emerald-300 bg-emerald-50 text-emerald-800 font-medium' :
                                    'border-gray-300 bg-white text-gray-700 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200'"
                                class="w-full text-sm border rounded-lg px-3 py-2 pr-8 appearance-none focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 cursor-pointer transition-colors duration-150">
                                <option value="__all__">{{ __('— All —') }}</option>
                                <template x-for="option in getDistinctValues(col.key)" :key="option">
                                    <option :value="option" x-text="option"></option>
                                </template>
                            </select>

                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="hasActiveFilters()" class="mt-3 flex flex-wrap gap-2">
                <template x-for="col in filterableColumns" :key="col.key">
                    <template x-if="activeFilters[col.key]">
                        <span
                            class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-xs font-medium px-3 py-1 rounded-full border border-emerald-200">
                            <span x-text="col.label + ': ' + activeFilters[col.key]"></span>
                            <button @click="clearFilter(col.key)" type="button"
                                class="hover:text-red-600 font-bold leading-none text-emerald-600 mr-1">
                                &times;
                            </button>
                        </span>
                    </template>
                </template>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 dark:bg-slate-800">
                <tr>
                    <template x-for="column in columns" :key="column.key">
                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                            <div class="flex items-center justify-between">
                                <span x-text="column.label"></span>

                                <button x-show="sortable && column.sortable !== false" @click="sort(column.key)"
                                    class="mr-2 text-gray-400 hover:text-gray-600 dark:hover:text-slate-200">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            :class="sortColumn === column.key ? 'text-blue-500 dark:text-teal-400' : ''"
                                            :d="sortColumn === column.key ?
                                                (sortDirection === 'desc' ?
                                                    'M19 9l-7 7-7-7' :
                                                    'M5 15l7-7 7 7') :
                                                'M8 9l4-4 4 4m0 6l-4 4-4-4'">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </th>
                    </template>

                    <th x-show="actions.length > 0"
                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
                <template x-for="(item, index) in paginatedData" :key="index">
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/80 transition-colors duration-200">
                        <template x-for="column in columns" :key="column.key">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div x-show="column.type === 'text' || !column.type">
                                    <span x-text="getNestedValue(item, column.key)"
                                        :class="column.cssClass || 'text-sm text-gray-900 dark:text-slate-100'"></span>
                                </div>

                                <div x-show="column.type === 'label'">
                                    <label :class="column.cssClass || 'text-blue-600 dark:text-sky-400 font-bold text-sm'"
                                        x-text="getNestedValue(item, column.key)"></label>
                                </div>

                                <div x-show="column.type === 'badge'">
                                    <span
                                        :class="column.cssClass ||
                                            'px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-slate-700 dark:text-slate-100'"
                                        x-text="getNestedValue(item, column.key)"></span>
                                </div>
                            </td>
                        </template>

                        <td x-show="actions.length > 0"
                            class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-reverse space-x-2">

                            <template x-for="action in actions" :key="action.name">
                                <template x-if="!isActionDisabled(action, item)">
                                    <a :href="buildActionRoute(action, item)"
                                        :class="action.cssClass ||
                                            'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200'"
                                        x-text="action.label">
                                    </a>
                                </template>
                            </template>

                            <template x-for="action in actions" :key="action.name + '-disabled'">
                                <template x-if="isActionDisabled(action, item)">
                                    <span
                                        :class="action.disabledClass ||
                                            'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-gray-400 cursor-not-allowed ml-2'"
                                        x-text="action.disabledLabel || action.label">
                                    </span>
                                </template>
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- EMPTY -->
    <div x-show="paginatedData.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            </path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-slate-100">{{ __('No data') }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">{{ __('No data found to display.') }}</p>
    </div>

    <!-- PAGINATION -->
    <div x-show="pagination && totalPages > 1"
        class="mt-0 border-t border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 sm:px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-gray-600 dark:text-slate-400 text-center sm:text-start">
                {{ __('Showing') }}
                <span class="font-semibold text-gray-900 dark:text-slate-100 mx-1" x-text="startRecord"></span>
                {{ __('to') }}
                <span class="font-semibold text-gray-900 dark:text-slate-100 mx-1" x-text="endRecord"></span>
                {{ __('of') }}
                <span class="font-semibold text-gray-900 dark:text-slate-100 mx-1" x-text="filteredData.length"></span>
                {{ __('results') }}
            </div>

            <div class="flex items-center justify-center gap-1" dir="ltr">
                <button type="button" @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:border-emerald-300 dark:hover:border-emerald-700 hover:text-emerald-700 dark:hover:text-emerald-300'"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-200 transition-colors"
                    aria-label="{{ __('Previous') }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <template x-for="page in visiblePages" :key="page">
                    <button type="button" @click="goToPage(page)"
                        :class="page === currentPage
                            ? 'bg-emerald-600 border-emerald-600 text-white shadow-sm shadow-emerald-600/30'
                            : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-600 text-gray-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:border-emerald-300 dark:hover:border-emerald-700 hover:text-emerald-700 dark:hover:text-emerald-300'"
                        class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-medium transition-colors"
                        x-text="page">
                    </button>
                </template>

                <button type="button" @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                    :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed' : 'hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:border-emerald-300 dark:hover:border-emerald-700 hover:text-emerald-700 dark:hover:text-emerald-300'"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-200 transition-colors"
                    aria-label="{{ __('Next') }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function dataTable(options) {
        return {
            originalData: options.data || [],
            filteredData: options.data || [],
            paginatedData: [],
            columns: options.columns || [],
            actions: options.actions || [],
            title: options.title || '',
            addButton: options.addButton || null,
            headerButtons: options.headerButtons || [],
            tableId: options.tableId || 'dataTable',

            searchable: options.searchable ?? true,
            sortable: options.sortable ?? true,
            pagination: options.pagination ?? true,
            perPage: options.perPage || 10,
            serverFilters: options.serverFilters ?? false,
            filterOptions: options.filterOptions || {},

            searchTerm: '',
            sortColumn: '',
            sortDirection: 'asc',
            currentPage: 1,
            activeFilters: options.activeServerFilters || {},

            get filterableColumns() {
                return this.columns.filter(c => c.filter === true);
            },

            get totalPages() {
                const pages = Math.ceil(this.filteredData.length / this.perPage);
                return pages > 0 ? pages : 1;
            },

            get startRecord() {
                if (this.filteredData.length === 0) return 0;
                return ((this.currentPage - 1) * this.perPage) + 1;
            },

            get endRecord() {
                const end = this.currentPage * this.perPage;
                return end > this.filteredData.length ? this.filteredData.length : end;
            },

            get visiblePages() {
                const pages = [];
                const total = this.totalPages;
                const current = this.currentPage;

                if (total <= 7) {
                    for (let i = 1; i <= total; i++) pages.push(i);
                } else {
                    if (current <= 4) {
                        for (let i = 1; i <= 5; i++) pages.push(i);
                        pages.push(total);
                    } else if (current >= total - 3) {
                        pages.push(1);
                        for (let i = total - 4; i <= total; i++) pages.push(i);
                    } else {
                        pages.push(1);
                        for (let i = current - 1; i <= current + 1; i++) pages.push(i);
                        pages.push(total);
                    }
                }

                return pages;
            },

            init() {
                if (this.serverFilters) {
                    // Server filters come from the URL; do not restore stale localStorage filters.
                    this.activeFilters = { ...(options.activeServerFilters || {}) };
                    this.applyAll(false);
                    return;
                }
                this.loadState();
                this.applyAll(false);
            },

            getStorageKey() {
                return `dataTableState_${this.tableId}`;
            },

            // Add this helper to know if this table should persist state
            shouldPersistState() {
                return this.tableId && this.tableId !== 'dataTable';
            },

            saveState() {
                if (!this.shouldPersistState()) return; // ← skip if no unique ID

                const state = {
                    searchTerm: this.searchTerm,
                    sortColumn: this.sortColumn,
                    sortDirection: this.sortDirection,
                    currentPage: this.currentPage,
                    activeFilters: this.activeFilters
                };
                localStorage.setItem(this.getStorageKey(), JSON.stringify(state));
            },

            loadState() {
                if (!this.shouldPersistState()) return; // ← skip if no unique ID

                const saved = localStorage.getItem(this.getStorageKey());
                if (!saved) return;

                try {
                    const state = JSON.parse(saved);
                    this.searchTerm = state.searchTerm || '';
                    this.sortColumn = state.sortColumn || '';
                    this.sortDirection = state.sortDirection || 'asc';
                    this.currentPage = parseInt(state.currentPage || 1);
                    this.activeFilters = state.activeFilters || {};
                } catch (e) {
                    localStorage.removeItem(this.getStorageKey());
                }
            },

            clearSavedState() {
                localStorage.removeItem(this.getStorageKey());
            },

            getDistinctValues(key) {
                if (this.serverFilters) {
                    const opts = this.filterOptions[key] || [];
                    return Array.from(opts).map(String).filter(v => v !== '').sort();
                }

                const seen = new Set();

                this.originalData.forEach(item => {
                    const val = this.getNestedValue(item, key);
                    if (val !== null && val !== undefined && val !== '') {
                        seen.add(String(val));
                    }
                });

                return Array.from(seen).sort();
            },

            navigateWithFilters(nextFilters) {
                const url = new URL(window.location.href);
                // Drop previous f[*] params
                [...url.searchParams.keys()]
                    .filter(k => k === 'f' || k.startsWith('f['))
                    .forEach(k => url.searchParams.delete(k));

                Object.entries(nextFilters || {}).forEach(([key, value]) => {
                    if (value !== null && value !== undefined && String(value) !== '' && String(value) !== '__all__') {
                        url.searchParams.set(`f[${key}]`, String(value));
                    }
                });
                url.searchParams.delete('page');
                window.location.href = url.toString();
            },

            setFilter(key, value) {
                if (this.serverFilters) {
                    const next = { ...this.activeFilters };
                    if (value === '__all__') {
                        delete next[key];
                    } else {
                        next[key] = value;
                    }
                    this.navigateWithFilters(next);
                    return;
                }

                if (value === '__all__') {
                    delete this.activeFilters[key];
                } else {
                    this.activeFilters[key] = value;
                }

                this.applyAll(true);
            },

            clearFilter(key) {
                if (this.serverFilters) {
                    const next = { ...this.activeFilters };
                    delete next[key];
                    this.navigateWithFilters(next);
                    return;
                }
                delete this.activeFilters[key];
                this.applyAll(true);
            },

            clearAllFilters() {
                if (this.serverFilters) {
                    this.navigateWithFilters({});
                    return;
                }
                this.activeFilters = {};
                this.applyAll(true);
            },

            hasActiveFilters() {
                return Object.keys(this.activeFilters).length > 0;
            },

            applyAll(resetPage = true) {
                let result = [...this.originalData];

                // Client search/filter only when not in server mode
                if (!this.serverFilters && this.searchTerm.trim()) {
                    const term = this.searchTerm.toLowerCase();

                    result = result.filter(item =>
                        this.columns.some(column => {
                            const value = this.getNestedValue(item, column.key);
                            return value !== null &&
                                value !== undefined &&
                                value.toString().toLowerCase().includes(term);
                        })
                    );
                }

                if (!this.serverFilters) {
                    Object.entries(this.activeFilters).forEach(([key, value]) => {
                        result = result.filter(item => {
                            const val = this.getNestedValue(item, key);
                            return val !== null && val !== undefined && String(val) === String(value);
                        });
                    });
                }

                if (this.sortColumn) {
                    result.sort((a, b) => {
                        const aVal = this.getNestedValue(a, this.sortColumn);
                        const bVal = this.getNestedValue(b, this.sortColumn);

                        if (aVal == null && bVal == null) return 0;
                        if (aVal == null) return this.sortDirection === 'asc' ? -1 : 1;
                        if (bVal == null) return this.sortDirection === 'asc' ? 1 : -1;

                        if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                        if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                        return 0;
                    });
                }

                this.filteredData = result;

                if (resetPage) {
                    this.currentPage = 1;
                }

                if (this.currentPage > this.totalPages) {
                    this.currentPage = this.totalPages;
                }

                if (this.currentPage < 1) {
                    this.currentPage = 1;
                }

                this.updatePaginatedData();
                this.saveState();
            },

            search() {
                this.applyAll(true);
            },

            sort(column) {
                if (this.sortColumn === column) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortColumn = column;
                    this.sortDirection = 'asc';
                }

                this.applyAll(false);
            },

            goToPage(page) {
                if (page >= 1 && page <= this.totalPages) {
                    this.currentPage = page;
                    this.updatePaginatedData();
                    this.saveState();
                }
            },

            updatePaginatedData() {
                if (this.pagination) {
                    const start = (this.currentPage - 1) * this.perPage;
                    this.paginatedData = this.filteredData.slice(start, start + this.perPage);
                } else {
                    this.paginatedData = this.filteredData;
                }
            },

            isActionDisabled(action, item) {
                if (!action.disableWhen) return false;

                const fieldValue = this.getNestedValue(item, action.disableWhen.field);
                return String(fieldValue) === String(action.disableWhen.value);
            },
            buildActionRoute(action, item) {
                if (!action.route) return '#';

                let url = action.route;
                const idField = action.idField || 'id';

                url = url.replace(':id', this.getNestedValue(item, idField));

                if (action.extraFields) {
                    Object.entries(action.extraFields).forEach(([paramName, fieldPath]) => {
                        url = url.replace(`:${paramName}`, this.getNestedValue(item, fieldPath));
                    });
                }

                return url;
            },

            getNestedValue(obj, path) {
                return path.split('.').reduce((current, key) => current && current[key], obj);
            }
        }
    }
</script>
