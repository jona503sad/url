<?php
// index.php

// Si se envía el formulario desde el Home, guardamos la URL temporalmente y mandamos al login
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['url_to_shorten'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Guardamos la intención del usuario para procesarla después del login
    $_SESSION['pending_url'] = $_POST['url_to_shorten'];
    header("Location: login.php");
    exit;
}

// Incluir la cabecera modular
require_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center animate-fade-in-up">
    <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-gray-900 tracking-tight mb-6 leading-tight">
        Acorta tus enlaces, <br>
        <span class="text-blue-600">expande tu alcance</span>
    </h1>
    <p class="text-lg text-gray-500 mb-10 max-w-2xl mx-auto">
        Crea enlaces únicos, permanentes o temporales, protegidos con contraseña y gestiona tus estadísticas. Todo en una sola plataforma profesional.
    </p>

    <div class="bg-white p-2 rounded-full shadow-xl border border-gray-100 max-w-3xl mx-auto flex items-center transform transition hover:scale-[1.01]">
        <form method="POST" action="index.php" class="w-full flex items-center">
            <div class="pl-5 text-gray-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
            </div>
            <input type="url" name="url_to_shorten" required placeholder="Pega tu enlace largo aquí..." class="w-full py-4 px-4 outline-none text-gray-700 bg-transparent text-lg">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-full transition shadow-md whitespace-nowrap flex items-center gap-2">
                <span>Generar Link</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>
    </div>
    
    <div class="mt-8 text-sm text-gray-400">
        Únete a administradores, colaboradores y usuarios pro.
    </div>
</div>

<?php 
// Incluir el pie de página modular
require_once 'includes/footer.php'; 
?>