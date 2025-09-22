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
                            <div class="relative">
                                <div x-ref="scanner" id="scanner-container" class="mx-auto max-w-full rounded-lg shadow-lg" style="max-width: 400px; min-height: 300px;"></div>

                                <!-- Loading overlay -->
                                <div x-show="cameraLoading" class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-lg">
                                    <div class="text-center">
                                        <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Starting camera...</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button @click="stopCamera()" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Stop Camera
                                </button>
                            </div>

                            <p x-show="!cameraLoading && !cameraError" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Point your camera at the QR code on your receipt</p>

                            <div x-show="cameraError" class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <p class="text-sm text-red-600 dark:text-red-400" x-text="cameraError"></p>
                            </div>
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
                            <li>• Camera requires HTTPS connection and permission to access camera</li>
                            <li>• On mobile: ensure you're using Chrome, Safari, or Firefox for best camera support</li>
                        </ul>
                    </div>

                    <!-- HTTPS Warning -->
                    @if(!request()->secure() && !app()->environment('local'))
                    <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">HTTPS Required</h4>
                                <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                                    Camera access requires a secure HTTPS connection. If the camera isn't working, please use the manual entry option or contact support.
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
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
                cameraError: '',
                cameraLoading: false,

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
                    this.cameraError = '';
                    this.cameraLoading = true;

                    this.$nextTick(() => {
                        this.initializeScanner();
                    });
                },

                stopCamera() {
                    this.showCamera = false;
                    this.cleanupScanner();
                },

                async cleanupScanner() {
                    if (this.html5QrcodeScanner) {
                        try {
                            await this.html5QrcodeScanner.stop();
                            this.html5QrcodeScanner.clear();
                        } catch (error) {
                            console.log('Error cleaning up scanner:', error);
                        }
                        this.html5QrcodeScanner = null;
                    }
                },

                async initializeScanner() {
                    if (typeof Html5Qrcode !== 'undefined') {
                        try {
                            // Get available cameras
                            const devices = await Html5Qrcode.getCameras();

                            if (devices && devices.length > 0) {
                                // Try to find rear camera first, fallback to any camera
                                let cameraId = devices[0].id;

                                // Look for rear/back camera
                                const rearCamera = devices.find(device =>
                                    device.label.toLowerCase().includes('back') ||
                                    device.label.toLowerCase().includes('rear') ||
                                    device.label.toLowerCase().includes('environment')
                                );

                                if (rearCamera) {
                                    cameraId = rearCamera.id;
                                }

                                // Initialize Html5Qrcode
                                this.html5QrcodeScanner = new Html5Qrcode("scanner-container");

                                // Start scanning with the selected camera
                                await this.html5QrcodeScanner.start(
                                    cameraId,
                                    {
                                        fps: 10,
                                        qrbox: { width: 250, height: 250 },
                                        aspectRatio: 1.0
                                    },
                                    (decodedText) => this.onScanSuccess(decodedText),
                                    (error) => this.onScanFailure(error)
                                );

                                console.log('Camera started successfully with:', rearCamera ? rearCamera.label : devices[0].label);
                                this.cameraLoading = false;

                            } else {
                                this.cameraError = 'No cameras found on this device.';
                                this.cameraLoading = false;
                            }
                        } catch (error) {
                            console.error('Failed to initialize scanner:', error);
                            if (error.name === 'NotAllowedError') {
                                this.cameraError = 'Camera permission denied. Please allow camera access and try again.';
                            } else if (error.name === 'NotFoundError') {
                                this.cameraError = 'No camera found on this device.';
                            } else if (error.name === 'NotSupportedError') {
                                this.cameraError = 'Camera not supported by this browser. Try using Chrome or Safari.';
                            } else {
                                this.cameraError = 'Failed to access camera: ' + error.message;
                            }
                            this.cameraLoading = false;
                        }
                    } else {
                        console.error('Html5Qrcode not loaded');
                        this.cameraError = 'QR scanner library failed to load. Please refresh the page and try again.';
                        this.cameraLoading = false;
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
                    // Handle scan failure - only show critical errors
                    if (error.includes('Permission denied') || error.includes('NotAllowedError')) {
                        this.cameraError = 'Camera permission denied. Please allow camera access and try again.';
                    } else if (error.includes('NotFoundError') || error.includes('DevicesNotFoundError')) {
                        this.cameraError = 'No camera found. Please check that your device has a camera.';
                    } else if (error.includes('NotSupportedError')) {
                        this.cameraError = 'Camera not supported by this browser. Try using Chrome or Safari.';
                    }
                    // Don't log common scanning errors like "QR code not found"
                    if (!error.includes('QR code parse error') && !error.includes('No MultiFormat Readers')) {
                        console.log(`QR Code scan error: ${error}`);
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
