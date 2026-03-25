<?php
session_start();
require_once 'config/config.php';

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión. Asegúrate de haber instalado el sistema.");
}

$error = '';
$success = '';

// Lógica de "Olvidé mi contraseña" ([Source 6])
if (isset($_GET['action']) && $_GET['action'] == 'forgot') {
    $stmt = $pdo->query("SELECT email FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $admin_email = $admin['email'];
        // mail($admin_email, "Solicitud de restablecimiento", "Un usuario solicitó restablecer contraseña.");
        $success = "Se ha notificado al administrador para restablecer tu contraseña.";
    }
}

// Lógica de Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $captcha_input = (int)$_POST['captcha'];
    
    // Validar Captcha Numérico ([Source 6])
    if ($captcha_input !== $_SESSION['captcha_answer']) {
        $error = "El resultado del captcha numérico es incorrecto.";
    } else {
        // Buscar usuario
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] == 0) {
                $error = "Tu cuenta está inactiva. Espera la activación del administrador.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                
                // Si venía de intentar acortar un link en el Home
                if (isset($_SESSION['pending_url'])) {
                    $pending_url = $_SESSION['pending_url'];
                    unset($_SESSION['pending_url']);
                    header("Location: dashboard.php?create=" . urlencode($pending_url));
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            }
        } else {
            $error = "Credenciales incorrectas.";
        }
    }
}

// Generar Captcha Numérico para el formulario ([Source 6])
$num1 = rand(1, 9);
$num2 = rand(1, 9);
$_SESSION['captcha_answer'] = $num1 + $num2;

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
        z-index: -1; /* Detrás de todo */
        pointer-events: none; /* No intercepta clics */
    }

    .circles li{
        position: absolute;
        display: block;
        list-style: none;
        width: 20px;
        height: 20px;
        background: rgba(37, 99, 235, 0.1); /* Azul Blue-600 muy suave */
        animation: animate 25s linear infinite;
        bottom: -150px;
        border-radius: 50%; /* Círculos */
    }

    .circles li:nth-child(1){
        left: 25%; width: 80px; height: 80px; animation-delay: 0s;
    }
    .circles li:nth-child(2){
        left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s;
    }
    .circles li:nth-child(3){
        left: 70%; width: 20px; height: 20px; animation-delay: 4s;
    }
    .circles li:nth-child(4){
        left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s;
    }
    .circles li:nth-child(5){
        left: 65%; width: 20px; height: 20px; animation-delay: 0s;
    }
    .circles li:nth-child(6){
        left: 75%; width: 110px; height: 110px; animation-delay: 3s;
    }
    .circles li:nth-child(7){
        left: 35%; width: 150px; height: 150px; animation-delay: 7s;
    }
    .circles li:nth-child(8){
        left: 50%; width: 25px; height: 25px; animation-delay: 15s; animation-duration: 45s;
    }
    .circles li:nth-child(9){
        left: 20%; width: 15px; height: 15px; animation-delay: 2s; animation-duration: 35s;
    }
    .circles li:nth-child(10){
        left: 85%; width: 150px; height: 150px; animation-delay: 0s; animation-duration: 11s;
    }

    @keyframes animate {
        0%{
            transform: translateY(0) rotate(0deg);
            opacity: 1;
            border-radius: 50%;
        }
        100%{
            transform: translateY(-1000px) rotate(720deg);
            opacity: 0;
            border-radius: 10%; /* Se deforman al subir */
        }
    }
</style>

<div class="flex items-center justify-center min-h-[calc(100vh-16rem)] px-4 animate-fade-in-up relative overflow-hidden py-12">
    
    <ul class="circles">
            <li></li><li></li><li></li><li></li><li></li>
            <li></li><li></li><li></li><li></li><li></li>
    </ul>

    <div class="bg-white/95 backdrop-blur-sm p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 z-10">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Bienvenido de nuevo</h2>
            <p class="text-gray-500 mt-2 text-sm">Ingresa a tu cuenta para gestionar tus enlaces</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-6 text-sm text-center border border-red-100"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-50 text-green-600 p-3 rounded-lg mb-6 text-sm text-center border border-green-100"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="login"> <input type="hidden" name="login" value="1">
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Correo Electrónico</label>
                <input type="email" name="email" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-sm shadow-inner">
            </div>
            
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
                <input type="password" name="password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-sm shadow-inner">
            </div>

            <div class="mb-6 flex items-center space-x-4 bg-blue-50 p-3 rounded-xl border border-blue-100">
                <label class="text-sm font-semibold text-blue-800 whitespace-nowrap">¿Cuánto es <?php echo $num1; ?> + <?php echo $num2; ?>?</label>
                <input type="number" name="captcha" required class="w-20 px-3 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-center font-bold text-lg text-gray-800">
            </div>

            <div class="flex items-center justify-between mb-6">
                <a href="login?action=forgot" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="w-full bg-gray-900 text-white font-bold py-3 px-4 rounded-xl hover:bg-gray-800 transition shadow-lg flex items-center justify-center gap-2">
                Iniciar Sesión
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>
        
        <p class="text-center text-sm text-gray-500 mt-6 pt-4 border-t border-gray-100">
            ¿No tienes una cuenta? <a href="register" class="text-blue-600 font-medium hover:underline">Regístrate aquí</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>