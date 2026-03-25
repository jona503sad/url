<?php
// includes/topbar.php
$role_display = ucfirst($_SESSION['user_role'] ?? 'Usuario');
$user_id = $_SESSION['user_id'] ?? 0;
?>

<div class="flex flex-col flex-1 w-full md:pl-64">
    
    <div class="bg-indigo-600 text-white text-xs font-medium px-4 py-2 text-center overflow-hidden relative">
        <div class="animate-marquee whitespace-nowrap">
            🚀 ¡Novedad! Ya está disponible la versión Pro con estadísticas avanzadas. Aprovecha el descuento de lanzamiento.
        </div>
    </div>

    <header class="h-16 bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 relative">
        
        <button class="md:hidden text-gray-500 hover:text-gray-700">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <div class="flex-1 px-4 flex justify-between">
            <div class="flex-1 flex items-center">
                <h1 class="text-xl font-semibold text-gray-800 hidden sm:block">Panel de Control</h1>
            </div>
            
            <div class="ml-4 flex items-center md:ml-6 space-x-4">
                
                <div class="relative">
                    <button id="btnNotificaciones" class="p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none transition relative">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        
                        <span id="badgeNotificacion" style="display: none;" class="absolute top-0 right-0 h-4 w-4 rounded-full bg-red-500 ring-2 ring-white text-[10px] font-bold text-white flex items-center justify-center">
                            1
                        </span>
                    </button>
                    
                    <div id="panelNotificaciones" class="hidden origin-top-right absolute right-0 mt-2 w-72 rounded-xl shadow-xl py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                        <div class="px-4 py-3 text-sm text-gray-800 border-b border-gray-100 font-bold bg-gray-50/50 rounded-t-xl">Tus Notificaciones</div>
                        
                        <div id="listaNotificaciones" class="max-h-64 overflow-y-auto">
                            </div>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="hidden md:flex flex-col text-right">
                        <span class="text-sm font-bold text-gray-800">Usuario</span>
                        <span class="text-xs text-gray-500"><?php echo $role_display; ?></span>
                    </div>
                    <div class="relative">
                        <img class="h-10 w-10 rounded-full object-cover border border-gray-200 shadow-sm" src="https://ui-avatars.com/api/?name=Usuario&background=eff6ff&color=2563eb&font-size=0.4" alt="Avatar">
                        <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full bg-green-500 ring-2 ring-white"></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <style>
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        .animate-marquee {
            display: inline-block;
            animation: marquee 15s linear infinite;
        }
        /* Scrollbar suave para las notificaciones */
        #listaNotificaciones::-webkit-scrollbar { width: 4px; }
        #listaNotificaciones::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const userId = <?php echo $user_id; ?>;
            const storageKey = 'welcome_noti_seen_' + userId;
            const hasSeenWelcome = localStorage.getItem(storageKey);

            const btnNoti = document.getElementById('btnNotificaciones');
            const badgeNoti = document.getElementById('badgeNotificacion');
            const panelNoti = document.getElementById('panelNotificaciones');
            const listaNoti = document.getElementById('listaNotificaciones');

            // Diseño de estado vacío (Sin notificaciones)
            const emptyStateHTML = `
                <div class="px-4 py-8 text-sm text-gray-400 text-center flex flex-col items-center">
                    <svg class="w-8 h-8 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    No hay notificaciones nuevas
                </div>
            `;

            // Evaluar estado al cargar la página
            if (!hasSeenWelcome) {
                badgeNoti.style.display = 'flex';
                listaNoti.innerHTML = `
                    <div class="block px-4 py-4 text-sm text-gray-700 border-b border-gray-50 hover:bg-gray-50 transition cursor-default">
                        <p class="font-bold text-blue-600 mb-1">🎉 ¡Bienvenido a AcortadorPro!</p>
                        <p class="text-xs text-gray-500">Comienza a crear, gestionar y proteger tus enlaces desde hoy mismo.</p>
                    </div>
                `;
            } else {
                badgeNoti.style.display = 'none';
                listaNoti.innerHTML = emptyStateHTML;
            }

            // Lógica al hacer clic en la campana
            btnNoti.addEventListener('click', (e) => {
                e.stopPropagation();
                panelNoti.classList.toggle('hidden');

                // Si era la primera vez y abrieron el panel
                if (!hasSeenWelcome && !panelNoti.classList.contains('hidden')) {
                    badgeNoti.style.display = 'none';
                    localStorage.setItem(storageKey, 'true'); // Marcar como visto

                    // Limpiar la lista SOLO cuando cierren el panel (para que no desaparezca mientras leen)
                    const clearOnClose = (event) => {
                        if (!btnNoti.contains(event.target) && !panelNoti.contains(event.target)) {
                            listaNoti.innerHTML = emptyStateHTML;
                            document.removeEventListener('click', clearOnClose);
                        }
                    };
                    document.addEventListener('click', clearOnClose);
                }
            });

            // Cerrar el panel al hacer clic en cualquier otra parte de la pantalla
            document.addEventListener('click', (e) => {
                if (!btnNoti.contains(e.target) && !panelNoti.contains(e.target)) {
                    panelNoti.classList.add('hidden');
                }
            });
            
            // Evitar que hacer clic dentro del panel lo cierre
            panelNoti.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });
    </script>