<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>HRMS API | Restricted Access</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    @endif

    <style>
        :root {
            --brand-primary: oklch(0.3803 0.1386 258.03);
            --brand-dark: #ffffff;
            --glass-bg: rgba(0, 0, 0, 0.03);
            --glass-border: rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--brand-dark);
            color: #0a0a0a;
            overflow: hidden;
        }

        .mesh-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background:
                radial-gradient(circle at 20% 30%, oklch(0.3803 0.1386 258.03 / 0.15) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, oklch(0.3803 0.1386 258.03 / 0.1) 0%, transparent 40%);
            filter: blur(80px);
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .glass-card:hover {
            border-color: oklch(0.3803 0.1386 258.03 / 0.3);
        }

        .btn-glow {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .btn-glow:hover {
            box-shadow: 0 0 20px oklch(0.3803 0.1386 258.03 / 0.4);
            transform: translateY(-2px);
        }

        .restricted-badge {
            background: oklch(0.3803 0.1386 258.03 / 0.1);
            color: var(--brand-primary);
            border: 1px solid oklch(0.3803 0.1386 258.03 / 0.2);
        }

        .text-gradient-primary {
            background: linear-gradient(to right, var(--brand-primary), oklch(0.5 0.13 258));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        .bg-gradient-primary {
            background: linear-gradient(to right, var(--brand-primary), oklch(0.5 0.13 258));
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .shimmer {
            position: relative;
            overflow: hidden;
        }

        .shimmer::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(to bottom right,
                    rgba(255, 255, 255, 0) 0%,
                    rgba(255, 255, 255, 0) 40%,
                    rgba(255, 255, 255, 0.1) 50%,
                    rgba(255, 255, 255, 0) 60%,
                    rgba(255, 255, 255, 0) 100%);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) rotate(45deg);
            }
        }
    </style>
</head>

<body class="antialiased selection:bg-[oklch(0.3803_0.1386_258.03)] selection:text-white">
    <div class="mesh-background"></div>

    <div class="relative flex flex-col items-center justify-center min-h-screen p-6 overflow-hidden">

        <!-- Floating Decorative Elements -->
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-[oklch(0.3803_0.1386_258.03)]/10 rounded-full blur-3xl animate-float"
            style="animation-delay: 0s;"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-[oklch(0.3803_0.1386_258.03)]/10 rounded-full blur-3xl animate-float"
            style="animation-delay: -2s;"></div>

        <main class="relative z-10 w-full max-w-2xl text-center">
            <!-- Branding -->
            <div class="mb-8 flex justify-center">
                <div class="p-3 rounded-2xl bg-black/5 border border-black/10 backdrop-blur-md">
                    <svg class="w-12 h-12 text-[oklch(0.3803_0.1386_258.03)]" viewBox="0 0 438 104" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.2036 -3H0V102.197H49.5189V86.7187H17.2036V-3Z" fill="currentColor" />
                        <path
                            d="M110.256 41.6337C108.061 38.1275 104.945 35.3731 100.905 33.3681C96.8667 31.3647 92.8016 30.3618 88.7131 30.3618C83.4247 30.3618 78.5885 31.3389 74.201 33.2923C69.8111 35.2456 66.0474 37.928 62.9059 41.3333C59.7643 44.7401 57.3198 48.6726 55.5754 53.1293C53.8287 57.589 52.9572 62.274 52.9572 67.1813C52.9572 72.1925 53.8287 76.8995 55.5754 81.3069C57.3191 85.7173 59.7636 89.6241 62.9059 93.0293C66.0474 96.4361 69.8119 99.1155 74.201 101.069C78.5885 103.022 83.4247 103.999 88.7131 103.999C92.8016 103.999 96.8667 102.997 100.905 100.994C104.945 98.9911 108.061 96.2359 110.256 92.7282V102.195H126.563V32.1642H110.256V41.6337ZM108.76 75.7472C107.762 78.4531 106.366 80.8078 104.572 82.8112C102.776 84.8161 100.606 86.4183 98.0637 87.6206C95.5202 88.823 92.7004 89.4238 89.6103 89.4238C86.5178 89.4238 83.7252 88.823 81.2324 87.6206C78.7388 86.4183 76.5949 84.8161 74.7998 82.8112C73.004 80.8078 71.6319 78.4531 70.6856 75.7472C69.7356 73.0421 69.2644 70.1868 69.2644 67.1821C69.2644 64.1758 69.7356 61.3205 70.6856 58.6154C71.6319 55.9102 73.004 53.5571 74.7998 51.5522C76.5949 49.5495 78.738 47.9451 81.2324 46.7427C83.7252 45.5404 86.5178 44.9396 89.6103 44.9396C92.7012 44.9396 95.5202 45.5404 98.0637 46.7427C100.606 47.9451 102.776 49.5487 104.572 51.5522C106.367 53.5571 107.762 55.9102 108.76 58.6154C109.756 61.3205 110.256 64.1758 110.256 67.1821C110.256 70.1868 109.756 73.0421 108.76 75.7472Z"
                            fill="currentColor" />
                    </svg>
                </div>
            </div>

            <!-- Main Card -->
            <div class="glass-card rounded-3xl p-10 lg:p-16 mb-8 text-center border border-black/10">
                <div
                    class="inline-flex items-center px-4 py-1.5 rounded-full restricted-badge text-xs font-semibold uppercase tracking-widest mb-6">
                    <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                    Restricted Endpoint
                </div>

                <h1 class="text-4xl lg:text-5xl font-bold mb-6 tracking-tight leading-tight">
                    HRMS <br />
                    <span class="text-gradient-primary">API
                        Gateway</span>
                </h1>

                {{-- <p class="text-gray-400 text-lg mb-10 leading-relaxed max-w-md mx-auto">
                    This system is strictly for authorized personnel. Access to these resources requires valid
                    cryptographic credentials and special permissions.
                </p> --}}

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/docs/api"
                        class="btn-glow shimmer w-full sm:w-auto px-8 py-4 bg-gradient-primary rounded-xl text-white font-bold text-lg inline-flex items-center justify-center">
                        Explore API Docs
                        <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>

                    <div class="text-gray-500 text-sm font-medium px-4">
                        IP Logged: <span class="text-gray-400">{{ request()->ip() }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <footer class="text-gray-500 text-sm flex flex-col items-center gap-4">
                <p>© {{ date('Y') }} HRMS Engineering. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-[oklch(0.3803_0.1386_258.03)] transition-colors">Privacy Policy</a>
                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                    <a href="#" class="hover:text-[oklch(0.3803_0.1386_258.03)] transition-colors">Security Audit</a>
                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                    <a href="#" class="hover:text-[oklch(0.3803_0.1386_258.03)] transition-colors">System Status</a>
                </div>
                <div
                    class="mt-4 px-3 py-1 rounded-lg bg-black/5 border border-black/5 text-[10px] uppercase tracking-widest text-gray-500">
                    Version {{ app()->version() }} Build {{ date('Ymd') }}
                </div>
            </footer>
        </main>
    </div>
</body>

</html>