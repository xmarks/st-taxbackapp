<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Scan Receipt') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="receiptScanner()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Scanning Options -->
                    <div class="mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- QR Scanner Option -->
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h4"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Camera QR Scanner</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Use your device camera to scan a QR code from your receipt</p>
                                <button @click="startCamera()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Start Camera
                                </button>
                            </div>

                            <!-- Manual Input Option -->
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Manual Entry</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Paste or type the receipt URL manually</p>
                                <button @click="showManualForm()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Manual Entry
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Camera Scanner Section -->
                    <div x-show="showCamera" x-transition class="mb-8">
                        <div class="text-center">
                            <video x-ref="scanner" class="mx-auto max-w-full rounded-lg shadow-lg" style="max-width: 400px; max-height: 300px;"></video>
                            <div class="mt-4">
                                <button @click="stopCamera()" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Stop Camera
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Point your camera at the QR code on your receipt</p>
                        </div>
                    </div>

                    <!-- Manual Form Section -->
                    <div x-show="showManual" x-transition class="mb-8">
                        <form method="POST" action="{{ route('receipts.process-scan') }}">
                            @csrf
                            <div class="mb-6">
                                <label for="qr_data" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Receipt URL or QR Data
                                </label>
                                <textarea
                                    x-ref="qrDataField"
                                    name="qr_data"
                                    rows="4"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    placeholder="Paste the complete URL from the QR code here (e.g., https://efiskalizimi-app.tatime.gov.al/invoice-check/#/verify?iic=...)"
                                    required>{{ old('qr_data') }}</textarea>
                                @error('qr_data')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between">
                                <button type="button" @click="hideManualForm()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                    Cancel
                                </button>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Process Receipt
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <h4 class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-2">Instructions:</h4>
                        <ul class="text-sm text-blue-800 dark:text-blue-300 space-y-1">
                            <li>• Receipt must be from Albania and scanned within {{ config('receipt.validity_hours', 24) }} hours of creation</li>
                            <li>• Each receipt can only be scanned once</li>
                            <li>• VAT amount will be calculated and added to your profile</li>
                            <li>• For desktop users: right-click QR code → "Copy link address" → paste here</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        function receiptScanner() {
            return {
                showManual: {{ $errors->has('qr_data') || old('qr_data') ? 'true' : 'false' }},
                showCamera: false,
                html5QrcodeScanner: null,

                showManualForm() {
                    this.showManual = true;
                    this.showCamera = false;
                    this.cleanupScanner();
                },

                hideManualForm() {
                    this.showManual = false;
                },

                startCamera() {
                    this.showCamera = true;
                    this.showManual = false;

                    this.$nextTick(() => {
                        this.initializeScanner();
                    });
                },

                stopCamera() {
                    this.showCamera = false;
                    this.cleanupScanner();
                },

                cleanupScanner() {
                    if (this.html5QrcodeScanner) {
                        this.html5QrcodeScanner.clear();
                        this.html5QrcodeScanner = null;
                    }
                },

                initializeScanner() {
                    if (typeof Html5QrcodeScanner !== 'undefined') {
                        this.html5QrcodeScanner = new Html5QrcodeScanner(
                            this.$refs.scanner,
                            {
                                fps: 10,
                                qrbox: { width: 250, height: 250 },
                                rememberLastUsedCamera: true
                            },
                            false
                        );

                        this.html5QrcodeScanner.render(
                            (decodedText) => this.onScanSuccess(decodedText),
                            (error) => this.onScanFailure(error)
                        );
                    } else {
                        console.error('Html5QrcodeScanner not loaded');
                    }
                },

                onScanSuccess(decodedText) {
                    // Clean up scanner
                    this.cleanupScanner();

                    // Fill the manual form with scanned data
                    this.$refs.qrDataField.value = decodedText;

                    // Show manual form and hide camera
                    this.showManual = true;
                    this.showCamera = false;

                    // Auto-submit after confirmation
                    setTimeout(() => {
                        if (confirm('Receipt QR code detected. Process this receipt?')) {
                            this.$refs.qrDataField.closest('form').submit();
                        }
                    }, 500);
                },

                onScanFailure(error) {
                    // Handle scan failure - usually just ignore
                    console.log(`QR Code scan error: ${error}`);
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
