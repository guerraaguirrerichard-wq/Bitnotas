<?php

require_once "conexion.php";
session_start();

$mensaje = "";
$tipo_formulario = $_GET["form"] ?? "login";


/* =====================================================
   PROCESAR FORMULARIOS
   ===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $accion = $_POST["accion"] ?? "";


    /* =================================================
       INICIAR SESIÓN
       ================================================= */

    if ($accion === "login") {

        $usuario = trim($_POST["usuario"] ?? "");
        $clave = $_POST["clave"] ?? "";

        $tipo_formulario = "login";

        if ($usuario === "" || $clave === "") {

            $mensaje = "Por favor, completa todos los campos.";

        } else {

            $sql = "SELECT
                        id_usuario,
                        usuario,
                        clave,
                        nombre_completo,
                        id_rol,
                        activo
                    FROM usuarios
                    WHERE usuario = ?
                    LIMIT 1";

            $stmt = $conexion->prepare($sql);

            if (!$stmt) {

                $mensaje = "Error en la consulta: " . $conexion->error;

            } else {

                $stmt->bind_param("s", $usuario);
                $stmt->execute();

                $resultado = $stmt->get_result();

                if ($resultado->num_rows === 1) {

                    $usuario_db = $resultado->fetch_assoc();


                    /* Comprobar si la cuenta está activa */

                    if ((int)$usuario_db["activo"] !== 1) {

                        $mensaje = "Este usuario se encuentra inactivo.";

                    }


                    /* Comprobar contraseña */

                    elseif (password_verify($clave, $usuario_db["clave"])) {

                        $_SESSION["id_usuario"] =
                            $usuario_db["id_usuario"];

                        $_SESSION["usuario"] =
                            $usuario_db["usuario"];

                        $_SESSION["nombre_completo"] =
                            $usuario_db["nombre_completo"];

                        
$_SESSION["id_rol"] = $usuario_db["id_rol"];

/* =============================================
   REGISTRAR INICIO DE SESION EN EL HISTORIAL
   ============================================= */

$id_usuario_historial = (int) $usuario_db["id_usuario"];
$accion_historial = "Inicio de sesión";

$sql_historial = "INSERT INTO historial_accesos
                  (id_usuario, accion)
                  VALUES (?, ?)";

$stmt_historial = $conexion->prepare($sql_historial);

if (!$stmt_historial) {
    die("Error al preparar el historial: " . $conexion->error);
}

$stmt_historial->bind_param(
    "is",
    $id_usuario_historial,
    $accion_historial
);

if (!$stmt_historial->execute()) {
    die("Error al guardar el inicio de sesión: " . $stmt_historial->error);
}

$stmt_historial->close();

/* Redirigir a la pagina principal */

header("Location: index.php");
exit;


                

                        


                    } else {

                        $mensaje = "Usuario o contraseña incorrectos.";
                    }

                } else {

                    $mensaje = "Usuario o contraseña incorrectos.";
                }

                $stmt->close();
            }
        }
    }


    /* =================================================
       CREAR CUENTA
       ================================================= */

    if ($accion === "registro") {

        $nombre = trim($_POST["nombre_completo"] ?? "");
        $usuario = trim($_POST["usuario"] ?? "");
        $clave = $_POST["clave"] ?? "";
        $confirmar_clave = $_POST["confirmar_clave"] ?? "";
        $tipo_cuenta = $_POST["tipo_cuenta"] ?? "visitante";
$codigo_admin = trim($_POST["codigo_admin"] ?? "");

        $tipo_formulario = "registro";


       
/* Comprobar campos */

if (
    $nombre === "" ||
    $usuario === "" ||
    $clave === "" ||
    $confirmar_clave === ""
) {

    $mensaje = "Por favor, completa todos los campos.";

}

/* Comprobar tipo de cuenta */

elseif (
    $tipo_cuenta !== "visitante" &&
    $tipo_cuenta !== "administrador"
) {

    $mensaje = "El tipo de cuenta seleccionado no es válido.";

}

/* Comprobar autorización de administrador */

elseif (
    $tipo_cuenta === "administrador" &&
    !hash_equals(CODIGO_ADMIN, $codigo_admin)
) {

    $mensaje = "El código de autorización es incorrecto.";

}



        /* Comprobar contraseñas */

        elseif ($clave !== $confirmar_clave) {

            $mensaje = "Las contraseñas no coinciden.";

        }


        /* Comprobar longitud */

        elseif (strlen($clave) < 6) {

            $mensaje = "La contraseña debe tener mínimo 6 caracteres.";

        } else {


            /* =============================================
               COMPROBAR SI EL USUARIO YA EXISTE
               ============================================= */

            $sql = "SELECT id_usuario
                    FROM usuarios
                    WHERE usuario = ?
                    LIMIT 1";

            $stmt = $conexion->prepare($sql);

            if (!$stmt) {

                $mensaje = "Error en la consulta: " . $conexion->error;

            } else {

                $stmt->bind_param("s", $usuario);
                $stmt->execute();

                $resultado = $stmt->get_result();


                if ($resultado->num_rows > 0) {

                    $mensaje = "Ese usuario ya existe.";

                } else {


                    /* =====================================
                       CREAR USUARIO
                       ===================================== */

                    $clave_hash = password_hash(
                        $clave,
                        PASSWORD_DEFAULT
                    );


                    
/* Asignar el rol según el tipo de cuenta autorizado */

if ($tipo_cuenta === "administrador") {
    $id_rol = 1;
} else {
    $id_rol = 2;
}

$activo = 1;


                    /*
                     * fecha_registro es obligatoria
                     */

                    $sql_insert = "INSERT INTO usuarios
                        (
                            usuario,
                            clave,
                            nombre_completo,
                            id_rol,
                            activo,
                            fecha_registro
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            CURDATE()
                        )";


                    $stmt_insert =
                        $conexion->prepare($sql_insert);


                    if (!$stmt_insert) {

                        $mensaje =
                            "Error al crear la cuenta: " .
                            $conexion->error;

                    } else {

                        $stmt_insert->bind_param(
                            "sssii",
                            $usuario,
                            $clave_hash,
                            $nombre,
                            $id_rol,
                            $activo
                        );


                        if ($stmt_insert->execute()) {

                            $mensaje =
                                "Cuenta creada correctamente. " .
                                "Ahora puedes iniciar sesión.";

                            $tipo_formulario = "login";

                        } else {

                            $mensaje =
                                "Error al crear la cuenta: " .
                                $stmt_insert->error;
                        }


                        $stmt_insert->close();
                    }
                }

                $stmt->close();
            }
        }
    }
}

?>


<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Bitnotas —
        <?= $tipo_formulario === "registro"
            ? "Crear cuenta"
            : "Iniciar sesión"
        ?>
    </title>


    <link rel="stylesheet" href="styles.css">


    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>


<body>


<!-- =====================================================
     ENCABEZADO
     ===================================================== -->

<header class="site-header">

    <div class="container">

        <!-- Página principal: index.php -->

        <a href="index.php" class="logo">

            <div class="logo-icon">

                <i class="fa-solid fa-book-open"></i>

                <i class="fa-solid fa-leaf"></i>

            </div>

            <span>Bitnotas</span>

        </a>

    </div>

</header>



<!-- =====================================================
     CONTENIDO
     ===================================================== -->

<main class="login-container">

    <section class="login-card">


        <!-- ICONO -->

        <div class="login-icon">

            <i
                class="fa-solid
                <?= $tipo_formulario === "registro"
                    ? "fa-user-plus"
                    : "fa-user"
                ?>"
            ></i>

        </div>



        <?php if ($tipo_formulario === "registro"): ?>


            <!-- =========================================
                 CREAR CUENTA
                 ========================================= -->

            <h1>Crear cuenta</h1>

            <p>Regístrate en Bitnotas</p>


            <?php if ($mensaje !== ""): ?>

                <div class="login-message">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?= htmlspecialchars($mensaje) ?>

                </div>

            <?php endif; ?>


            <form method="POST" action="">

                <input
                    type="hidden"
                    name="accion"
                    value="registro"
                >


                <!-- NOMBRE -->

                <div class="input-group">

                    <label for="nombre_completo">
                        Nombre completo
                    </label>

                    <div class="input-box">

                        <i class="fa-solid fa-user"></i>

                        <input
                            type="text"
                            name="nombre_completo"
                            id="nombre_completo"
                            placeholder="Ingresa tu nombre"
                            required
                        >

                    </div>

                </div>


                <!-- USUARIO -->

                <div class="input-group">

                    <label for="usuario">
                        Usuario
                    </label>

                    <div class="input-box">

                        <i class="fa-solid fa-user"></i>

                        <input
                            type="text"
                            name="usuario"
                            id="usuario"
                            placeholder="Crea un usuario"
                            required
                        >

                    </div>

                </div>


                <!-- CONTRASEÑA -->

                <div class="input-group">

                    <label for="clave">
                        Contraseña
                    </label>

                    <div class="input-box">

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            name="clave"
                            id="clave"
                            placeholder="Crea una contraseña"
                            required
                        >

                    </div>

                </div>


                <!-- CONFIRMAR CONTRASEÑA -->

                <div class="input-group">

                    <label for="confirmar_clave">
                        Confirmar contraseña
                    </label>

                    <div class="input-box">

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            name="confirmar_clave"
                            id="confirmar_clave"
                            placeholder="Repite tu contraseña"
                            required
                        >

                    </div>

                </div>

                
<!-- TIPO DE CUENTA -->

<div class="input-group">

    <label for="tipo_cuenta">
        Tipo de cuenta
    </label>

    <div class="input-box">

        <i class="fa-solid fa-users"></i>

        <select
            name="tipo_cuenta"
            id="tipo_cuenta"
            required
        >
            <option value="visitante" selected>
                Visitante
            </option>

            <option value="administrador">
                Administrador
            </option>

        </select>

    </div>
    
<!-- CODIGO DE AUTORIZACION ADMINISTRADOR -->

<div class="input-group" id="grupo_codigo_admin" hidden>

    <label for="codigo_admin">
        Código de autorización
    </label>

    <div class="input-box">

        <i class="fa-solid fa-shield-halved"></i>

        <input
            type="password"
            name="codigo_admin"
            id="codigo_admin"
            placeholder="Ingresa el código de autorización"
            autocomplete="off"
            disabled
        >

    </div>

    <small>
        Necesitas autorización para crear una cuenta de administrador.
    </small>

</div>


    <small>
        La cuenta de administrador requiere autorización.
    </small>

</div>



                <!-- BOTÓN -->

                <button
                    type="submit"
                    class="login-button"
                >

                    Crear cuenta

                    <i class="fa-solid fa-user-plus"></i>

                </button>

            </form>


            <!-- ENLACES -->

            <div class="login-links">

                <a href="login.php">

                    <i class="fa-solid fa-arrow-left"></i>

                    Ya tengo una cuenta

                </a>

            </div>



        <?php else: ?>


            <!-- =========================================
                 INICIAR SESIÓN
                 ========================================= -->

            <h1>Bienvenido a Bitnotas</h1>

            <p>Inicia sesión para continuar</p>


            <?php if ($mensaje !== ""): ?>

                <div class="login-message">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?= htmlspecialchars($mensaje) ?>

                </div>

            <?php endif; ?>


            <form method="POST" action="">

                <input
                    type="hidden"
                    name="accion"
                    value="login"
                >


                <!-- USUARIO -->

                <div class="input-group">

                    <label for="usuario">
                        Usuario
                    </label>

                    <div class="input-box">

                        <i class="fa-solid fa-user"></i>

                        <input
                            type="text"
                            name="usuario"
                            id="usuario"
                            placeholder="Ingresa tu usuario"
                            required
                            autocomplete="username"
                        >

                    </div>

                </div>


                <!-- CONTRASEÑA -->

                <div class="input-group">

                    <label for="clave">
                        Contraseña
                    </label>

                    <div class="input-box">

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            name="clave"
                            id="clave"
                            placeholder="Ingresa tu contraseña"
                            required
                            autocomplete="current-password"
                        >

                    </div>

                </div>


                <!-- BOTÓN -->

                <button
                    type="submit"
                    class="login-button"
                >

                    Iniciar sesión

                    <i class="fa-solid fa-arrow-right"></i>

                </button>

            </form>


            <!-- ENLACES -->

            <div class="login-links">

                <a href="login.php?form=registro">

                    <i class="fa-solid fa-user-plus"></i>

                    Crear una cuenta

                </a>


                <br><br>


                <!-- Volver a la página principal -->

                <a href="index.php">

                    <i class="fa-solid fa-house"></i>

                    Volver al inicio

                </a>

            </div>


        <?php endif; ?>


    </section>

</main>



<!-- =====================================================
     PIE DE PÁGINA
     ===================================================== -->

<footer class="site-footer">

    <div class="container">

        <p>
            © 2026 Bitnotas — Digitalizamos tus ideas
        </p>

    </div>

</footer>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const tipoCuenta = document.getElementById("tipo_cuenta");
    const grupoCodigo = document.getElementById("grupo_codigo_admin");
    const codigoAdmin = document.getElementById("codigo_admin");

    if (!tipoCuenta || !grupoCodigo || !codigoAdmin) {
        return;
    }

    function actualizarCampoCodigo() {

        const esAdministrador =
            tipoCuenta.value === "administrador";

        grupoCodigo.hidden = !esAdministrador;
        codigoAdmin.disabled = !esAdministrador;
        codigoAdmin.required = esAdministrador;

        if (!esAdministrador) {
            codigoAdmin.value = "";
        }
    }

    tipoCuenta.addEventListener("change", actualizarCampoCodigo);

    actualizarCampoCodigo();

});
</script>

</body>
</html>

