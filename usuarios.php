<?php
session_start();
require_once 'config/config.php';

// Protección extra: Solo Admin y Colaborador
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'colaborador'])) {
    header("Location: dashboard");
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

// Lógica para Añadir un Nuevo Usuario (Solo Administradores)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user']) && $_SESSION['user_role'] === 'admin') {
    $new_email = trim($_POST['email']);
    $new_password = $_POST['password'];
    $new_role = $_POST['role'];
    
    // Verificar si el correo ya existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$new_email]);
    if ($stmt->rowCount() > 0) {
        $error = "El correo electrónico ya está registrado en el sistema.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        // Creamos la cuenta activada por defecto (status = 1)
        $insert = $pdo->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, 1)");
        if ($insert->execute([$new_email, $hashed_password, $new_role])) {
            $success = "Usuario creado exitosamente con el rol de " . ucfirst($new_role) . ".";
        } else {
            $error = "Hubo un error al crear el usuario.";
        }
    }
}

// Obtener todos los usuarios
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Acortador Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800 flex h-screen overflow-hidden">

    <?php require_once 'includes/sidebar.php'; ?>

    <?php require_once 'includes/topbar.php'; ?>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 animate-fade-in">
            
            <div class="max-w-6xl mx-auto">
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 border-b border-gray-200 pb-5">
                    <div>
                        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Gestión de Usuarios</h2>
                        <p class="text-gray-500 text-sm mt-2">Administra los accesos, roles y el estado de las cuentas de la plataforma.</p>
                    </div>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <button onclick="document.getElementById('modalAddUser').classList.remove('hidden')" class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold py-2.5 px-5 rounded-full transition shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Añadir Usuario
                    </button>
                    <?php endif; ?>
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

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full whitespace-nowrap text-left text-sm">
                            <thead class="bg-white border-b border-gray-100 text-gray-400 uppercase text-[11px] font-bold tracking-wider">
                                <tr>
                                    <th class="px-6 py-5">Usuario</th>
                                    <th class="px-6 py-5">Rol de Acceso</th>
                                    <th class="px-6 py-5">Fecha de Registro</th>
                                    <th class="px-6 py-5 text-center">Estado</th>
                                    <th class="px-6 py-5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach ($usuarios as $user): 
                                    $inicial = strtoupper(substr($user['email'], 0, 1));
                                    $is_active = $user['status'] == 1;
                                ?>
                                <tr class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="relative flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg">
                                                    <?php echo $inicial; ?>
                                                </div>
                                                <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white <?php echo $is_active ? 'bg-green-500' : 'bg-gray-300'; ?>"></span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <?php switch($user['role']):
                                            case 'admin': echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-900 text-white shadow-sm">Admin</span>'; break;
                                            case 'colaborador': echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">Colaborador</span>'; break;
                                            case 'pro': echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-100">Pro</span>'; break;
                                            default: echo '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">Free</span>'; break;
                                        endswitch; ?>
                                    </td>

                                    <td class="px-6 py-4 text-gray-500 text-sm">
                                        <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($is_active): ?>
                                            <span class="inline-flex items-center text-xs font-medium text-green-600">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center text-xs font-medium text-gray-400">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg> Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition" title="Editar Usuario">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </button>
                                            <?php if ($_SESSION['user_role'] === 'admin' && $user['id'] !== $_SESSION['user_id']): ?>
                                            <button class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-full transition" title="Eliminar Usuario">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div> <div id="modalAddUser" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 overflow-hidden transform transition-all animate-fade-in-up m-4">
            
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-900">Crear Nuevo Usuario</h3>
                <button onclick="document.getElementById('modalAddUser').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="POST" action="usuarios" class="p-6">
                <input type="hidden" name="add_user" value="1">
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Correo Electrónico</label>
                    <input type="email" name="email" required placeholder="ejemplo@correo.com" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-sm transition shadow-inner">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contraseña Temporal</label>
                    <input type="password" name="password" required placeholder="Crea una contraseña segura" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-sm transition shadow-inner">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Rol de Acceso</label>
                    <div class="relative">
                        <select name="role" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-sm transition appearance-none">
                            <option value="free">Usuario Free (Básico)</option>
                            <option value="pro">Usuario Pro (Premium)</option>
                            <option value="colaborador">Colaborador (Soporte)</option>
                            <option value="admin">Administrador (Total)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('modalAddUser').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition shadow-sm">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-md">
                        Crear Cuenta
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }
    </style>
</body>
</html>