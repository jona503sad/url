<?php
session_start();
// Protección básica: si no está logueado, a fuera
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/config.php';

$user_id = $_SESSION['user_id'];
$url_to_shorten = '';
if (isset($_GET['create'])) {
    $url_to_shorten = htmlspecialchars($_GET['create']);
}

// 1. Inicializamos variables en 0 para evitar cualquier Error 500
$total_links = 0;
$total_clics = 0;
$mis_links = [];

// 2. Conexión y consultas blindadas (Si algo falla, la página sigue viva)
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Borrar enlace si se solicita
    if (isset($_GET['delete'])) {
        $id_del = (int)$_GET['delete'];
        $stmt_del = $pdo->prepare("DELETE FROM links WHERE id = ? AND user_id = ?");
        $stmt_del->execute([$id_del, $user_id]);
        header("Location: dashboard.php");
        exit;
    }

    // Contar links totales
    $stmt_total = $pdo->prepare("SELECT COUNT(*) FROM links WHERE user_id = ?");
    $stmt_total->execute([$user_id]);
    $total_links = $stmt_total->fetchColumn();

    // Obtener la lista de enlaces
    $stmt_list = $pdo->prepare("SELECT * FROM links WHERE user_id = ? ORDER BY created_at DESC");
    $stmt_list->execute([$user_id]);
    $mis_links = $stmt_list->fetchAll(PDO::FETCH_ASSOC);

    // Sumar clics de forma segura desde PHP (así no crashea si falta la columna en MySQL)
    foreach ($mis_links as $link) {
        if (isset($link['clicks'])) {
            $total_clics += $link['clicks'];
        }
    }

} catch (PDOException $e) {
    // Si la base de datos falla o falta alguna tabla, la página carga vacía pero NO da Error 500
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Acortador Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-800">

    <?php require_once 'includes/sidebar.php'; ?>

    <?php require_once 'includes/topbar.php'; ?>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            
            <?php if ($url_to_shorten): ?>
                <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded shadow-sm">
                    Detectamos que intentaste acortar un enlace desde la página de inicio. ¡Ve a la sección <strong>Cortar Link</strong> para procesarlo!
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-full bg-blue-50 text-blue-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Links Totales</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_links; ?></p>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-full bg-green-50 text-green-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Clics Totales</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_clics; ?></p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
                    <div class="p-3 rounded-full bg-purple-50 text-purple-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Links Activos</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_links; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Actividad Reciente</h3>
                </div>
                
                <?php if (count($mis_links) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-wider border-b border-gray-50">
                                    <th class="pb-3 font-medium">Enlace Corto</th>
                                    <th class="pb-3 font-medium">Destino</th>
                                    <th class="pb-3 font-medium text-center">Clics</th>
                                    <th class="pb-3 font-medium text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-50">
                                <?php foreach ($mis_links as $link): 
                                    $short_url = $_SERVER['HTTP_HOST'] . "/" . $link['alias'];
                                ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-4 font-semibold text-blue-600">
                                        <a href="http://<?php echo $short_url; ?>" target="_blank">/<?php echo htmlspecialchars($link['alias']); ?></a>
                                    </td>
                                    <td class="py-4 text-gray-500 max-w-xs truncate" title="<?php echo htmlspecialchars($link['url_larga']); ?>">
                                        <?php echo htmlspecialchars($link['url_larga']); ?>
                                    </td>
                                    <td class="py-4 text-center font-bold text-gray-700">
                                        <?php echo isset($link['clicks']) ? $link['clicks'] : 0; ?>
                                    </td>
                                    <td class="py-4 text-right">
                                        <button onclick="copyToClipboard('<?php echo $short_url; ?>')" class="text-gray-400 hover:text-green-500 transition mr-3" title="Copiar">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <a href="dashboard.php?delete=<?php echo $link['id']; ?>" onclick="return confirm('¿Seguro que quieres borrar este enlace?')" class="text-gray-400 hover:text-red-500 transition" title="Borrar">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-10 text-gray-500 text-sm">
                        Aún no tienes actividad. ¡Crea tu primer enlace acortado!
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script>
    function copyToClipboard(text) {
        const fullLink = window.location.protocol + "//" + text;
        navigator.clipboard.writeText(fullLink).then(() => {
            alert('¡Enlace copiado al portapapeles!');
        });
    }
    </script>
</body>
</html>