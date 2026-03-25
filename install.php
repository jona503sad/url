<?php
require_once 'config/config.php';

echo "<h2>⚙️ Instalador del Sistema</h2>";

try {
    // 1. Conexión inicial al servidor MySQL
    $pdo = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Crear la Base de Datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `" . DB_NAME . "`;");
    echo "✅ Base de datos '" . DB_NAME . "' verificada/creada.<br>";

    // 3. Crear Tabla de USUARIOS (Asegurando que existan los roles)
    $sql_users = "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('admin', 'colaborador', 'pro', 'free', 'demo') DEFAULT 'free',
        `status` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
    $pdo->exec($sql_users);
    echo "✅ Tabla 'users' lista.<br>";

    // 4. Crear Tabla de LINKS (Con soporte para límites y opciones PRO)
    $sql_links = "CREATE TABLE IF NOT EXISTS `links` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `url_larga` TEXT NOT NULL,
        `alias` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) DEFAULT NULL,
        `expiracion` DATE DEFAULT NULL,
        `clicks` INT DEFAULT 0,
        `status` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    $pdo->exec($sql_links);
    echo "✅ Tabla 'links' lista (con soporte para límites diarios).<br>";

    // 5. Crear un Administrador por defecto (si no existe)
    $check_admin = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $check_admin->execute();
    if (!$check_admin->fetch()) {
        $pass_admin = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)")
            ->execute(['Admin General', 'admin@correo.com', $pass_admin, 'admin', 1]);
        echo "👤 <b>Usuario Admin creado:</b> admin@correo.com / admin123<br>";
    }

    echo "<br><div style='color: green; font-weight: bold;'>🚀 ¡Instalación completada con éxito!</div>";
    echo "<a href='login.php'>Ir al Login</a>";

} catch (PDOException $e) {
    die("<br><div style='color: red;'>❌ Error en la instalación: " . $e->getMessage() . "</div>");
}
?>