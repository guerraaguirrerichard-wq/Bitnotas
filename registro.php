<?php
session_start();
require_once 'conexion.php';

$mensaje = "";
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $clave = trim($_POST['clave'] ?? '');

    if (!empty($nombre_completo) && !empty($usuario) && !empty($clave)) {
        
        // 1. Verificar si el correo/usuario ya existe
        $stmt_check = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ?");
        $stmt_check->bind_param("s", $usuario);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $mensaje = "El correo o usuario ya se encuentra registrado.";
        } else {
            // 2. Registrar el nuevo usuario como usuario normal
$id_rol = 2;
$activo = 1;
$fecha_registro = date('Y-m-d');

            $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, clave, nombre_completo, id_rol, activo, fecha_registro) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiss", $usuario, $clave, $nombre_completo, $id_rol, $activo, $fecha_registro);

            if ($stmt->execute()) {
                $exito = true;
                $mensaje = "¡Registro exitoso! Ya puedes iniciar sesión.";
            } else {
                $mensaje = "Error al registrar: " . $conexion->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    } else {
        $mensaje = "Por favor completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Bitnotas — Registro de Usuario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="container main">
        <section class="card" style="max-width: 450px; margin: 40px auto;">
            <h2>Crear Cuenta</h2>
            
            <?php if (!empty($mensaje)): ?>
                <p style="color: <?php echo $exito ? 'green' : 'red'; ?>; font-weight: bold;">
                    <?php echo htmlspecialchars($mensaje); ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="registro.php">
                <div class="campo">
                    <label for="nombre_completo">Nombre Completo</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" placeholder="Ej: Maria Lopez" required>
                </div>

                <div class="campo">
                    <label for="usuario">Correo / Usuario</label>
                    <input type="email" id="usuario" name="usuario" placeholder="maria@ejemplo.com" required>
                </div>

                <div class="campo">
                    <label for="clave">Contraseña</label>
                    <input type="password" id="clave" name="clave" placeholder="••••••" required>
                </div>

                <button type="submit" style="margin-top: 15px;">Registrarse</button>
            </form>

            <p style="margin-top: 15px; font-size: 0.9em; text-align: center;">
                ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
            </p>
        </section>
    </main>
</body>
</html>