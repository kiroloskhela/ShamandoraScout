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
])

<div class="bg-white shadow-lg rounded-lg overflow-hidden" x-data="dataTable({
    data: @js($data),
    columns: @js($columns),
    actions: @js($actions),
    searchable: @js($searchable),
    sortable: @js($sortable),
    pagination: @js($pagination),
    perPage: @js($perPage),
    title: @js($title),
    addButton: @js($addButton)
})">
    <!-- Header with Title, Search, and Add Button -->
    <div class="p-4 bg-gray-50 border-b">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <!-- Add Button (Right) -->
            <div class="order-1">
                <a x-show="addButton" :href="addButton ? addButton.route : '#'"
                    :class="addButton ? addButton.cssClass : ''"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                    x-text="addButton ? addButton.label : ''">
                </a>
            </div>

            <!-- Title (Center) -->
            <div class="order-2 flex-1 text-center">
                <h2 x-show="title" x-text="title" class="text-xl font-bold text-gray-900"></h2>
            </div>

            <!-- Search (Left) -->
            <div x-show="searchable" class="order-3">
                <div class="relative">
                    <input type="text" x-model="searchTerm" @input="search()" placeholder="البحث..."
                        class="w-64 pr-10 pl-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <template x-for="column in columns" :key="column.key">
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center justify-between">
                                <span x-text="column.label"></span>
                                <button x-show="sortable && column.sortable !== false" @click="sort(column.key)"
                                    class="mr-2 text-gray-400 hover:text-gray-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            :class="sortColumn === column.key ? (sortDirection === 'desc' ?
                                                'text-blue-500' : 'text-blue-500') : ''"
                                            :d="sortColumn === column.key ? (sortDirection === 'desc' ?
                                                'M19 9l-7 7-7-7' : 'M5 15l7-7 7 7') : 'M8 9l4-4 4 4m0 6l-4 4-4-4'">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </th>
                    </template>
                    <th x-show="actions.length > 0"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        الإجراءات
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="(item, index) in paginatedData" :key="index">
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <template x-for="column in columns" :key="column.key">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div x-show="column.type === 'text' || !column.type">
                                    <span x-text="getNestedValue(item, column.key)"
                                        :class="column.cssClass || 'text-sm text-gray-900'"></span>
                                </div>
                                <div x-show="column.type === 'label'">
                                    <label :class="column.cssClass || 'text-blue-600 font-bold text-sm'"
                                        x-text="getNestedValue(item, column.key)"></label>
                                </div>
                                <div x-show="column.type === 'badge'">
                                    <span
                                        :class="column.cssClass ||
                                            'px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800'"
                                        x-text="getNestedValue(item, column.key)"></span>
                                </div>
                            </td>
                        </template>
                        <td x-show="actions.length > 0"
                            class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-reverse space-x-2">
                            <template x-for="action in actions" :key="action.name">
                                <a :href="buildActionRoute(action, item)"
                                    :class="action.cssClass ||
                                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200'"
                                    x-text="action.label">
                                </a>
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    <div x-show="paginatedData.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            </path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">لا توجد بيانات</h3>
        <p class="mt-1 text-sm text-gray-500">لم يتم العثور على أي بيانات لعرضها.</p>
    </div>

    <!-- Pagination -->
    <div x-show="pagination && totalPages > 1"
        class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between items-center">
            <div class="text-sm text-gray-700">
                عرض
                <span class="font-medium" x-text="startRecord"></span>
                إلى
                <span class="font-medium" x-text="endRecord"></span>
                من
                <span class="font-medium" x-text="filteredData.length"></span>
                نتيجة
            </div>

            <div class="flex items-center space-x-reverse space-x-2">
                <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'"
                    class="relative inline-flex items-center px-2 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <template x-for="page in visiblePages" :key="page">
                    <button @click="goToPage(page)"
                        :class="page === currentPage ? 'bg-blue-50 border-blue-500 text-blue-600' :
                            'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md"
                        x-text="page">
                    </button>
                </template>

                <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                    :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'"
                    class="relative inline-flex items-center px-2 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function dataTable(options) {
        return {
            // Data
            originalData: options.data || [],
            filteredData: options.data || [],
            paginatedData: [],
            columns: options.columns || [],
            actions: options.actions || [],
            title: options.title || '',
            addButton: options.addButton || null,

            // Features
            searchable: options.searchable ?? true,
            sortable: options.sortable ?? true,
            pagination: options.pagination ?? true,
            perPage: options.perPage || 10,

            // State
            searchTerm: '',
            sortColumn: '',
            sortDirection: 'asc',
            currentPage: 1,

            // Computed
            get totalPages() {
                return Math.ceil(this.filteredData.length / this.perPage);
            },

            get startRecord() {
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
                    for (let i = 1; i <= total; i++) {
                        pages.push(i);
                    }
                } else {
                    if (current <= 4) {
                        for (let i = 1; i <= 5; i++) {
                            pages.push(i);
                        }
                        pages.push('...');
                        pages.push(total);
                    } else if (current >= total - 3) {
                        pages.push(1);
                        pages.push('...');
                        for (let i = total - 4; i <= total; i++) {
                            pages.push(i);
                        }
                    } else {
                        pages.push(1);
                        pages.push('...');
                        for (let i = current - 1; i <= current + 1; i++) {
                            pages.push(i);
                        }
                        pages.push('...');
                        pages.push(total);
                    }
                }

                return pages.filter(page => page !== '...');
            },

            // Methods
            init() {
                this.updatePaginatedData();
            },
            buildActionRoute(action, item) {
                if (!action.route) return '#';

                let url = action.route;

                // main id
                const idField = action.idField || 'id';
                url = url.replace(':id', this.getNestedValue(item, idField));

                // extra fields replacement (optional)
                // action.extraFields = { sana_id: 'SanaMarhalaID', qetaa_id: 'QetaaID', ... }
                if (action.extraFields) {
                    Object.entries(action.extraFields).forEach(([paramName, fieldPath]) => {
                        const value = this.getNestedValue(item, fieldPath);
                        url = url.replace(`:${paramName}`, value);
                    });
                }

                return url;
            },

            search() {
                if (!this.searchTerm.trim()) {
                    this.filteredData = [...this.originalData];
                } else {
                    const term = this.searchTerm.toLowerCase();
                    this.filteredData = this.originalData.filter(item => {
                        return this.columns.some(column => {
                            const value = this.getNestedValue(item, column.key);
                            return value && value.toString().toLowerCase().includes(term);
                        });
                    });
                }
                this.currentPage = 1;
                this.updatePaginatedData();
            },

            sort(column) {
                if (this.sortColumn === column) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortColumn = column;
                    this.sortDirection = 'asc';
                }

                this.filteredData.sort((a, b) => {
                    const aVal = this.getNestedValue(a, column);
                    const bVal = this.getNestedValue(b, column);

                    if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                    if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                    return 0;
                });

                this.updatePaginatedData();
            },

            goToPage(page) {
                if (page >= 1 && page <= this.totalPages) {
                    this.currentPage = page;
                    this.updatePaginatedData();
                }
            },

            updatePaginatedData() {
                if (this.pagination) {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    this.paginatedData = this.filteredData.slice(start, end);
                } else {
                    this.paginatedData = this.filteredData;
                }
            },

            getNestedValue(obj, path) {
                return path.split('.').reduce((current, key) => current && current[key], obj);
            }
        }
    }
</script>
