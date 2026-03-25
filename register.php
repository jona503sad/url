<?php
session_start();
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos.");
}

$error = '';
$show_success_modal = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // Validar contraseñas
    if ($password !== $password_confirm) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Verificar si el correo ya existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Este correo electrónico ya está registrado.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // CONFIGURACIÓN DE ACTIVACIÓN: ([Source 7])
            $estado_cuenta = 1; // 1 = Auto, 0 = Manual por admin

            $insert = $pdo->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, 'free', ?)");
            if ($insert->execute([$email, $hashed_password, $estado_cuenta])) {
                $show_success_modal = true;
            } else {
                $error = "Hubo un error al crear la cuenta. Inténtalo de nuevo.";
            }
        }
    }
}

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

    /* Mismo patrón de círculos */
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

    <div class="bg-white/95 backdrop-blur-sm p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 z-10 relative">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Crea tu cuenta</h2>
            <p class="text-gray-500 mt-2 text-sm">Empieza a acortar y gestionar tus enlaces profesionalmente</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-6 text-sm text-center border border-red-100"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="register"> <input type="hidden" name="register" value="1">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Correo Electrónico</label>
                <input type="email" name="email" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-sm shadow-inner">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
                <input type="password" name="password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-sm shadow-inner">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Contraseña</label>
                <input type="password" name="password_confirm" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-sm shadow-inner">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-blue-700 transition shadow-lg flex items-center justify-center gap-2">
                Registrarse
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            </button>
        </form>
        
        <p class="text-center text-sm text-gray-500 mt-6 pt-4 border-t border-gray-100">
            ¿Ya tienes una cuenta? <a href="login" class="text-blue-600 font-medium hover:underline">Inicia Sesión</a>
        </p>

        <?php if ($show_success_modal): ?>
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 animate-fade-in-up">
            <div class="bg-white rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl m-4 border border-gray-100">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">¡Registro Exitoso!</h3>
                <p class="text-gray-500 mb-6 text-sm">Tu cuenta ha sido creada correctamente. Ya puedes iniciar sesión y comenzar a usar el sistema profesionalmente.</p>
                <a href="login" class="inline-block w-full bg-gray-900 text-white font-bold py-3 px-4 rounded-xl hover:bg-gray-800 transition shadow-md">Ir al Login</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>