<?php
session_start();
require_once 'config/config.php';

// Redirigir si no ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos.");
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Determinar qué enlaces mostrar según el rol 
if (in_array($user_role, ['admin', 'colaborador'])) {
    // Admin y Colaborador ven todos los links, con el correo de quien lo creó
    $stmt = $pdo->prepare("
        SELECT l.*, u.email as creator_email 
        FROM links l 
        LEFT JOIN users u ON l.user_id = u.id 
        ORDER BY l.created_at DESC
    ");
    $stmt->execute();
} else {
    // Usuarios Free y Pro solo ven sus propios links 
    $stmt = $pdo->prepare("SELECT * FROM links WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
}

$links = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Links - Acortador Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
        
        /* Ocultar scrollbar para un look más limpio en tablas responsivas */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800 flex h-screen overflow-hidden">

    <?php require_once 'includes/sidebar.php'; ?>

    <div class="flex flex-col flex-1 w-full md:pl-64 h-screen">
        
        <?php require_once 'includes/topbar.php'; ?>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 animate-fade-in">
            
            <div class="max-w-6xl mx-auto">
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Gestión de Enlaces</h2>
                        <p class="text-gray-500 text-sm mt-1">Administra, copia y revisa las estadísticas de tus URLs.</p>
                    </div>
                    <a href="cortar.php" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-5 rounded-lg transition shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Nuevo Enlace
                    </a>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full whitespace-nowrap text-left text-sm">
                            <thead class="bg-gray-50/50 border-b border-gray-100 text-gray-500">
                                <tr>
                                    <th class="px-6 py-4 font-medium">URL Original</th>
                                    <th class="px-6 py-4 font-medium">Enlace Corto</th>
                                    <th class="px-6 py-4 font-medium text-center">Clics</th>
                                    <th class="px-6 py-4 font-medium">Estado / Info</th>
                                    <th class="px-6 py-4 font-medium text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php if (count($links) > 0): ?>
                                    <?php foreach ($links as $link): 
                                        $short_url = BASE_URL . $link['short_code'];
                                        
                                        // Determinar el estado del enlace
                                        $is_expired = false;
                                        if (!empty($link['expiration_date']) && strtotime($link['expiration_date']) < time()) {
                                            $is_expired = true;
                                        }
                                        $has_password = !empty($link['link_password']);
                                    ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors group">
                                        
                                        <td class="px-6 py-4 max-w-[200px] truncate text-gray-600" title="<?php echo htmlspecialchars($link['original_url']); ?>">
                                            <?php echo htmlspecialchars($link['original_url']); ?>
                                        </td>
                                        
                                        <td class="px-6 py-4 font-medium text-blue-600">
                                            <a href="<?php echo $short_url; ?>" target="_blank" class="hover:underline">
                                                /<?php echo htmlspecialchars($link['short_code']); ?>
                                            </a>
                                        </td>
                                        
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <?php echo $link['views']; ?>
                                            </span>
                                        </td>
                                        
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-2">
                                                <?php if ($is_expired): ?>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-red-50 text-red-600 uppercase tracking-wider">Expirado</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-green-50 text-green-600 uppercase tracking-wider">Activo</span>
                                                <?php endif; ?>

                                                <?php if ($has_password): ?>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-purple-50 text-purple-600 uppercase tracking-wider" title="Protegido con contraseña">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                        Pass
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (isset($link['creator_email'])): ?>
                                                <div class="text-[11px] text-gray-400 mt-1">Por: <?php echo htmlspecialchars($link['creator_email']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button onclick="copyLink('<?php echo $short_url; ?>')" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Copiar Enlace">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                </button>
                                                <a href="<?php echo $short_url; ?>" target="_blank" class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Probar Enlace">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                                <p>Aún no hay enlaces generados.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <div id="toast" class="fixed bottom-5 right-5 bg-gray-900 text-white px-6 py-3 rounded-xl shadow-2xl transform translate-y-20 opacity-0 transition-all duration-300 z-50 flex items-center gap-3">
        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span>Enlace copiado al portapapeles</span>
    </div>

    <script>
        function copyLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                const toast = document.getElementById('toast');
                toast.classList.remove('translate-y-20', 'opacity-0');
                setTimeout(() => {
                    toast.classList.add('translate-y-20', 'opacity-0');
                }, 3000);
            });
        }
    </script>
</body>
</html>