<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'VAT Receipt Scanner') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 text-white">
        <div class="min-h-screen relative overflow-hidden">
            <!-- Animated Background Grid -->
            <div class="absolute inset-0 opacity-20">
                <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25px 25px, rgba(255,255,255,0.1) 2px, transparent 0), radial-gradient(circle at 75px 75px, rgba(59,130,246,0.1) 1px, transparent 0); background-size: 100px 100px; animation: grid-move 20s linear infinite;">
                </div>
            </div>

            <!-- Floating Geometric Shapes -->
            <div class="absolute top-1/4 left-1/4 w-32 h-32 bg-blue-500/10 rounded-full blur-xl animate-pulse"></div>
            <div class="absolute top-3/4 right-1/4 w-24 h-24 bg-purple-500/10 rounded-lg blur-lg animate-bounce" style="animation-delay: 2s;"></div>
            <div class="absolute bottom-1/4 left-1/3 w-16 h-16 bg-cyan-500/10 rounded-full blur-lg animate-ping" style="animation-delay: 4s;"></div>

            <div class="relative z-10">
                <!-- Header -->
                <header class="flex justify-between items-center p-6 lg:px-12">
                    <div class="flex items-center space-x-3">
                        <svg class="h-10 w-auto text-white" viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M61.8548 14.6253C61.8778 14.7102 61.8895 14.7978 61.8897 14.8858V28.5615C61.8898 28.737 61.8434 28.9095 61.7554 29.0614C61.6675 29.2132 61.5409 29.3392 61.3887 29.4265L49.9104 36.0351V49.1337C49.9104 49.4902 49.7209 49.8192 49.4118 49.9987L25.4519 63.7916C25.3971 63.8227 25.3372 63.8427 25.2774 63.8639C25.255 63.8714 25.2338 63.8851 25.2101 63.8913C25.0426 63.9354 24.8666 63.9354 24.6991 63.8913C24.6716 63.8838 24.6467 63.8689 24.6205 63.8589C24.5657 63.8389 24.5084 63.8215 24.456 63.7916L0.501061 49.9987C0.348882 49.9113 0.222437 49.7853 0.134469 49.6334C0.0465019 49.4816 0.000120578 49.3092 0 49.1337L0 8.10652C0 8.01678 0.0124642 7.92953 0.0348998 7.84477C0.0423783 7.8161 0.0598282 7.78993 0.0697995 7.76126C0.0884958 7.70891 0.105946 7.65531 0.133367 7.6067C0.152063 7.5743 0.179485 7.54812 0.20192 7.51821C0.230588 7.47832 0.256763 7.43719 0.290416 7.40229C0.319084 7.37362 0.356476 7.35243 0.388883 7.32751C0.425029 7.29759 0.457436 7.26518 0.498568 7.2415L12.4779 0.345059C12.6296 0.257786 12.8015 0.211853 12.9765 0.211853C13.1515 0.211853 13.3234 0.257786 13.475 0.345059L25.4531 7.2415H25.4556C25.4955 7.26643 25.5292 7.29759 25.5653 7.32626C25.5977 7.35119 25.6339 7.37362 25.6625 7.40104C25.6974 7.43719 25.7224 7.47832 25.7523 7.51821C25.7735 7.54812 25.8021 7.5743 25.8196 7.6067C25.8483 7.65656 25.8645 7.70891 25.8844 7.76126C25.8944 7.78993 25.9118 7.8161 25.9193 7.84602C25.9423 7.93096 25.954 8.01853 25.9542 8.10652V33.7317L35.9355 27.9844V14.8846C35.9355 14.7973 35.948 14.7088 35.9704 14.6253C35.9792 14.5954 35.9954 14.5692 36.0053 14.5405C36.0253 14.4882 36.0427 14.4346 36.0702 14.386C36.0888 14.3536 36.1163 14.3274 36.1375 14.2975C36.1674 14.2576 36.1923 14.2165 36.2272 14.1816C36.2559 14.1529 36.292 14.1317 36.3244 14.1068C36.3618 14.0769 36.3942 14.0445 36.4341 14.0208L48.4147 7.12434C48.5663 7.03694 48.7383 6.99094 48.9133 6.99094C49.0883 6.99094 49.2602 7.03694 49.4118 7.12434L61.3899 14.0208C61.4323 14.0457 61.4647 14.0769 61.5021 14.1055C61.5333 14.1305 61.5694 14.1529 61.5981 14.1803C61.633 14.2165 61.6579 14.2576 61.6878 14.2975C61.7103 14.3274 61.7377 14.3536 61.7551 14.386C61.7838 14.4346 61.8 14.4882 61.8199 14.5405C61.8312 14.5692 61.8474 14.5954 61.8548 14.6253ZM59.893 27.9844V16.6121L55.7013 19.0252L49.9104 22.3593V33.7317L59.8942 27.9844H59.893ZM47.9149 48.5566V37.1768L42.2187 40.4299L25.953 49.7133V61.2003L47.9149 48.5566ZM1.99677 9.83281V48.5566L23.9562 61.199V49.7145L12.4841 43.2219L12.4804 43.2194L12.4754 43.2169C12.4368 43.1945 12.4044 43.1621 12.3682 43.1347C12.3371 43.1097 12.3009 43.0898 12.2735 43.0624L12.271 43.0586C12.2386 43.0275 12.2162 42.9888 12.1887 42.9539C12.1638 42.9203 12.1339 42.8916 12.114 42.8567L12.1127 42.853C12.0903 42.8156 12.0766 42.7707 12.0604 42.7283C12.0442 42.6909 12.023 42.656 12.013 42.6161C12.0005 42.5688 11.998 42.5177 11.9931 42.4691C11.9881 42.4317 11.9781 42.3943 11.9781 42.3569V15.5801L6.18848 12.2446L1.99677 9.83281ZM12.9777 2.36177L2.99764 8.10652L12.9752 13.8513L22.9541 8.10527L12.9752 2.36177H12.9777ZM18.1678 38.2138L23.9574 34.8809V9.83281L19.7657 12.2459L13.9749 15.5801V40.6281L18.1678 38.2138ZM48.9133 9.14105L38.9344 14.8858L48.9133 20.6305L58.8909 14.8846L48.9133 9.14105ZM47.9149 22.3593L42.124 19.0252L37.9323 16.6121V27.9844L43.7219 31.3174L47.9149 33.7317V22.3593ZM24.9533 47.987L39.59 39.631L46.9065 35.4555L36.9352 29.7145L25.4544 36.3242L14.9907 42.3482L24.9533 47.987Z" fill="currentColor"/></svg>
                        <span class="text-xl font-semibold">VAT Scanner</span>
                    </div>

                    @if (Route::has('login'))
                        <nav class="flex items-center space-x-4">
                            @auth
                                <a
                                    href="{{ url('/dashboard') }}"
                                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium transition-all duration-200 transform hover:scale-105"
                                >
                                    Dashboard
                                </a>
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    class="px-6 py-2.5 bg-transparent border border-white/20 hover:border-white/40 hover:bg-white/10 rounded-lg font-medium transition-all duration-200"
                                >
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a
                                        href="{{ route('register') }}"
                                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium transition-all duration-200 transform hover:scale-105"
                                    >
                                        Get Started
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </header>

                <!-- Main Content -->
                <main class="flex-1 flex items-center justify-center min-h-screen -mt-24 px-6">
                    <div class="max-w-6xl mx-auto text-center">
                        <!-- Hero Section -->
                        <div class="mb-16">
                            <div class="inline-flex items-center px-4 py-2 bg-blue-500/20 border border-blue-400/30 rounded-full text-blue-300 text-sm font-medium mb-8">
                                <span class="w-2 h-2 bg-blue-400 rounded-full mr-2 animate-pulse"></span>
                                AI-Powered VAT Processing
                            </div>

                            <h1 class="text-5xl lg:text-7xl font-bold mb-6 bg-gradient-to-r from-white via-blue-100 to-white bg-clip-text text-transparent leading-tight">
                                Smart Receipt
                                <span class="block bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">
                                    VAT Recovery
                                </span>
                            </h1>

                            <p class="text-xl lg:text-2xl text-gray-300 mb-12 leading-relaxed max-w-3xl mx-auto">
                                Scan your receipts with advanced AI technology and automatically calculate your VAT returns.
                                <span class="text-blue-300">Smart, fast, and secure.</span>
                            </p>

                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                @auth
                                    <a href="{{ route('receipts.scan') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-xl shadow-blue-500/25">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Scan Receipt Now
                                    </a>
                                @else
                                    <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-xl shadow-blue-500/25">
                                        Start Scanning
                                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                    </a>
                                @endauth

                                <a href="#features" class="inline-flex items-center px-8 py-4 bg-transparent border border-white/20 hover:border-white/40 hover:bg-white/10 rounded-xl font-semibold text-lg transition-all duration-200">
                                    Learn More
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Features Section -->
                        <div id="features" class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                            <div class="group p-8 rounded-2xl bg-gradient-to-br from-white/5 to-white/10 border border-white/10 hover:border-white/20 transition-all duration-300 hover:transform hover:scale-105 backdrop-blur-sm">
                                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mb-6 group-hover:rotate-3 transition-transform duration-300">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold mb-4">AI-Powered Scanning</h3>
                                <p class="text-gray-300 leading-relaxed">
                                    Advanced OCR technology extracts data from your receipts with 99%+ accuracy, handling any format or language.
                                </p>
                            </div>

                            <div class="group p-8 rounded-2xl bg-gradient-to-br from-white/5 to-white/10 border border-white/10 hover:border-white/20 transition-all duration-300 hover:transform hover:scale-105 backdrop-blur-sm">
                                <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center mb-6 group-hover:rotate-3 transition-transform duration-300">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold mb-4">Automatic VAT Calculation</h3>
                                <p class="text-gray-300 leading-relaxed">
                                    Intelligent algorithms automatically identify VAT rates and calculate your potential returns instantly.
                                </p>
                            </div>

                            <div class="group p-8 rounded-2xl bg-gradient-to-br from-white/5 to-white/10 border border-white/10 hover:border-white/20 transition-all duration-300 hover:transform hover:scale-105 backdrop-blur-sm">
                                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center mb-6 group-hover:rotate-3 transition-transform duration-300">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold mb-4">Secure & Compliant</h3>
                                <p class="text-gray-300 leading-relaxed">
                                    Enterprise-grade security with full GDPR compliance. Your data is encrypted and never shared.
                                </p>
                            </div>
                        </div>

                        <!-- Stats Section -->
                        <div class="mt-24 grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-400 mb-2">99.2%</div>
                                <div class="text-gray-400">Accuracy Rate</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-400 mb-2">< 3s</div>
                                <div class="text-gray-400">Processing Time</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-purple-400 mb-2">50+</div>
                                <div class="text-gray-400">Currencies</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-cyan-400 mb-2">24/7</div>
                                <div class="text-gray-400">Available</div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <style>
            @keyframes grid-move {
                0% { transform: translate(0, 0); }
                100% { transform: translate(100px, 100px); }
            }

            html {
                font-family: 'Inter', sans-serif;
                scroll-behavior: smooth;
            }
        </style>
    </body>
</html>
