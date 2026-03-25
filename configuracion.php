<?php
session_start();
require_once 'config/config.php';

// Protección Estricta: Solo el Administrador puede acceder a esta página
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Si es un usuario normal o colaborador, lo mandamos al dashboard
    header("Location: dashboard.php");
    exit;
}

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos.");
}

$error = '';
$success = '';
$admin_id = $_SESSION['user_id'];

// Obtener los datos actuales del administrador
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Procesar el formulario de actualización de cuenta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del correo electrónico no es válido.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "Las contraseñas nuevas no coinciden.";
    } else {
        // Verificar si el nuevo correo ya está en uso por OTRO usuario
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$new_email, $admin_id]);
        if ($stmt->rowCount() > 0) {
            $error = "Este correo electrónico ya está siendo utilizado por otra cuenta.";
        } else {
            // Actualizar datos
            if (!empty($new_password)) {
                // Actualiza correo y contraseña
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $update = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
                if ($update->execute([$new_email, $hashed_password, $admin_id])) {
                    $success = "Perfil y contraseña actualizados correctamente.";
                    $admin_data['email'] = $new_email; // Actualizar vista
                } else {
                    $error = "Error al actualizar los datos.";
                }
            } else {
                // Actualiza solo el correo
                $update = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                if ($update->execute([$new_email, $admin_id])) {
                    $success = "Correo electrónico actualizado correctamente.";
                    $admin_data['email'] = $new_email; // Actualizar vista
                } else {
                    $error = "Error al actualizar el correo.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Acortador Pro</title>
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
            
            <div class="max-w-4xl mx-auto">
                
                <div class="mb-8 border-b border-gray-200 pb-5">
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Configuración del Sistema</h2>
                    <p class="text-gray-500 text-sm mt-2">Administra tus credenciales de acceso y preferencias globales.</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm border border-red-100 flex items-start shadow-sm">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm border border-green-200 flex items-start shadow-sm">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-1">
                        <nav class="space-y-1">
                            <a href="#" class="bg-blue-50 text-blue-700 group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors border-l-4 border-blue-600">
                                <svg class="text-blue-500 mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Perfil Administrador
                            </a>
                            <a href="#" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors border-l-4 border-transparent opacity-50 cursor-not-allowed" title="Próximamente">
                                <svg class="text-gray-400 group-hover:text-gray-500 mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Ajustes Generales
                            </a>
                        </nav>
                    </div>

                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-100">
                                <h3 class="text-lg font-bold text-gray-900">Credenciales de Acceso</h3>
                                <p class="text-sm text-gray-500 mt-1">Actualiza tu correo de administrador o cambia tu contraseña.</p>
                            </div>
                            
                            <form method="POST" action="configuracion.php" class="p-6 space-y-6">
                                <input type="hidden" name="update_profile" value="1">
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Correo Electrónico Administrador</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required class="w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-sm transition shadow-inner">
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 pt-6">
                                    <h4 class="text-sm font-bold text-gray-800 mb-4">Cambiar Contraseña</h4>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-2">Nueva Contraseña</label>
                                            <input type="password" name="new_password" placeholder="Dejar en blanco para no cambiar" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-sm transition">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-2">Confirmar Nueva Contraseña</label>
                                            <input type="password" name="confirm_password" placeholder="Repite la contraseña" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-sm transition">
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-4 flex justify-end">
                                    <button type="submit" class="bg-gray-900 text-white font-bold py-2.5 px-6 rounded-xl hover:bg-gray-800 transition shadow-md">
                                        Guardar Cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</body>
</html>