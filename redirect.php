<?php
session_start();
require_once 'config/config.php';

// Si no hay código en la URL, mandamos al inicio
if (!isset($_GET['c']) || empty($_GET['c'])) {
    header("Location: " . BASE_URL);
    exit;
}

$short_code = trim($_GET['c']);

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos.");
}

// Buscar el enlace en la base de datos
$stmt = $pdo->prepare("SELECT * FROM links WHERE short_code = ? LIMIT 1");
$stmt->execute([$short_code]);
$link = $stmt->fetch(PDO::FETCH_ASSOC);

// 1. Si el enlace no existe (Error 404)
if (!$link) {
    mostrarPantallaMensaje("Enlace no encontrado", "El enlace al que intentas acceder no existe o ha sido eliminado.", "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z", "text-yellow-500", "bg-yellow-50");
    exit;
}

// 2. Comprobar Expiración (Si es un enlace temporal)
if (!empty($link['expiration_date']) && strtotime($link['expiration_date']) < time()) {
    mostrarPantallaMensaje("Enlace Expirado", "Este enlace temporal ha superado su fecha de caducidad y ya no está disponible.", "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z", "text-red-500", "bg-red-50");
    exit;
}

// 3. Comprobar Contraseña
$requiere_password = !empty($link['link_password']);
$acceso_concedido = false;

// Si ya se ingresó la contraseña correcta en esta sesión
if (isset($_SESSION['unlocked_links']) && in_array($link['id'], $_SESSION['unlocked_links'])) {
    $acceso_concedido = true;
}

if ($requiere_password && !$acceso_concedido) {
    $error_pass = '';
    
    // Procesar formulario de contraseña
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verificar_pass'])) {
        $input_pass = $_POST['password'];
        if (password_verify($input_pass, $link['link_password'])) {
            // Guardar en sesión que este link está desbloqueado
            $_SESSION['unlocked_links'][] = $link['id'];
            $acceso_concedido = true;
            // Recargar para aplicar redirección
            header("Location: " . BASE_URL . $short_code);
            exit;
        } else {
            $error_pass = "La contraseña es incorrecta.";
        }
    }
    
    // Mostrar pantalla de solicitud de contraseña
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enlace Protegido</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-50 flex items-center justify-center min-h-screen font-sans text-gray-800">
        <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-sm border border-gray-100 text-center animate-fade-in-up">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-purple-50 mb-6">
                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Enlace Protegido</h2>
            <p class="text-gray-500 mb-6 text-sm">Este enlace requiere una contraseña para continuar.</p>
            
            <?php if ($error_pass): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm border border-red-100"><?php echo $error_pass; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="verificar_pass" value="1">
                <input type="password" name="password" required placeholder="Ingresa la contraseña" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-white transition bg-gray-50 text-center text-lg tracking-widest mb-4 shadow-inner">
                <button type="submit" class="w-full bg-purple-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-purple-700 transition shadow-md">Desbloquear Enlace</button>
            </form>
        </div>
        <style>
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in-up { animation: fadeInUp 0.5s ease-out forwards; }
        </style>
    </body>
    </html>
    <?php
    exit;
}

// 4. Si pasamos todas las validaciones: Sumar visita y Redirigir
if (!$requiere_password || $acceso_concedido) {
    // Sumar 1 a las vistas
    $update = $pdo->prepare("UPDATE links SET views = views + 1 WHERE id = ?");
    $update->execute([$link['id']]);

    // Redirección final al destino original
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $link['original_url']);
    exit;
}


// --- Función Helper para pantallas de error minimalistas ---
function mostrarPantallaMensaje($titulo, $mensaje, $icono_svg, $color_texto, $color_fondo) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $titulo; ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-50 flex items-center justify-center min-h-screen font-sans text-gray-800">
        <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-sm border border-gray-100 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full <?php echo $color_fondo; ?> mb-6">
                <svg class="h-8 w-8 <?php echo $color_texto; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $icono_svg; ?>"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2"><?php echo $titulo; ?></h2>
            <p class="text-gray-500 mb-8 text-sm"><?php echo $mensaje; ?></p>
            <a href="<?php echo BASE_URL; ?>" class="inline-block w-full bg-gray-900 text-white font-bold py-3 px-4 rounded-xl hover:bg-gray-800 transition">Volver al Inicio</a>
        </div>
    </body>
    </html>
    <?php
}
?>