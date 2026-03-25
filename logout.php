<?php
session_start();

// Destruir todas las variables de sesión para cerrar la cuenta de forma segura
$_SESSION = array();
session_destroy();

// Incluir el encabezado público
require_once 'includes/header.php';
?>

<style>
    .circles{
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: -1;
        pointer-events: none;
    }

    .circles li{
        position: absolute;
        display: block;
        list-style: none;
        width: 20px;
        height: 20px;
        background: rgba(37, 99, 235, 0.1); 
        animation: animate 25s linear infinite;
        bottom: -150px;
        border-radius: 50%;
    }

    .circles li:nth-child(1){ left: 25%; width: 80px; height: 80px; animation-delay: 0s; }
    .circles li:nth-child(2){ left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s; }
    .circles li:nth-child(3){ left: 70%; width: 20px; height: 20px; animation-delay: 4s; }
    .circles li:nth-child(4){ left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }
    .circles li:nth-child(5){ left: 65%; width: 20px; height: 20px; animation-delay: 0s; }
    .circles li:nth-child(6){ left: 75%; width: 110px; height: 110px; animation-delay: 3s; }
    .circles li:nth-child(7){ left: 35%; width: 150px; height: 150px; animation-delay: 7s; }
    .circles li:nth-child(8){ left: 50%; width: 25px; height: 25px; animation-delay: 15s; animation-duration: 45s; }
    .circles li:nth-child(9){ left: 20%; width: 15px; height: 15px; animation-delay: 2s; animation-duration: 35s; }
    .circles li:nth-child(10){ left: 85%; width: 150px; height: 150px; animation-delay: 0s; animation-duration: 11s; }

    @keyframes animate {
        0%{ transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 50%; }
        100%{ transform: translateY(-1000px) rotate(720deg); opacity: 0; border-radius: 10%; }
    }
</style>

<div class="flex items-center justify-center min-h-[calc(100vh-16rem)] px-4 animate-fade-in-up relative overflow-hidden py-12">
    
    <ul class="circles">
        <li></li><li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li><li></li>
    </ul>

    <div class="bg-white/95 backdrop-blur-sm p-10 rounded-3xl shadow-2xl w-full max-w-sm border border-gray-100 z-10 text-center relative">
        
        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-blue-50 mb-6 border-4 border-white shadow-sm">
            <svg class="h-10 w-10 text-blue-600 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
        </div>
        
        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-3">¡Hasta pronto!</h2>
        <p class="text-gray-500 mb-8 text-sm leading-relaxed">Lamentamos que te vayas. Tu sesión se ha cerrado de forma segura. Te esperamos de vuelta para seguir gestionando tus enlaces.</p>
        
        <div class="flex flex-col gap-4">
            <a href="login" class="w-full bg-gray-900 text-white font-bold py-3.5 px-4 rounded-xl hover:bg-gray-800 transition shadow-lg flex items-center justify-center gap-2">
                Volver al Login
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
            
            <p class="text-xs text-gray-400 font-medium">Serás redirigido automáticamente en <span id="countdown" class="font-bold text-blue-600 text-sm">4</span> segundos...</p>
        </div>

    </div>
</div>

<script>
    let timeLeft = 4;
    const countdownEl = document.getElementById('countdown');
    
    const timer = setInterval(() => {
        timeLeft--;
        countdownEl.textContent = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(timer);
            window.location.href = 'login'; // Redirección usando la URL limpia
        }
    }, 1000);
</script>

<?php require_once 'includes/footer.php'; ?>