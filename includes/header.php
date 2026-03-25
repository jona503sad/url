<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acortador Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Animación de entrada suave */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.8s ease-out forwards; }
        
        /* Evitar scroll si el fondo animado es más grande */
        body { overflow-x: hidden; }
    </style>
</head>
<body class="bg-gray-50 flex flex-col h-full font-sans text-gray-800"> <nav class="bg-white/90 backdrop-blur-sm shadow-sm border-b border-gray-100 fixed top-0 w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index" class="flex items-center gap-2">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        <span class="font-bold text-xl text-gray-900 tracking-tight">Acortador<span class="text-blue-600">Pro</span></span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="login" class="text-gray-500 hover:text-gray-900 text-sm font-medium transition">Iniciar Sesión</a>
                    <a href="register" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-full text-sm font-medium transition shadow-sm">Registrarse</a>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="flex-grow flex flex-col justify-center pt-16 h-full relative z-0">