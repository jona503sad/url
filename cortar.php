<?php
session_start();
require_once 'config/config.php';

// 1. Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos.");
}

// 2. Verificación de Seguridad
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$rol_usuario = strtolower($_SESSION['user_role'] ?? 'free'); 
$mensaje = '';
$limite_alcanzado = false;

// 3. Obtener contador de links del día
$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM links WHERE user_id = ? AND DATE(created_at) = CURDATE()");
$stmt_count->execute([$user_id]);
$links_hoy = $stmt_count->fetchColumn();

// 4. Lógica de Restricción para Plan Free
if ($rol_usuario === 'free' && $links_hoy >= 3) {
    $limite_alcanzado = true;
    $mensaje = "<div class='alert-danger'>⚠️ Has alcanzado tu límite de 3 enlaces diarios. <a href='planes.php' style='color:inherit; font-weight:bold; text-decoration:underline;'>¡Pásate a PRO!</a></div>";
}

// 5. Procesar el Formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url_larga']) && !$limite_alcanzado) {
    $url_larga = trim($_POST['url_larga']);
    $alias = null;
    $password = null;
    $expiracion = null;

    // Solo procesar opciones avanzadas si NO es free
    if ($rol_usuario !== 'free') {
        if (!empty($_POST['alias'])) $alias = trim($_POST['alias']);
        if (!empty($_POST['password'])) $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        if (!empty($_POST['expiracion'])) $expiracion = $_POST['expiracion'];
    }

    if (!empty($url_larga)) {
        if (empty($alias)) {
            $alias = substr(md5(uniqid(rand(), true)), 0, 6);
        }

        try {
            $sql = "INSERT INTO links (user_id, url_larga, alias, password, expiracion, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $url_larga, $alias, $password, $expiracion]);
            
            // Refrescar contador después de insertar
            $links_hoy++; 
            $mensaje = "<div class='alert-success'>¡Éxito! Tu link es: <b>" . $_SERVER['HTTP_HOST'] . "/$alias</b></div>";
        } catch (PDOException $e) {
            $mensaje = "<div class='alert-danger'>Error: El alias o link ya existe o hubo un problema técnico.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acortar Link - Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        h2 { text-align: center; color: #1a202c; margin-bottom: 20px; font-size: 24px; }
        
        /* Barra de Estatus del Plan */
        .plan-status-bar { background: #f8fafc; padding: 12px 20px; border-radius: 10px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; border: 1px solid #e2e8f0; }
        .badge-plan { background: #2d3748; color: white; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .usage-text { font-size: 13px; color: #4a5568; margin-left: 8px; }
        .limit-info { color: #718096; font-size: 11px; }

        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #4a5568; }
        input { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; transition: all 0.3s; font-size: 14px; }
        input:focus { border-color: #3182ce; outline: none; box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2); }
        
        .btn-submit { width: 100%; padding: 15px; background: #2d3748; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        .btn-submit:hover { background: #1a202c; }
        .btn-locked { background: #a0aec0 !important; cursor: not-allowed; }

        /* Estilo Opciones Avanzadas */
        .advanced-box { position: relative; margin-top: 25px; padding: 20px; border: 1px solid #edf2f7; border-radius: 12px; background: #fdfdfd; }
        .is-free .blur-content { filter: blur(5px); pointer-events: none; user-select: none; opacity: 0.4; }
        
        .premium-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            z-index: 5; background: rgba(255,255,255,0.1);
        }
        .premium-overlay i { font-size: 35px; color: #ed8936; margin-bottom: 10px; }
        .btn-upgrade { background: #ed8936; color: white; padding: 8px 20px; text-decoration: none; border-radius: 20px; font-size: 12px; font-weight: bold; box-shadow: 0 4px 6px rgba(237, 137, 54, 0.2); }

        /* Alertas */
        .alert-success { background: #c6f6d5; color: #22543d; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #38a169; text-align: center; }
        .alert-danger { background: #fff5f5; color: #c53030; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #e53e3e; text-align: center; }
    </style>
</head>
<body>

<div class="container">
    <h2>Cortar Nuevo Enlace</h2>

    <div class="plan-status-bar">
        <div style="display: flex; align-items: center;">
            <span class="badge-plan">Plan <?php echo ucfirst($rol_usuario); ?></span>
            <span class="usage-text">
                <?php if($rol_usuario === 'free'): ?>
                    Uso diario: <b><?php echo $links_hoy; ?> / 3</b>
                <?php else: ?>
                    Uso diario: <b>Ilimitado ✨</b>
                <?php endif; ?>
            </span>
        </div>
        
        <?php if($rol_usuario === 'free'): ?>
            <div class="limit-info">
                <i class="fas fa-lock"></i> Funciones Pro bloqueadas
            </div>
        <?php endif; ?>
    </div>

    <?php echo $mensaje; ?>

    <form method="POST">
        <div class="form-group">
            <label>Pega tu URL larga aquí</label>
            <input type="url" name="url_larga" placeholder="https://ejemplo.com/tu-link-largo" required 
                   <?php echo $limite_alcanzado ? 'disabled' : ''; ?>>
        </div>

        <div class="advanced-box <?php echo ($rol_usuario === 'free') ? 'is-free' : ''; ?>">
            <?php if ($rol_usuario === 'free'): ?>
                <div class="premium-overlay">
                    <i class="fas fa-crown"></i>
                    <a href="planes.php" class="btn-upgrade">DESBLOQUEAR ALIAS Y CLAVES</a>
                </div>
            <?php endif; ?>

            <div class="blur-content">
                <div class="form-group">
                    <label>Alias Personalizado</label>
                    <input type="text" name="alias" placeholder="ej: oferta-especial">
                </div>
                <div class="form-group">
                    <label>Contraseña de acceso</label>
                    <input type="password" name="password" placeholder="Proteger link">
                </div>
                <div class="form-group">
                    <label>Fecha de Expiración</label>
                    <input type="date" name="expiracion">
                </div>
            </div>
        </div>

        <div style="margin-top: 25px;">
            <?php if ($limite_alcanzado): ?>
                <button type="button" class="btn-submit btn-locked">Límite Diario Alcanzado (3/3)</button>
            <?php else: ?>
                <button type="submit" class="btn-submit">Acortar Ahora</button>
            <?php endif; ?>
        </div>
    </form>
</div>

</body>
</html>