<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('User Details: :name', ['name' => $detailUser->name]) }}
            </h2>
            <div class="flex space-x-2">
                @php
                    $canManage = auth()->user()->can('manage-user', $detailUser);
                @endphp
                @if($canManage)
                    <button wire:click="openEditModalForDetailUser"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Edit User') }}
                    </button>
                @endif
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Back to Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            <div class="mb-6">
                <!-- Flash Messages removed - handled in layout -->
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- User Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">User Information</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $detailUser->name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $detailUser->email }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                    <x-role-badge :role="$detailUser->role" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Verified</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        @if($detailUser->email_verified_at)
                                            <span class="text-green-600 dark:text-green-400">Verified on {{ $detailUser->email_verified_at->format('M j, Y g:i A') }}</span>
                                        @else
                                            <span class="text-red-600 dark:text-red-400">Not verified</span>
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Member Since</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $detailUser->created_at->format('M j, Y g:i A') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Updated</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $detailUser->updated_at->format('M j, Y g:i A') }}</p>
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
                                        {{ $detailUser->scannedReceipts()->count() }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total VAT Collected</label>
                                    <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">
                                        {{ number_format($detailUser->scannedReceipts()->sum('total_vat_amount'), 2) }} ALL
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Receipt Scanned</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        @if($detailUser->scannedReceipts()->exists())
                                            {{ $detailUser->scannedReceipts()->latest('scanned_at')->first()->scanned_at->format('M j, Y g:i A') }}
                                        @else
                                            Never
                                        @endif
                                    </p>
                                </div>

                                @if($detailUser->scannedReceipts()->exists())
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recent Activity</label>
                                        <div class="mt-2 space-y-2">
                                            @foreach($detailUser->scannedReceipts()->latest('scanned_at')->limit(3)->get() as $receipt)
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

    <!-- Edit User Modal -->
    @if($showEditModal)
    <div class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
        <div class="fixed inset-0 transform transition-all" wire:click="closeModal">
            <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
        </div>

        <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-2xl sm:mx-auto">

            <div class="px-6 py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit User</h3>
            </div>

            <form wire:submit="updateUser" class="p-6">
                <div class="grid grid-cols-1 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name</label>
                        <input wire:model="name" type="text" id="edit_name"
                               class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        @error('name') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="edit_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input wire:model="email" type="email" id="edit_email"
                               class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        @error('email') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="edit_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                        <select wire:model="role" id="edit_role"
                                class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            @foreach($this->availableRoles as $availableRole)
                                <option value="{{ $availableRole }}">{{ $availableRole }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="edit_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                        <input wire:model="password" type="password" id="edit_password"
                               class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Leave blank to keep current password</p>
                        @error('password') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="edit_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                        <input wire:model="password_confirmation" type="password" id="edit_password_confirmation"
                               class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        @error('password_confirmation') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6 space-x-3">
                    <button type="button" wire:click="closeModal"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>