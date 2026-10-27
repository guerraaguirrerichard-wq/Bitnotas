<?php
session_start();
require_once "conexion.php";

/* Escapa texto para mostrarlo seguro en HTML */
function e($t) { return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8'); }

/* Token de seguridad del formulario (CSRF) */
if (empty($_SESSION['csrf_contacto'])) {
    $_SESSION['csrf_contacto'] = bin2hex(random_bytes(32));
}

$valores = ['correo' => '', 'telefono' => '', 'direccion' => ''];
$errores = [];
$mensaje = '';
$exito   = false;

/* Mensaje que quedó guardado tras un envío correcto */
if (isset($_SESSION['flash_contacto'])) {
    $mensaje = $_SESSION['flash_contacto'];
    $exito   = true;
    unset($_SESSION['flash_contacto']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf'] ?? '';

    if (!hash_equals($_SESSION['csrf_contacto'], $token)) {
        $mensaje = 'La sesión del formulario expiró. Recarga la página e intenta de nuevo.';

    } elseif (!empty($_POST['sitio_web'])) {
        /* Campo trampa para robots: si viene lleno, se ignora en silencio */
        $_SESSION['flash_contacto'] = '¡Gracias! Tus datos fueron registrados correctamente.';
        header('Location: 6_contacto.php#formulario');
        exit;

    } elseif (isset($_SESSION['ultimo_contacto']) && time() - $_SESSION['ultimo_contacto'] < 10) {
        $mensaje = 'Espera unos segundos antes de enviar otro mensaje.';

    } else {
        $valores['correo']    = trim($_POST['correo'] ?? '');
        $valores['telefono']  = trim($_POST['telefono'] ?? '');
        $valores['direccion'] = trim($_POST['direccion'] ?? '');

        /* ---- Validaciones en el servidor ---- */
        if ($valores['correo'] === '' || !filter_var($valores['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = '⚠️ Ingresa un correo válido';
        } elseif (mb_strlen($valores['correo']) > 120) {
            $errores['correo'] = '⚠️ El correo es demasiado largo';
        }

        if (!preg_match('/^[0-9+\s()-]{7,20}$/', $valores['telefono'])) {
            $errores['telefono'] = '⚠️ Ingresa un teléfono válido';
        }

        if (mb_strlen($valores['direccion']) < 5) {
            $errores['direccion'] = '⚠️ Ingresa una dirección válida';
        } elseif (mb_strlen($valores['direccion']) > 200) {
            $errores['direccion'] = '⚠️ La dirección es demasiado larga';
        }

        /* ---- Guardar en la base de datos (consulta preparada) ---- */
        if (empty($errores)) {
            $stmt = $conexion->prepare(
                "INSERT INTO contactos (correo, telefono, direccion) VALUES (?, ?, ?)"
            );

            if ($stmt && $stmt->bind_param("sss", $valores['correo'], $valores['telefono'], $valores['direccion']) && $stmt->execute()) {
                $stmt->close();
                $_SESSION['ultimo_contacto'] = time();
                $_SESSION['flash_contacto']  = '¡Gracias! Tus datos fueron registrados correctamente.';
                $_SESSION['csrf_contacto']   = bin2hex(random_bytes(32));

                /* Redirigir evita que al recargar se envíe dos veces */
                header('Location: 6_contacto.php#formulario');
                exit;
            }

            error_log('Contacto: ' . $conexion->error);
            $mensaje = 'Ocurrió un error al guardar. Intenta de nuevo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bitnotas — Contacto</title>
    <link rel="stylesheet" href="styles.css">

    <!-- Iconos de redes sociales (el mismo que usa index.php) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* ========== CONTACTO: DOS COLUMNAS ========== */
        .ct-intro { color: var(--verde-oscuro, #075E3F); font-weight: 600; margin-bottom: 18px; }

        .ct-grid { display: grid; grid-template-columns: 1fr 1.1fr; gap: 28px; align-items: start; margin-bottom: 40px; }
        .ct-col { display: grid; gap: 18px; }

        .c-card {
            background: #fff; border: 1px solid var(--gris-borde, #D8E5DE); border-radius: 22px;
            padding: 22px; box-shadow: 0 12px 35px rgba(0, 168, 107, .12);
        }
        .c-card h3 { color: var(--verde-oscuro, #075E3F); margin-bottom: 14px; }
        .c-card p, .c-card small { color: var(--gris-texto, #374151); }

        /* ---- iconos (mismo estilo que Quiénes somos) ---- */
        .icono {
            --c: #3b82c4;
            width: 60px; height: 60px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; line-height: 1; color: var(--c);
            background: #fff; border: 4px solid var(--c);
            box-shadow: 0 8px 20px rgba(0, 94, 63, .2);
            animation: flotar 4s ease-in-out infinite;
            transition: transform .2s, box-shadow .2s;
        }
        .icono svg { width: 28px; height: 28px; display: block; }
        .ct-col .c-card:nth-child(2) .icono { animation-delay: -1.3s; }
        .ct-col .c-card:nth-child(3) .red:nth-child(2) .icono { animation-delay: -.8s; }
        .ct-col .c-card:nth-child(3) .red:nth-child(3) .icono { animation-delay: -1.6s; }
        @keyframes flotar { 0%, 100% { translate: 0 0; } 50% { translate: 0 -5px; } }

        .dato { display: flex; align-items: center; gap: 16px; }
        .dato strong { display: block; color: var(--verde-oscuro, #075E3F); }
        .dato a { color: inherit; }
        .dato a:hover { color: var(--verde-principal, #00A86B); }

        .redes { display: grid; gap: 14px; }
        .red { display: flex; align-items: center; gap: 16px; border-radius: 16px; padding: 6px; transition: background .2s; }
        .red:hover { background: var(--verde-suave, #F0FFF7); }
        .red:hover .icono { transform: scale(1.08); box-shadow: 0 0 0 6px color-mix(in srgb, var(--c) 22%, transparent), 0 8px 20px rgba(0, 94, 63, .2); }
        .red strong { display: block; color: var(--negro, #17231D); }

        /* ---- formulario ---- */
        .ct-form h3 { margin-bottom: 4px; }
        .ct-form > p { margin-bottom: 18px; }
        .campo { margin-bottom: 18px; }
        .campo label { display: block; font-weight: 700; margin-bottom: 8px; color: var(--negro, #17231D); }
        .campo-fila { display: flex; align-items: center; gap: 12px; }
        .campo .icono { width: 48px; height: 48px; font-size: 22px; border-width: 3px; animation: none; box-shadow: 0 4px 12px rgba(0, 94, 63, .18); }
        .campo .icono svg { width: 22px; height: 22px; }
        .campo input {
            flex: 1; min-width: 0; padding: 12px 14px; font-size: 15px; background: #fff;
            border: 2px solid var(--gris-borde, #D8E5DE); border-radius: 12px; transition: border-color .2s, box-shadow .2s;
        }
        .campo input:focus { outline: none; border-color: var(--c); box-shadow: 0 0 0 4px color-mix(in srgb, var(--c) 20%, transparent); }
        .campo:focus-within .icono { box-shadow: 0 0 0 5px color-mix(in srgb, var(--c) 25%, transparent); }
        .form-error { display: block; min-height: 1px; margin: 5px 0 0 60px; color: #c0392b; font-size: 13px; }

        .btn-enviar {
            width: 100%; border: none; border-radius: 12px; padding: 14px; cursor: pointer;
            font-size: 16px; font-weight: 700; color: #fff;
            background: linear-gradient(135deg, var(--verde-principal, #00A86B), var(--verde-brillante, #00C878));
            box-shadow: 0 7px 18px rgba(0, 168, 107, .25); transition: transform .2s, background .2s;
        }
        .btn-enviar:hover { transform: translateY(-2px); background: var(--verde-oscuro, #075E3F); }
        #contactoMensaje { margin-top: 14px; }

        @media (prefers-reduced-motion: reduce) { .icono { animation: none; } }

        @media (max-width: 860px) {
            .ct-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>

    <!-- ================= HEADER ================= -->
    <header class="site-header">
        <div class="container">
            <h1 class="brand">Bitnotas</h1>
            <p class="tag">digitalizamos tus ideas y organizamos tu futuro</p>

            <nav class="main-nav">
                <a href="index.php">Inicio</a>
                <a href="2_quienes_somos.html">Quiénes somos</a>
                <a href="3_servicios.html">Servicios</a>
                <a href="4_noticias.html">Noticias</a>
                <a href="5_galeria.html">Galería</a>
                <a href="6_contacto.php">Contacto</a>
                <a href="7_politicas.html">Políticas</a>
                <a href="8_prototipo.php">Prototipo</a>
            </nav>
        </div>
    </header>

    <!-- ================= CONTACTO ================= -->
    <main class="container main">

        <h2>Contacto</h2>
        <p class="ct-intro">
            Puedes comunicarte con nosotros a través de nuestros diferentes medios de contacto.
        </p>

        <div class="ct-grid">

            <!-- ============ IZQUIERDA: CONTACTOS ============ -->
            <div class="ct-col">

                <article class="c-card">
                    <h3>Dirección</h3>
                    <div class="dato">
                        <span class="icono" style="--c:#3b82c4" aria-hidden="true">✉️</span>
                        <p>Avenida Las Palmas, cerca de Palmira</p>
                    </div>
                </article>

                <article class="c-card">
                    <h3>Teléfono</h3>
                    <div class="dato">
                        <span class="icono" style="--c:#1f9d55" aria-hidden="true">📞</span>
                        <p><a href="tel:+573332224081">+57 333 222 4081</a></p>
                    </div>
                </article>

                <article class="c-card">
                    <h3>Redes Sociales</h3>
                    <div class="redes">
                        <a class="red" href="https://www.facebook.com/JuanRamirez" target="_blank" rel="noopener">
                            <span class="icono" style="--c:#1877f2" aria-hidden="true"><i class="fa-brands fa-facebook-f"></i></span>
                            <span><strong>Facebook</strong><small>Juan Ramirez</small></span>
                        </a>
                        <a class="red" href="https://www.instagram.com/Bitnote10_3" target="_blank" rel="noopener">
                            <span class="icono" style="--c:#e1306c" aria-hidden="true"><i class="fa-brands fa-instagram"></i></span>
                            <span><strong>Instagram</strong><small>@Bitnote10_3</small></span>
                        </a>
                        <a class="red" href="https://www.tiktok.com/@Bitnote_empresa_" target="_blank" rel="noopener">
                            <span class="icono" style="--c:#17231D" aria-hidden="true"><i class="fa-brands fa-tiktok"></i></span>
                            <span><strong>TikTok</strong><small>@Bitnote_empresa_</small></span>
                        </a>
                    </div>
                </article>

            </div>

            <!-- ============ DERECHA: FORMULARIO ============ -->
            <section class="c-card ct-form" id="formulario">

                <h3>Déjanos tus datos</h3>
                <p>Completa el siguiente formulario y nos pondremos en contacto contigo.</p>

                <form id="contactoForm" method="POST" action="6_contacto.php#formulario" novalidate>
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf_contacto']) ?>">
                    <div style="position:absolute;left:-9999px" aria-hidden="true">
                        <label>No llenar este campo <input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <!-- CORREO (icono de Gmail) -->
                    <div class="campo" style="--c:#ea4335">
                        <label for="correo">Correo electrónico</label>
                        <div class="campo-fila">
                            <span class="icono" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path fill="#4285f4" d="M2 7v10.5A1.5 1.5 0 0 0 3.5 19h3v-8z"/>
                                    <path fill="#34a853" d="M22 7v10.5a1.5 1.5 0 0 1-1.5 1.5h-3v-8z"/>
                                    <path fill="#ea4335" d="M6.5 11V5.8L12 10l5.5-4.2V11L12 15.2z"/>
                                    <path fill="#c5221f" d="M2 7l4.5 4V5.8L4.9 4.6C3.7 3.7 2 4.5 2 6z"/>
                                    <path fill="#fbbc04" d="M22 7l-4.5 4V5.8l1.6-1.2C20.3 3.7 22 4.5 22 6z"/>
                                </svg>
                            </span>
                            <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" autocomplete="email" maxlength="120" value="<?= e($valores['correo']) ?>" required>
                        </div>
                        <small id="correoError" class="form-error"><?= e($errores['correo'] ?? '') ?></small>
                    </div>

                    <!-- TELÉFONO -->
                    <div class="campo" style="--c:#1f9d55">
                        <label for="telefono">Teléfono</label>
                        <div class="campo-fila">
                            <span class="icono" aria-hidden="true">📞</span>
                            <input type="tel" id="telefono" name="telefono" placeholder="+57 300 000 0000" autocomplete="tel" maxlength="20" value="<?= e($valores['telefono']) ?>" required>
                        </div>
                        <small id="telefonoError" class="form-error"><?= e($errores['telefono'] ?? '') ?></small>
                    </div>

                    <!-- DIRECCIÓN -->
                    <div class="campo" style="--c:#3b82c4">
                        <label for="direccion">Dirección</label>
                        <div class="campo-fila">
                            <span class="icono" aria-hidden="true">✉️</span>
                            <input type="text" id="direccion" name="direccion" placeholder="Escribe tu dirección" autocomplete="street-address" maxlength="200" value="<?= e($valores['direccion']) ?>" required>
                        </div>
                        <small id="direccionError" class="form-error"><?= e($errores['direccion'] ?? '') ?></small>
                    </div>

                    <button type="submit" class="btn-enviar">Enviar</button>
                    <?php if ($mensaje !== ''): ?>
                        <p id="contactoMensaje" class="alert <?= $exito ? 'success' : 'error' ?>"><?= $exito ? '✅' : '❌' ?> <?= e($mensaje) ?></p>
                    <?php else: ?>
                        <p id="contactoMensaje"></p>
                    <?php endif; ?>

                </form>
            </section>

        </div>

    </main>

    <!-- ================= FOOTER ================= -->
    <footer class="site-footer">
        <div class="container">
            <p>© 2026 Bitnotas — Digitalizamos tus ideas</p>
        </div>
    </footer>

    <!-- ================= JAVASCRIPT (solo avisos rápidos; el servidor vuelve a validar) ================= -->
    <script>
        const contactoForm = document.getElementById("contactoForm");
        const validarEmail = email => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        const validarTelefono = tel => /^[0-9+\s()-]{7,20}$/.test(tel);

        contactoForm.addEventListener("submit", function (e) {
            const correo = document.getElementById("correo").value.trim();
            const telefono = document.getElementById("telefono").value.trim();
            const direccion = document.getElementById("direccion").value.trim();

            const ec = !validarEmail(correo);
            const et = !validarTelefono(telefono);
            const ed = direccion.length < 5;

            document.getElementById("correoError").textContent = ec ? "⚠️ Ingresa un correo válido" : "";
            document.getElementById("telefonoError").textContent = et ? "⚠️ Ingresa un teléfono válido" : "";
            document.getElementById("direccionError").textContent = ed ? "⚠️ Ingresa una dirección válida" : "";

            if (ec || et || ed) { e.preventDefault(); return; }

            /* Evita enviar dos veces con doble clic */
            contactoForm.querySelector(".btn-enviar").disabled = true;
        });
    </script>

</body>

</html>