<?php
session_start();

require_once "conexion.php";


/* =============================================
   CONSULTAR HISTORIAL AUTOMÁTICO DESDE MYSQL
   ============================================= */

$sql_historial = "SELECT
                      h.id_acceso,
                      u.nombre_completo,
                      u.usuario,
                      h.accion,
                      h.fecha_hora
                  FROM historial_accesos h
                  INNER JOIN usuarios u
                      ON h.id_usuario = u.id_usuario
                  ORDER BY h.fecha_hora DESC
                  LIMIT 20";

$resultado_historial = $conexion->query($sql_historial);

if (!$resultado_historial) {
    die("Error al consultar el historial: " . $conexion->error);
}


if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

if ((int)($_SESSION["id_rol"] ?? 0) !== 1) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso restringido | Bitnotas</title>

        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

        <style>
            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                background: #f4f7f6;
                font-family: Arial, sans-serif;
                color: #333;
            }

            .tarjeta-acceso {
                width: 100%;
                max-width: 500px;
                padding: 40px;
                border-radius: 20px;
                background: white;
                box-shadow: 0 10px 35px #0002;
                text-align: center;
            }

            .icono-acceso {
                font-size: 60px;
                color: #d9534f;
                margin-bottom: 20px;
            }

            .tarjeta-acceso h1 {
                color: #2e7d32;
                margin-bottom: 15px;
            }

            .tarjeta-acceso p {
                line-height: 1.6;
                margin-bottom: 25px;
            }

            .boton-acceso {
                display: inline-block;
                margin: 8px;
                padding: 12px 22px;
                border-radius: 8px;
                text-decoration: none;
            }

            .boton-inicio {
                background: #198754;
                color: white;
            }

            .boton-salir {
                background: #eee;
                color: #333;
            }
        </style>
    </head>
    <body>
        <section class="tarjeta-acceso">
            <i class="fa-solid fa-lock icono-acceso"></i>

            <h1>Acceso restringido</h1>

            <p>
                No tienes permisos para acceder al prototipo de Bitnotas.
                Esta sección está disponible únicamente para administradores.
            </p>

            <a href="index.php" class="boton-acceso boton-inicio">
                <i class="fa-solid fa-house"></i> Volver al inicio
            </a>

            <a href="cerrar_sesion.php" class="boton-acceso boton-salir">
                Cerrar sesión
            </a>
        </section>
    </body>
    </html>
    <?php
    exit;
}
?>
<?php
/* =============================================
   CONTACTOS RECIBIDOS DESDE EL FORMULARIO
   (llegan aquí solo los administradores)
   ============================================= */
$contactos = [];
$total_contactos = 0;
$error_contactos = "";

$res_total = $conexion->query("SELECT COUNT(*) AS total FROM contactos");
if ($res_total) {
    $total_contactos = (int)$res_total->fetch_assoc()["total"];
}

$res_contactos = $conexion->query(
    "SELECT id, correo, telefono, direccion, fecha
     FROM contactos
     ORDER BY fecha DESC, id DESC
     LIMIT 200"
);

if ($res_contactos) {
    while ($fila_contacto = $res_contactos->fetch_assoc()) {
        $contactos[] = $fila_contacto;
    }
} else {
    $error_contactos = "No se pudo consultar la tabla de contactos.";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bitnotes — Panel de Administración</title>

    <style>
        /* ================= ESTILOS GENERALES Y BASE ================= */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f7f6;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ================= HEADER PRINCIPAL ================= */
        .site-header {
            background-color: #2e7d32;
            color: white;
            padding: 15px 0;
        }

        .site-header .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .brand {
            font-size: 24px;
        }

        .tag {
            font-size: 12px;
            opacity: 0.9;
        }

        .main-nav {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .main-nav a button {
            background: #1b5e20;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s ease;
        }

        .main-nav a button:hover {
            background: #003300;
        }

        /* ================= LAYOUT Y SIDEBAR DEL PANEL ================= */
        .admin-layout {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 120px);
        }

        .sidebar {
            width: 250px;
            background-color: #2e7d32;
            color: white;
            padding: 20px;
            flex-shrink: 0;
        }

        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 15px;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #1b5e20;
            font-weight: bold;
        }

        .main-content {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
        }

        .header-panel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .selector-usuario {
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #2e7d32;
            font-weight: bold;
            color: #2e7d32;
            background: #f0fdf4;
            cursor: pointer;
        }

        /* ================= TARJETAS Y DASHBOARD ================= */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card-dash {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .card-dash h3 {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
        }

        .card-dash p {
            font-size: 24px;
            font-weight: bold;
            color: #2e7d32;
        }

        /* ================= TABLAS Y SECCIONES ================= */
        .section-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .section-box h3 {
            color: #2e7d32;
        }

        .section-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .input-busqueda {
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 13px;
            width: 220px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f8f9fa;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        /* ================= BOTONES DE ACCIÓN ================= */
        .btn {
            background-color: #2e7d32;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-warning {
            background-color: #f39c12;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .btn-secondary {
            background-color: #7f8c8d;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 4px;
            cursor: pointer;
        }

        /* ================= MODAL INTERNO ================= */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .modal-content h3 {
            margin-bottom: 15px;
            color: #2e7d32;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        /* ================= FOOTER ================= */
        .site-footer {
            background: #222;
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: auto;
        }

        /* ================= CONTACTOS RECIBIDOS ================= */
        .tabla-scroll { overflow-x: auto; }

        .btn-enlace {
            display: inline-block;
            text-decoration: none;
            background-color: #2e7d32;
            color: white;
            padding: 6px 10px;
            border-radius: 3px;
            font-size: 13px;
            margin: 2px 4px 2px 0;
            white-space: nowrap;
        }

        .btn-enlace:hover { opacity: 0.9; }
        .btn-enlace.azul { background-color: #1976d2; }

        .aviso-tabla {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 4px;
            color: #666;
            font-size: 13px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <!-- Header Navegación Principal -->
    <header class="site-header">
        <div class="container">
            <div>
                <h1 class="brand">Bitnotas</h1>
                <p class="tag">digitalizamos tus ideas y organizamos tu futuro</p>
            </div>

            <nav class="main-nav">
                <a href="index.php"><button>Inicio</button></a>
                <a href="2_quienes_somos.html"><button>Quiénes somos</button></a>
                <a href="3_servicios.html"><button>Servicios</button></a>
                <a href="4_noticias.html"><button>Noticias</button></a>
                <a href="5_galeria.html"><button>Galería</button></a>
                <a href="6_contacto.php"><button>Contacto</button></a>
                <a href="7_politicas.html"><button>Políticas</button></a>
                <a href="8_prototipo.php"><button style="background: #003300;">Prototipo</button></a>
            </nav>
        </div>
    </header>

    <!-- Layout del Panel de Administración -->
    <div class="admin-layout">

        <!-- Sidebar Lateral -->
        <aside class="sidebar">
            <h2>🌱 Bitnote</h2>
            <ul>
                <li><a href="#dashboard" class="nav-link active">📊 Inicio / Dashboard</a></li>
                <li><a href="#personal" class="nav-link">👥 Personal y Usuarios</a></li>
                <li><a href="#herramientas" class="nav-link">🛠️ Herramientas</a></li>
                <li><a href="#actividades" class="nav-link">📅 Horarios y Actividades</a></li>
                <li><a href="#contactos" class="nav-link">📬 Contactos Recibidos</a></li>
                <li><a href="#estadisticas" class="nav-link">📈 Estadísticas Automáticas</a></li>
                <li><a href="cerrar_sesion.php" style="margin-top: 40px; background-color: #c62828;">🚪 Cerrar Sesión</a></li>
            </ul>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-content">
            <div class="header-panel">
                <h2>Panel Administrativo de la Huerta Escolar</h2>
                <div>
                    <label style="font-size: 13px; font-weight: bold; margin-right: 5px;">Usuario Activo:</label>
                    <select id="selector-usuario-activo" class="selector-usuario" onchange="cambiarUsuarioActivo(this.value)">
                        <option value="Richard Guerra Aguirre">Richard Guerra Aguirre</option>
                        <option value="Santiago Gallego Taborda">Santiago Gallego Taborda</option>
                        <option value="Santiago Marquez Villada">Santiago Marquez Villada</option>
                        <option value="Gloria Lora">Gloria Lora (Docente)</option>
                    </select>
                </div>
            </div>

            <!-- Dashboard Métricas -->
            <div class="cards-grid" id="dashboard">
                <div class="card-dash">
                    <h3>Participantes Registrados</h3>
                    <p id="cant-personas">0</p>
                </div>
                <div class="card-dash">
                    <h3>Herramientas Disponibles</h3>
                    <p id="cant-herramientas">0</p>
                </div>
                <div class="card-dash">
                    <h3>Actividades Semanales</h3>
                    <p id="cant-actividades">0 Pendientes</p>
                </div>
                <div class="card-dash">
                    <h3>Contactos Recibidos</h3>
                    <p><?= (int)$total_contactos ?></p>
                </div>
                <div class="card-dash">
                    <h3>Estado General</h3>
                    <p>Óptimo</p>
                </div>
            </div> 

                        <!-- Módulo Personal -->
            <div class="section-box" id="personal">
                <div class="section-top">
                    <h3>👥 Registro de Participantes (Estudiantes / Docentes)</h3>
                    <div>
                        <input type="text" class="input-busqueda" placeholder="🔍 Buscar participante..." onkeyup="filtrarTabla('tabla-personal', this.value)">
                        <button class="btn" onclick="abrirModal('personal')">+ Registrar Persona</button>
                    </div>
                </div>
                <table id="tabla-personal">
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Rol</th>
                            <th>Grado / Documento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dinámico con JS -->
                    </tbody>
                </table>
            </div>

            <!-- Módulo Herramientas -->
            <div class="section-box" id="herramientas">
                <div class="section-top">
                    <h3>🛠️ Control de Herramientas</h3>
                    <div>
                        <input type="text" class="input-busqueda" placeholder="🔍 Buscar herramienta..." onkeyup="filtrarTabla('tabla-herramientas', this.value)">
                        <button class="btn" onclick="abrirModal('herramientas')">+ Agregar Herramienta</button>
                    </div>
                </div>
               
<table id="tabla-herramientas">
    <thead>
        <tr>
            <th>Nombre de Herramienta</th>
            <th>Cantidad Total</th>
            <th>Disponibles</th>
            <th>Estado de Conservación</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sqlHerramientas = "SELECT id_herramientas, nombre, tipo, estado
                            FROM herramientas";
        $resultadoHerramientas = $conexion->query($sqlHerramientas);

        if ($resultadoHerramientas && $resultadoHerramientas->num_rows > 0):
            while ($herramienta = $resultadoHerramientas->fetch_assoc()):
        ?>
            <tr>
                <td><?= htmlspecialchars($herramienta["nombre"] ?? "") ?></td>
                <td>—</td>
                <td>—</td>
                <td><?= htmlspecialchars($herramienta["estado"] ?? "") ?></td>
                <td>Registrada en MySQL</td>
            </tr>
        <?php
            endwhile;
        else:
        ?>
            <tr>
                <td colspan="5">No hay herramientas registradas.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
            </div>

            <!-- Módulo Actividades -->
            <div class="section-box" id="actividades">
                <div class="section-top">
                    <h3>📅 Actividades y Horarios de Cuidado</h3>
                    <div>
                        <input type="text" class="input-busqueda" placeholder="🔍 Buscar actividad..." onkeyup="filtrarTabla('tabla-actividades', this.value)">
                        <button class="btn" onclick="abrirModal('actividades')">+ Programar Actividad</button>
                    </div>
                </div>
                <table id="tabla-actividades">
                    <thead>
                        <tr>
                            <th>Actividad</th>
                            <th>Responsable</th>
                            <th>Día / Horario</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dinámico con JS -->
                    </tbody>
                </table>
            </div>

            <!-- ================= CONTACTOS RECIBIDOS (desde MySQL) ================= -->
            <div class="section-box" id="contactos">
                <div class="section-top">
                    <h3>📬 Contactos Recibidos (formulario de la página)</h3>
                    <div>
                        <input type="text" class="input-busqueda" placeholder="🔍 Buscar contacto..." onkeyup="filtrarTabla('tabla-contactos', this.value)">
                    </div>
                </div>

                <div class="tabla-scroll">
                    <table id="tabla-contactos">
                        <thead>
                            <tr>
                                <th>Fecha y hora</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Dirección</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($error_contactos !== ""): ?>
                                <tr>
                                    <td colspan="5"><?= htmlspecialchars($error_contactos) ?></td>
                                </tr>
                            <?php elseif (empty($contactos)): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; color:#777;">
                                        Todavía no hay contactos registrados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contactos as $c): ?>
                                    <?php $tel_limpio = preg_replace('/[^0-9+]/', '', $c["telefono"]); ?>
                                    <tr>
                                        <td><?= htmlspecialchars(date("d/m/Y, g:i a", strtotime($c["fecha"]))) ?></td>
                                        <td><?= htmlspecialchars($c["correo"], ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($c["telefono"], ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($c["direccion"], ENT_QUOTES, "UTF-8") ?></td>
                                        <td>
                                            <a class="btn-enlace" href="mailto:<?= htmlspecialchars($c["correo"], ENT_QUOTES, "UTF-8") ?>">✉️ Responder</a>
                                            <a class="btn-enlace azul" href="tel:<?= htmlspecialchars($tel_limpio, ENT_QUOTES, "UTF-8") ?>">📞 Llamar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_contactos > count($contactos)): ?>
                    <p class="aviso-tabla">
                        Mostrando los <?= count($contactos) ?> contactos más recientes de <?= $total_contactos ?> en total.
                    </p>
                <?php endif; ?>
            </div>

            <!-- ================= SECCIÓN AUTOMÁTICA: CUADROS ESTADÍSTICOS ================= -->
            <div class="section-box" id="estadisticas">
                <h3 style="margin-bottom: 20px;">📈 Cuadro Estadístico Automatizado</h3>

                <div class="stats-grid">
                    <!-- Cuadro 1: Conteo Automático de Accesos -->
                    <div>
                        <h4 style="margin-bottom: 12px; color: #555;">🔑 Inicios de Sesión por Usuario</h4>
                        <table id="tabla-accesos">
                            <thead>
                                <tr>
                                    <th>Usuario / Persona</th>
                                    <th>Rol</th>
                                    <th>Ingresos Registrados</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- JS automatizado -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Cuadro 2: Conteo de Acciones General -->
                    <div>
                        <h4 style="margin-bottom: 12px; color: #555;">⚡ Total de Acciones en el Sistema</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Acción</th>
                                    <th>Total Realizadas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Registros Realizados</td>
                                    <td><strong id="stat-creaciones" style="color: #2e7d32;">0</strong></td>
                                </tr>
                                <tr>
                                    <td>Ediciones Realizadas</td>
                                    <td><strong id="stat-ediciones" style="color: #f39c12;">0</strong></td>
                                </tr>
                                <tr>
                                    <td>Eliminaciones Realizadas</td>
                                    <td><strong id="stat-eliminaciones" style="color: #e74c3c;">0</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Historial en vivo -->
                <div style="margin-top: 25px;">
                    <h4 style="margin-bottom: 12px; color: #555;">📋 Historial Automático de Acciones Recientes</h4>
                    <table id="tabla-historial">
    <thead>
        <tr>
            <th>Fecha y Hora</th>
            <th>Usuario</th>
            <th>Acción</th>
            <th>Módulo</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($fila = $resultado_historial->fetch_assoc()) { ?>
            <tr>
                <td>
                    <?= htmlspecialchars($fila["fecha_hora"]) ?>
                </td>
                <td>
                    <?= htmlspecialchars($fila["nombre_completo"]) ?>
                </td>
                <td>
                    <?= htmlspecialchars($fila["accion"] === "Inicio de sesión"
                        ? "Ingreso al prototipo"
                        : $fila["accion"]) ?>
                </td>
                <td>
                    <?= htmlspecialchars($fila["accion"] === "Inicio de sesión"
                        ? "ACCESO"
                        : "SISTEMA") ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
                </div>
            </div>

        </main>
    </div>

    <!-- Modal Formulario Interno -->
    <div class="modal-overlay" id="modalFormulario">
        <div class="modal-content">
            <h3 id="modalTitulo">Registro</h3>
            <form id="formInterno" onsubmit="guardarFormulario(event)">
                <div id="modalCampos"></div>
                <div class="modal-buttons">
                    <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="site-footer">
        <p>© 2026 Bitnotas — Digitalizamos tus ideas</p>
    </footer>

    <!-- Lógica Interactiva y Dinámica en JS -->
    <script>
        let moduloActual = '';
        let idEditando = null;

        // Datos iniciales persistentes en localStorage
        let datosPersonal = JSON.parse(localStorage.getItem('bitnote_personal')) || [
            { id: 1, nombre: 'Richard Guerra Aguirre', rol: 'Estudiante Encargado', doc: '1015075179', estado: 'Activo' },
            { id: 2, nombre: 'Gloria Lora', rol: 'Docente IERU', doc: 'Docente', estado: 'Activo' }
        ];

        let datosHerramientas = JSON.parse(localStorage.getItem('bitnote_herramientas')) || [
            { id: 1, nombre: 'Palas de Jardinería', total: 10, disp: 8, estado: 'Buen estado' },
            { id: 2, nombre: 'Regaderas de 5L', total: 5, disp: 5, estado: 'Excelente' }
        ];

        let datosActividades = JSON.parse(localStorage.getItem('bitnote_actividades')) || [
            { id: 1, actividad: 'Riego y Fertilización', resp: 'Santiago Gallego', horario: 'Lunes - 8:00 AM', estado: 'Completado' },
            { id: 2, actividad: 'Control de Plagas y Deshierbe', resp: 'Santiago Marquez', horario: 'Jueves - 10:00 AM', estado: 'Pendiente' }
        ];

        let accesosUsuarios = JSON.parse(localStorage.getItem('bitnote_accesos')) || {
            'Richard Guerra Aguirre': { rol: 'Estudiante Encargado', accesos: 5 },
            'Santiago Gallego Taborda': { rol: 'Estudiante Encargado', accesos: 3 },
            'Santiago Marquez Villada': { rol: 'Estudiante Encargado', accesos: 2 },
            'Gloria Lora': { rol: 'Docente IERU', accesos: 1 }
        };

        let contadoresAcciones = JSON.parse(localStorage.getItem('bitnote_contadores')) || { creaciones: 2, ediciones: 0, eliminaciones: 0 };
        let historialAcciones = JSON.parse(localStorage.getItem('bitnote_historial')) || [];
        let usuarioSesionActual = localStorage.getItem('bitnote_usuario_activo') || 'Richard Guerra Aguirre';

        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('selector-usuario-activo').value = usuarioSesionActual;

            // Registrar automaticamente cada entrada al prototipo en el historial.
            if (!accesosUsuarios[usuarioSesionActual]) {
                accesosUsuarios[usuarioSesionActual] = { rol: 'Usuario', accesos: 0 };
            }
            accesosUsuarios[usuarioSesionActual].accesos++;
            registrarHistorial('Ingreso al prototipo', 'ACCESO');
            localStorage.setItem('bitnote_accesos', JSON.stringify(accesosUsuarios));
            guardarDatosLocal();
            renderizarTodasLasTablas();
        });

        function cambiarUsuarioActivo(nuevoUsuario) {
            usuarioSesionActual = nuevoUsuario;
            localStorage.setItem('bitnote_usuario_activo', nuevoUsuario);

            if (!accesosUsuarios[nuevoUsuario]) {
                accesosUsuarios[nuevoUsuario] = { rol: 'Usuario', accesos: 0 };
            }
            accesosUsuarios[nuevoUsuario].accesos++;
            registrarHistorial('Cambio de usuario activo', 'ACCESO');
            localStorage.setItem('bitnote_accesos', JSON.stringify(accesosUsuarios));
            guardarDatosLocal();
            renderizarTodasLasTablas();
        }

        function guardarDatosLocal() {
            localStorage.setItem('bitnote_personal', JSON.stringify(datosPersonal));
            localStorage.setItem('bitnote_herramientas', JSON.stringify(datosHerramientas));
            localStorage.setItem('bitnote_actividades', JSON.stringify(datosActividades));
            localStorage.setItem('bitnote_contadores', JSON.stringify(contadoresAcciones));
            localStorage.setItem('bitnote_historial', JSON.stringify(historialAcciones));
        }
        
                function renderizarTodasLasTablas() {
            // Personal
            const tbodyP = document.querySelector('#tabla-personal tbody');
            tbodyP.innerHTML = '';
            datosPersonal.forEach(item => {
                tbodyP.innerHTML += `
                    <tr>
                        <td>${item.nombre}</td>
                        <td>${item.rol}</td>
                        <td>${item.doc}</td>
                        <td><span style="color: ${item.estado === 'Activo' ? 'green' : 'red'}; font-weight: bold;">${item.estado}</span></td>
                        <td>
                            <button class="btn-warning" onclick="prepararEdicion('personal', ${item.id})">Editar</button>
                            <button class="btn-danger" onclick="eliminarRegistro('personal', ${item.id})">Eliminar</button>
                        </td>
                    </tr>
                `;
            });

            // Herramientas
            const tbodyH = document.querySelector('#tabla-herramientas tbody');
            tbodyH.innerHTML = '';
            datosHerramientas.forEach(item => {
                tbodyH.innerHTML += `
                    <tr>
                        <td>${item.nombre}</td>
                        <td>${item.total}</td>
                        <td>${item.disp}</td>
                        <td>${item.estado}</td>
                        <td>
                            <button class="btn-warning" onclick="prepararEdicion('herramientas', ${item.id})">Editar</button>
                            <button class="btn-danger" onclick="eliminarRegistro('herramientas', ${item.id})">Eliminar</button>
                        </td>
                    </tr>
                `;
            });

            // Actividades
            const tbodyA = document.querySelector('#tabla-actividades tbody');
            tbodyA.innerHTML = '';
            datosActividades.forEach(item => {
                tbodyA.innerHTML += `
                    <tr>
                        <td>${item.actividad}</td>
                        <td>${item.resp}</td>
                        <td>${item.horario}</td>
                        <td><span style="color: ${item.estado === 'Completado' ? 'green' : 'orange'}; font-weight: bold;">${item.estado}</span></td>
                        <td>
                            <button class="btn-warning" onclick="prepararEdicion('actividades', ${item.id})">Editar</button>
                            <button class="btn-danger" onclick="eliminarRegistro('actividades', ${item.id})">Eliminar</button>
                        </td>
                    </tr>
                `;
            });

            // Dashboard
            document.getElementById('cant-personas').textContent = datosPersonal.length;
            document.getElementById('cant-herramientas').textContent = datosHerramientas.length;
            document.getElementById('cant-actividades').textContent =
                datosActividades.filter(a => a.estado === 'Pendiente').length + " Pendientes";

            // Estadísticas de accesos
            const tbodyAcc = document.querySelector('#tabla-accesos tbody');
            tbodyAcc.innerHTML = '';

            for (let p in accesosUsuarios) {
                tbodyAcc.innerHTML += `
                    <tr>
                        <td>${p}</td>
                        <td>${accesosUsuarios[p].rol}</td>
                        <td><strong style="color: #2e7d32;">${accesosUsuarios[p].accesos} veces</strong></td>
                    </tr>
                `;
            }

            // Contadores de acciones
            document.getElementById('stat-creaciones').textContent = contadoresAcciones.creaciones;
            document.getElementById('stat-ediciones').textContent = contadoresAcciones.ediciones;
            document.getElementById('stat-eliminaciones').textContent = contadoresAcciones.eliminaciones;

            // Historial automático
            const tbodyHist = document.querySelector('#tabla-historial tbody');
            tbodyHist.innerHTML = '';

            if (historialAcciones.length === 0) {
                tbodyHist.innerHTML = `
                    <tr>
                        <td colspan="4" style="text-align:center; color:#777;">
                            Sin acciones registradas
                        </td>
                    </tr>
                `;
            } else {
                historialAcciones.forEach(item => {
                    tbodyHist.innerHTML += `
                        <tr>
                            <td>${item.fecha}</td>
                            <td>${item.usuario}</td>
                            <td><strong>${item.accion}</strong></td>
                            <td>${item.modulo}</td>
                        </tr>
                    `;
                });
            }
        }

        // Buscar información en las tablas
        function filtrarTabla(idTabla, texto) {
            const filas = document.querySelectorAll(`#${idTabla} tbody tr`);

            filas.forEach(fila => {
                fila.style.display = fila.innerText.toLowerCase().includes(texto.toLowerCase())
                    ? ''
                    : 'none';
            });
        }

        // Abrir formulario para registrar información
        function abrirModal(modulo) {
            moduloActual = modulo;
            idEditando = null;

            const campos = document.getElementById('modalCampos');
            const titulo = document.getElementById('modalTitulo');

            if (modulo === 'personal') {
                titulo.textContent = 'Registrar Participante';
                campos.innerHTML = `
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" id="c1" required>
                    </div>
                    <div class="form-group">
                        <label>Rol</label>
                        <input type="text" id="c2" required>
                    </div>
                    <div class="form-group">
                        <label>Grado / Documento</label>
                        <input type="text" id="c3" required>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select id="c4">
                            <option>Activo</option>
                            <option>Inactivo</option>
                        </select>
                    </div>
                `;
            } else if (modulo === 'herramientas') {
                titulo.textContent = 'Agregar Herramienta';
                campos.innerHTML = `
                    <div class="form-group">
                        <label>Nombre Herramienta</label>
                        <input type="text" id="c1" required>
                    </div>
                    <div class="form-group">
                        <label>Cantidad Total</label>
                        <input type="number" id="c2" required>
                    </div>
                    <div class="form-group">
                        <label>Disponibles</label>
                        <input type="number" id="c3" required>
                    </div>
                    <div class="form-group">
                        <label>Estado Conservación</label>
                        <input type="text" id="c4" required>
                    </div>
                `;
            } else if (modulo === 'actividades') {
                titulo.textContent = 'Programar Actividad';
                campos.innerHTML = `
                    <div class="form-group">
                        <label>Actividad</label>
                        <input type="text" id="c1" required>
                    </div>
                    <div class="form-group">
                        <label>Responsable</label>
                        <input type="text" id="c2" required>
                    </div>
                    <div class="form-group">
                        <label>Horario</label>
                        <input type="text" id="c3" required>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select id="c4">
                            <option>Pendiente</option>
                            <option>Completado</option>
                        </select>
                    </div>
                `;
            }

            document.getElementById('modalFormulario').style.display = 'flex';
        }

        // Preparar la edición de un registro
        function prepararEdicion(modulo, id) {
            abrirModal(modulo);
            idEditando = id;

            document.getElementById('modalTitulo').textContent = 'Editar Registro';

            if (modulo === 'personal') {
                const item = datosPersonal.find(x => x.id === id);
                document.getElementById('c1').value = item.nombre;
                document.getElementById('c2').value = item.rol;
                document.getElementById('c3').value = item.doc;
                document.getElementById('c4').value = item.estado;
            } else if (modulo === 'herramientas') {
                const item = datosHerramientas.find(x => x.id === id);
                document.getElementById('c1').value = item.nombre;
                document.getElementById('c2').value = item.total;
                document.getElementById('c3').value = item.disp;
                document.getElementById('c4').value = item.estado;
            } else if (modulo === 'actividades') {
                const item = datosActividades.find(x => x.id === id);
                document.getElementById('c1').value = item.actividad;
                document.getElementById('c2').value = item.resp;
                document.getElementById('c3').value = item.horario;
                document.getElementById('c4').value = item.estado;
            }
        }

        // Guardar los registros nuevos o las modificaciones
        function guardarFormulario(e) {
            e.preventDefault();

            const v1 = document.getElementById('c1').value;
            const v2 = document.getElementById('c2').value;
            const v3 = document.getElementById('c3').value;
            const v4 = document.getElementById('c4').value;

            if (idEditando) {
                if (moduloActual === 'personal') {
                    const idx = datosPersonal.findIndex(x => x.id === idEditando);
                    datosPersonal[idx] = {
                        id: idEditando,
                        nombre: v1,
                        rol: v2,
                        doc: v3,
                        estado: v4
                    };
                } else if (moduloActual === 'herramientas') {
                    const idx = datosHerramientas.findIndex(x => x.id === idEditando);
                    datosHerramientas[idx] = {
                        id: idEditando,
                        nombre: v1,
                        total: v2,
                        disp: v3,
                        estado: v4
                    };
                } else if (moduloActual === 'actividades') {
                    const idx = datosActividades.findIndex(x => x.id === idEditando);
                    datosActividades[idx] = {
                        id: idEditando,
                        actividad: v1,
                        resp: v2,
                        horario: v3,
                        estado: v4
                    };
                }

                contadoresAcciones.ediciones++;
                registrarHistorial('Edición', moduloActual.toUpperCase());

            } else {
                const nuevoId = Date.now();

                if (moduloActual === 'personal') {
                    datosPersonal.push({
                        id: nuevoId,
                        nombre: v1,
                        rol: v2,
                        doc: v3,
                        estado: v4
                    });
                } else if (moduloActual === 'herramientas') {
                    datosHerramientas.push({
                        id: nuevoId,
                        nombre: v1,
                        total: v2,
                        disp: v3,
                        estado: v4
                    });
                } else if (moduloActual === 'actividades') {
                    datosActividades.push({
                        id: nuevoId,
                        actividad: v1,
                        resp: v2,
                        horario: v3,
                        estado: v4
                    });
                }

                contadoresAcciones.creaciones++;

                if (moduloActual === 'personal') {
                    // Mostrar en el historial el nombre de la persona que acaba de registrarse.
                    registrarHistorial('Registro de participante: ' + v1, 'PERSONAL');
                } else {
                    registrarHistorial('Nuevo Registro', moduloActual.toUpperCase());
                }
            }

            guardarDatosLocal();
            renderizarTodasLasTablas();
            cerrarModal();
        }

        // Eliminar registros
        function eliminarRegistro(modulo, id) {
            if (modulo === 'personal') {
                datosPersonal = datosPersonal.filter(x => x.id !== id);
            }

            if (modulo === 'herramientas') {
                datosHerramientas = datosHerramientas.filter(x => x.id !== id);
            }

            if (modulo === 'actividades') {
                datosActividades = datosActividades.filter(x => x.id !== id);
            }

            contadoresAcciones.eliminaciones++;
            registrarHistorial('Eliminación', modulo.toUpperCase());

            guardarDatosLocal();
            renderizarTodasLasTablas();
        }

        // Guardar automáticamente una acción en el historial
        function registrarHistorial(accion, modulo) {
            historialAcciones.unshift({
                fecha: new Date().toLocaleString(),
                usuario: usuarioSesionActual,
                accion: accion,
                modulo: modulo
            });

            // Mantener solamente las 8 acciones más recientes
            if (historialAcciones.length > 8) {
                historialAcciones.pop();
            }
        }

        // Cerrar el formulario
        function cerrarModal() {
            document.getElementById('modalFormulario').style.display = 'none';
        }
    </script>
</body>

</html>