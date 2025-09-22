<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('User Details: :name', ['name' => $user->name]) }}
            </h2>
            <div class="flex space-x-2">
                @php
                    $canManage = auth()->user()->role === 'SuperAdmin' ||
                        (auth()->user()->role === 'Admin' && !in_array($user->role, ['Admin', 'SuperAdmin'])) ||
                        (auth()->user()->role === 'Manager' && $user->role === 'User');
                @endphp
                @if($canManage)
                    <livewire:admin.user-edit-modal :userId="$user->id" />
                @endif
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Back to Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- User Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">User Information</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                    <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($user->role === 'SuperAdmin') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                        @elseif($user->role === 'Admin') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @elseif($user->role === 'Manager') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @endif">
                                        {{ $user->role }}
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Verified</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        @if($user->email_verified_at)
                                            <span class="text-green-600 dark:text-green-400">Verified on {{ $user->email_verified_at->format('M j, Y g:i A') }}</span>
                                        @else
                                            <span class="text-red-600 dark:text-red-400">Not verified</span>
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Member Since</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->created_at->format('M j, Y g:i A') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Updated</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->updated_at->format('M j, Y g:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Statistics -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Activity Statistics</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Scanned Receipts</label>
                                    <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        {{ $user->scannedReceipts()->count() }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total VAT Collected</label>
                                    <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">
                                        {{ number_format($user->scannedReceipts()->sum('total_vat_amount'), 2) }} ALL
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Receipt Scanned</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        @if($user->scannedReceipts()->exists())
                                            {{ $user->scannedReceipts()->latest('scanned_at')->first()->scanned_at->format('M j, Y g:i A') }}
                                        @else
                                            Never
                                        @endif
                                    </p>
                                </div>

                                @if($user->scannedReceipts()->exists())
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recent Activity</label>
                                        <div class="mt-2 space-y-2">
                                            @foreach($user->scannedReceipts()->latest('scanned_at')->limit(3)->get() as $receipt)
                                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                                    <span class="font-medium">{{ number_format($receipt->total_vat_amount, 2) }} ALL</span>
                                                    from {{ $receipt->seller->name ?? 'Unknown Seller' }}
                                                    <span class="text-xs">{{ $receipt->scanned_at->diffForHumans() }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>