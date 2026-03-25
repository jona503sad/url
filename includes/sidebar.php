<?php
// includes/sidebar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['user_role'] ?? 'free';
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col h-screen fixed left-0 top-0 z-10">
    <div class="h-16 flex items-center px-6 border-b border-gray-100">
        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
        <span class="ml-2 font-bold text-xl text-gray-900 tracking-tight">Acortador<span class="text-blue-600">Pro</span></span>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'bg-gray-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
            <svg class="mr-3 h-5 w-5 <?php echo $current_page == 'dashboard.php' ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Dashboard
        </a>

        <a href="cortar.php" class="<?php echo $current_page == 'cortar.php' ? 'bg-gray-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
            Cortar Link
        </a>

        <a href="links.php" class="<?php echo $current_page == 'links.php' ? 'bg-gray-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
            Lista de Links
        </a>

        <?php if (in_array($role, ['admin', 'colaborador'])): ?>
        <a href="usuarios.php" class="<?php echo $current_page == 'usuarios.php' ? 'bg-gray-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Lista de Usuarios
        </a>

        <a href="mensajes.php" class="<?php echo $current_page == 'mensajes.php' ? 'bg-gray-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            Mensajes
        </a>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
        <a href="configuracion.php" class="<?php echo $current_page == 'configuracion.php' ? 'bg-gray-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Configuración
        </a>
        <?php endif; ?>
    </nav>

    <div class="p-4 border-t border-gray-100">
        <a href="logout.php" class="group flex items-center px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">
            <svg class="mr-3 h-5 w-5 text-red-500 group-hover:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Cerrar Sesión
        </a>
    </div>
</aside>