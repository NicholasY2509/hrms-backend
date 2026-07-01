<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Human Resource Management System | API Gateway</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    @endif

    <style>
        :root {
            --bg-base: #050505;
            --brand-primary: #3b82f6;
            --brand-secondary: #8b5cf6;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-base);
            color: #ffffff;
            overflow-x: hidden;
            margin: 0;
            min-height: 100vh;
        }

        .mesh-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            background-color: var(--bg-base);
            background-image:
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(139, 92, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            filter: blur(60px);
        }

        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.5);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.4s ease;
        }

        .glass-panel:hover {
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-4px);
        }

        .text-gradient {
            background: linear-gradient(135deg, #60a5fa, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        .btn-primary {
            position: relative;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.8), rgba(139, 92, 246, 0.8));
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover {
            box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.4);
            transform: translateY(-2px);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .delay-100 {
            animation-delay: 100ms;
        }

        .delay-200 {
            animation-delay: 200ms;
        }

        .delay-300 {
            animation-delay: 300ms;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .grid-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            z-index: -1;
            mask-image: radial-gradient(circle at center, black, transparent 80%);
            -webkit-mask-image: radial-gradient(circle at center, black, transparent 80%);
        }
    </style>
</head>

<body class="antialiased selection:bg-blue-500/30 selection:text-white flex flex-col min-h-screen">
    <div class="mesh-bg"></div>
    <div class="grid-pattern"></div>

    <div class="flex-grow flex flex-col items-center justify-center p-6 relative">
        <main class="w-full max-w-3xl text-center z-10">
            <!-- Icon -->
            <div class="mb-10 flex justify-center animate-fade-in-up">
                <div class="p-4 rounded-2xl bg-white/5 border border-white/10 shadow-lg backdrop-blur-xl">
                    <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                        </path>
                    </svg>
                </div>
            </div>

            <!-- Card -->
            <div
                class="glass-panel rounded-3xl p-10 md:p-14 mb-10 text-center animate-fade-in-up delay-100 relative overflow-hidden">
                <!-- Subtle glow inside card -->
                <div
                    class="absolute top-0 left-1/2 -translate-x-1/2 w-[200%] h-32 bg-gradient-to-b from-blue-500/10 to-transparent blur-2xl pointer-events-none">
                </div>

                <div
                    class="inline-flex items-center px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-xs font-semibold uppercase tracking-widest text-gray-300 mb-8 backdrop-blur-md">
                    <span class="w-2 h-2 rounded-full bg-green-400 mr-2 animate-pulse"></span>
                    API Gateway Active
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 tracking-tight leading-tight text-white">
                    Human Resource <br /> Management System
                </h1>

                <h2 class="text-2xl md:text-3xl font-medium mb-10 text-gray-400">
                    <span class="text-gradient font-bold">API Gateway</span>
                </h2>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-5 mt-4 relative z-10">
                    <a href="/docs/api"
                        class="btn-primary group w-full sm:w-auto px-8 py-4 rounded-xl text-white font-semibold text-lg inline-flex items-center justify-center shadow-lg">
                        Explore Documentation
                        <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="animate-fade-in-up delay-200">
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-black/40 border border-white/5 text-sm text-gray-400 backdrop-blur-sm">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9">
                        </path>
                    </svg>
                    IP Logged: <span class="font-mono text-gray-300">{{ request()->ip() }}</span>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer
        class="w-full p-6 text-gray-500 text-sm flex flex-col md:flex-row items-center justify-between z-10 bg-black/20 backdrop-blur-md border-t border-white/5 animate-fade-in-up delay-300">
        <p class="mb-4 md:mb-0">© {{ date('Y') }} Human Resource Management System. All rights reserved.</p>
        <div class="flex items-center gap-4">
            <a href="#" class="hover:text-blue-400 transition-colors">Privacy Policy</a>
            <span class="w-1 h-1 bg-gray-600 rounded-full"></span>
            <a href="#" class="hover:text-blue-400 transition-colors">Security Audit</a>
            <span class="w-1 h-1 bg-gray-600 rounded-full"></span>
            <span
                class="px-2 py-1 rounded bg-white/5 text-[10px] uppercase tracking-wider border border-white/10">v{{ app()->version() }}</span>
        </div>
    </footer>
</body>

</html>