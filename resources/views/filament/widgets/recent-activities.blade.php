<div>
    <div class="p-6 bg-white rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">آخر العمليات</h3>
        
        <div class="space-y-4">
            @php
                $activities = collect($this->getViewData()['recentContracts'])
                    ->merge($this->getViewData()['recentPayments'])
                    ->merge($this->getViewData()['recentTenants'])
                    ->sortByDesc('date')
                    ->take(10);
            @endphp

            @foreach($activities as $activity)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3 rtl:space-x-reverse">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ 
                                match($activity['color']) {
                                    'success' => 'bg-green-100 text-green-600',
                                    'danger' => 'bg-red-100 text-red-600',
                                    'warning' => 'bg-yellow-100 text-yellow-600',
                                    'info' => 'bg-blue-100 text-blue-600',
                                    default => 'bg-gray-100 text-gray-600'
                                }
                            }}">
                                <x-heroicon-o-document-text class="w-4 h-4" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $activity['description'] }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $activity['date']->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-end">
                        @if($activity['amount'])
                            <span class="text-sm font-semibold text-gray-900">
                                {{ number_format($activity['amount'], 2) }} ر.س
                            </span>
                        @endif
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ 
                            match($activity['color']) {
                                'success' => 'bg-green-100 text-green-800',
                                'danger' => 'bg-red-100 text-red-800',
                                'warning' => 'bg-yellow-100 text-yellow-800',
                                'info' => 'bg-blue-100 text-blue-800',
                                default => 'bg-gray-100 text-gray-800'
                            }
                        }}">
                            {{ 
                                match($activity['status']) {
                                    'active' => 'نشط',
                                    'paid' => 'مدفوع',
                                    'pending' => 'معلق',
                                    'overdue' => 'متأخر',
                                    'expired' => 'منتهي',
                                    default => $activity['status']
                                }
                            }}
                        </span>
                    </div>
                </div>
            @endforeach

            @if($activities->isEmpty())
                <div class="text-center py-8">
                    <x-heroicon-o-clock class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">لا توجد عمليات حديثة</h3>
                    <p class="mt-1 text-sm text-gray-500">ستظهر آخر العمليات هنا عند إضافة بيانات جديدة.</p>
                </div>
            @endif
        </div>
    </div>
</div>
