@props(['show' => false, 'receipt' => null])

<div x-data="{ show: false }"
     x-show="show"
     x-on:open-delete-modal.window="show = true; setTimeout(() => $refs.confirmButton?.focus(), 100)"
     x-on:keydown.escape.window="if (show) { show = false }"
     style="display: none;"
     class="fixed inset-0 z-50 overflow-y-auto">

    <!-- Background overlay -->
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 opacity-75" x-show="show" x-on:click="show = false"></div>

        <!-- Center the modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
             x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            <!-- Modal Content -->
            <div class="px-4 pt-5 pb-4 sm:p-6">
                <!-- Header with Icon and Title -->
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100 dark:bg-red-900/20">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">
                        Delete Receipt Permanently?
                    </h3>
                </div>

                @if($receipt)
                    <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            <div class="font-medium text-gray-900 dark:text-gray-100">Receipt #{{ $receipt->invoice_order_number }}</div>
                            <div class="mt-1">{{ $receipt->seller->name }}</div>
                            <div>{{ $receipt->receipt_created_at->format('F j, Y') }}</div>
                            <div class="font-medium text-green-600 dark:text-green-400">VAT: {{ number_format($receipt->total_vat_amount, 2) }} ALL</div>
                        </div>
                    </div>
                @endif

                <!-- Warning Section -->
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h4 class="text-sm font-semibold text-red-800 dark:text-red-400 mb-2">
                                ⚠️ PERMANENT DELETION WARNING
                            </h4>
                            <div class="text-sm text-red-700 dark:text-red-300">
                                <p class="font-medium mb-2">This action cannot be undone. Once deleted:</p>
                                <ul class="list-disc ml-4 space-y-1">
                                    <li>The receipt data will be permanently removed from our system</li>
                                    <li>You will lose the VAT refund amount associated with this receipt</li>
                                    <li><strong>If the receipt date has passed, you may never be able to scan this receipt again</strong></li>
                                    <li>This could affect your total VAT recovery calculations</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Warning -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                <strong>Consider keeping this receipt:</strong> If you're unsure about deleting, you can always hide or archive receipts instead of permanently removing them.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button"
                        x-ref="confirmButton"
                        class="w-full inline-flex justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                        x-on:click="show = false; $wire.deleteReceipt()">
                    Yes, Delete Permanently
                </button>
                <button type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md bg-white dark:bg-gray-600 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-500 hover:bg-gray-50 dark:hover:bg-gray-500 sm:mt-0 sm:w-auto"
                        x-on:click="show = false">
                    Cancel & Keep Receipt
                </button>
            </div>
        </div>
    </div>
</div>