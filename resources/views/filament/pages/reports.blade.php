<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">تقارير النظام العقاري</h1>
                    <p class="mt-1 text-sm text-gray-600">عرض شامل لأداء العقارات والإيرادات</p>
                </div>
                <div class="flex items-center space-x-4 rtl:space-x-reverse">
                    <span class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-blue-100 text-blue-800">
                        <x-heroicon-m-calendar class="w-4 h-4 mr-2" />
                        {{ now()->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Stats Section --}}
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">الإحصائيات العامة</h2>
            <x-filament-widgets::widgets
                :widgets="$this->getHeaderWidgets()"
                :columns="$this->getHeaderWidgetsColumns()"
            />
        </div>

        {{-- Charts Section --}}
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">الرسوم البيانية والتحليلات</h2>
            <x-filament-widgets::widgets
                :widgets="$this->getWidgets()"
                :columns="$this->getWidgetsColumns()"
            />
        </div>

        {{-- Export Section --}}
        <div class="bg-gray-50 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">تصدير التقارير</h3>
                    <p class="mt-1 text-sm text-gray-600">تصدير البيانات والتقارير بصيغ مختلفة</p>
                </div>
                <div class="flex space-x-3 rtl:space-x-reverse">
                    <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <x-heroicon-m-document-arrow-down class="w-4 h-4 mr-2" />
                        تصدير PDF
                    </button>
                    <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <x-heroicon-m-table-cells class="w-4 h-4 mr-2" />
                        تصدير Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
