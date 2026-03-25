<?php
session_start();
require_once 'config/config.php';

// Protección: Solo Admin y Colaborador
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'colaborador'])) {
    header("Location: dashboard.php");
    exit;
}

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Auto-crear la tabla de mensajes si no existe (Para no tener que reinstalar)
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('unread', 'read') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    die("Error de conexión a la base de datos.");
}

$success = '';

// Lógica para marcar como leído o eliminar
if (isset($_GET['action']) && isset($_GET['id'])) {
    $msg_id = (int)$_GET['id'];
    
    if ($_GET['action'] == 'read') {
        $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = ?");
        $stmt->execute([$msg_id]);
        $success = "Mensaje marcado como leído.";
    } elseif ($_GET['action'] == 'delete' && $_SESSION['user_role'] === 'admin') {
        // Solo el admin puede borrar mensajes
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$msg_id]);
        $success = "Mensaje eliminado correctamente.";
    }
    
    // Redirigir para limpiar la URL
    header("Location: mensajes.php");
    exit;
}

// Para propósitos de demostración: Si la tabla está vacía, insertamos un mensaje de prueba
$stmt = $pdo->query("SELECT COUNT(*) FROM messages");
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO messages (sender_email, subject, message) VALUES ('sistema@acortadorpro.com', '¡Bienvenido a tu panel de mensajes!', 'Este es un mensaje de prueba autogenerado. Aquí recibirás notificaciones del sistema y solicitudes de los usuarios (como reseteos de contraseña o reportes de enlaces caídos).')");
}

// Obtener los mensajes
$stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - Acortador Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800 flex h-screen overflow-hidden">

    <?php require_once 'includes/sidebar.php'; ?>

    <div class="flex flex-col flex-1 w-full md:pl-64 h-screen">
        
        <?php require_once 'includes/topbar.php'; ?>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 animate-fade-in">
            
            <div class="max-w-5xl mx-auto">
                
                <div class="mb-8 border-b border-gray-200 pb-5">
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Buzón de Entrada</h2>
                    <p class="text-gray-500 text-sm mt-2">Revisa las notificaciones del sistema y las solicitudes de soporte.</p>
                </div>

                <?php if ($success): ?>
                    <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm border border-green-200 flex items-start shadow-sm transition-all duration-500" id="alert-success">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span><?php echo $success; ?></span>
                    </div>
                    <script>
                        setTimeout(() => { document.getElementById('alert-success').style.display = 'none'; }, 3000);
                    </script>
                <?php endif; ?>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <ul class="divide-y divide-gray-100">
                        <?php if (count($mensajes) > 0): ?>
                            <?php foreach ($mensajes as $msg): 
                                $is_unread = $msg['status'] === 'unread';
                            ?>
                            <li class="relative hover:bg-gray-50/50 transition duration-150 ease-in-out group">
                                <div class="px-6 py-5 flex items-center justify-between">
                                    <div class="flex items-center min-w-0 gap-4">
                                        <div class="flex-shrink-0 w-3 flex justify-center">
                                            <?php if ($is_unread): ?>
                                                <span class="h-2.5 w-2.5 bg-blue-600 rounded-full ring-4 ring-blue-50"></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="hidden sm:flex flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 border border-gray-200 items-center justify-center text-gray-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        </div>

                                        <div class="min-w-0 flex-1 px-2 cursor-pointer" onclick="toggleMessage(<?php echo $msg['id']; ?>)">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium <?php echo $is_unread ? 'text-gray-900' : 'text-gray-600'; ?> truncate">
                                                    <?php echo htmlspecialchars($msg['sender_email']); ?>
                                                </p>
                                                <p class="text-xs text-gray-400 whitespace-nowrap ml-4">
                                                    <?php echo date('d M, Y H:i', strtotime($msg['created_at'])); ?>
                                                </p>
                                            </div>
                                            <div class="mt-1">
                                                <p class="text-sm <?php echo $is_unread ? 'font-semibold text-gray-800' : 'text-gray-500'; ?> truncate">
                                                    <?php echo htmlspecialchars($msg['subject']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="ml-4 flex-shrink-0 flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <?php if ($is_unread): ?>
                                            <a href="mensajes.php?action=read&id=<?php echo $msg['id']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition" title="Marcar como leído">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                            <a href="mensajes.php?action=delete&id=<?php echo $msg['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este mensaje?');" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-full transition" title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div id="msg-body-<?php echo $msg['id']; ?>" class="hidden px-6 py-6 bg-gray-50 border-t border-gray-100 shadow-inner">
                                    <h4 class="text-sm font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($msg['subject']); ?></h4>
                                    <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">
                                        <?php echo htmlspecialchars($msg['message']); ?>
                                    </div>
                                    
                                    <div class="mt-6 flex gap-3">
                                        <a href="mailto:<?php echo htmlspecialchars($msg['sender_email']); ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                            Responder por Correo
                                        </a>
                                        <?php if ($is_unread): ?>
                                            <a href="mensajes.php?action=read&id=<?php echo $msg['id']; ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                                                Marcar Leído
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="px-6 py-12 text-center text-gray-400">
                                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-50 mb-4">
                                    <svg class="h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                </div>
                                <p class="text-sm font-medium">Tu buzón está vacío.</p>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

            </div>
        </main>
    </div>

    <script>
        function toggleMessage(id) {
            const bodyDiv = document.getElementById('msg-body-' + id);
            if (bodyDiv.classList.contains('hidden')) {
                bodyDiv.classList.remove('hidden');
            } else {
                bodyDiv.classList.add('hidden');
            }
        }
    </script>
</body>
</html>