
<?php
session_start();
require_once "conexion.php";

// Verificar que el usuario haya iniciado sesion
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

// Verificar que el usuario sea administrador
if ((int)($_SESSION["id_rol"] ?? 0) !== 1) {
    http_response_code(403);
    exit("Acceso denegado. Solo los administradores pueden ver este panel.");
}

// Consultar el historial de accesos
$sql = "SELECT
            h.id_acceso,
            u.nombre_completo,
            u.usuario,
            h.accion,
            h.fecha_hora
        FROM historial_accesos h
        INNER JOIN usuarios u
            ON h.id_usuario = u.id_usuario
        ORDER BY h.fecha_hora DESC
        LIMIT 100";

$resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrador - Bitnotas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f1;
            margin: 0;
            padding: 25px;
            color: #26352b;
        }

        .contenedor {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px #00000012;
        }

        h1 {
            color: #247344;
        }

        .tabla-contenedor {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #247344;
            color: white;
        }

        tr:hover {
            background: #f3f8f4;
        }

        .boton {
            display: inline-block;
            background: #247344;
            color: white;
            padding: 10px 16px;
            border-radius: 7px;
            text-decoration: none;
            margin-top: 15px;
        }

        .boton:hover {
            background: #18552f;
        }
    </style>
</head>
<body>
    <main class="contenedor">
        <h1>Panel de administrador</h1>
        <p>Historial de accesos de Bitnotas</p>

        <a class="boton" href="index.php">Volver al inicio</a>

        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        date("d/m/Y, g:i:s a",
                                        strtotime($fila["fecha_hora"]))
                                    ) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($fila["nombre_completo"]) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($fila["usuario"]) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($fila["accion"]) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No hay accesos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
