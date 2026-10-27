<?php
session_start();

require_once "conexion.php";

$logueado = isset($_SESSION["id_usuario"]);

$nombre_usuario = $_SESSION["nombre_completo"]
    ?? $_SESSION["usuario"]
    ?? "";

$usuario = $_SESSION["usuario"] ?? "";



?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bitnotas | Digitalizamos tus ideas</title>

    <!-- CSS PRINCIPAL -->
    <link rel="stylesheet" href="styles.css">

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>

<body>




<!-- =====================================================
     HEADER
     ===================================================== -->

<header class="header">

    <div class="container navbar">


        <!-- =================================================
             LOGO
             ================================================= -->

        <a href="index.php" class="logo">

            <div class="logo-icon">

                <i class="fa-solid fa-book-open"></i>

                <i class="fa-solid fa-leaf"></i>

            </div>

            <span>Bitnotas</span>

        </a>


        <!-- =================================================
             MENÚ
             ================================================= -->

        <nav class="menu">

            <a href="index.php" class="active">
                Inicio
            </a>

            <a href="2_quienes_somos.html">
                Quiénes somos
            </a>

            <a href="3_servicios.html">
                Servicios
            </a>

            <a href="4_noticias.html">
                Noticias
            </a>

            <a href="5_galeria.html">
                Galería
            </a>

            <a href="6_contacto.php">
                Contacto
            </a>

            <a href="7_politicas.php">
                Políticas
            </a>

            <a href="8_prototipo.php">
                Prototipo
            </a>

        </nav>


        <!-- =================================================
             USUARIO / INICIAR SESIÓN
             ================================================= -->

        <div class="usuario-area">

            <?php if ($logueado): ?>


                <!-- =========================================
                     USUARIO LOGUEADO
                     ========================================= -->

                <div class="usuario-info">


                    <!-- NOMBRE DEL USUARIO -->

                    <div
                        class="usuario-nombre"
                        title="<?= htmlspecialchars($usuario) ?>"
                    >

                        <i class="fa-solid fa-circle-user"></i>

                        <span>
                            <?= htmlspecialchars($nombre_usuario ?: $usuario) ?>
                        </span>

                    </div>


                    <!-- =====================================
                         CERRAR SESIÓN
                         ===================================== -->

                    <a
                        href="logout.php"
                        class="btn-cerrar-sesion"
                        title="Cerrar sesión"
                    >

                        <i class="fa-solid fa-right-from-bracket"></i>

                        <span>
                            Cerrar sesión
                        </span>

                    </a>


                </div>


            <?php else: ?>


                <!-- =========================================
                     NO HAY SESIÓN
                     ========================================= -->

                <a
                    href="login.php"
                    class="btn-iniciar-sesion"
                >

                    <i class="fa-solid fa-user"></i>

                    <span>
                        Iniciar sesión
                    </span>

                </a>


            <?php endif; ?>

        </div>


    </div>

</header>



<!-- =====================================================
     HERO
     ===================================================== -->

<section class="hero">

    <div class="container hero-content">


        <!-- TEXTO -->

        <div class="hero-text">


            <span class="mini-title">

                <i class="fa-solid fa-leaf"></i>

                Tecnología que transforma

            </span>


            <h1>

                Digitalizamos tus ideas,

                <span>
                    organizamos tu futuro
                </span>

            </h1>


            <p>

                Transformamos cuadernos físicos en soluciones
                digitales seguras y accesibles para estudiantes,
                agricultores e investigadores.

            </p>


            <!-- BOTONES -->

            <div class="hero-buttons">


                <a
                    href="2_quienes_somos.html"
                    class="btn-primary"
                >

                    Conoce más

                    <i class="fa-solid fa-arrow-right"></i>

                </a>


                <a
                    href="3_servicios.html"
                    class="btn-secondary"
                >

                    Nuestros servicios

                    <i class="fa-solid fa-leaf"></i>

                </a>


            </div>


        </div>



        <!-- IMAGEN -->

        <div class="hero-image">


            <div class="image-circle">

                <img
                    src="img/cuaderno.jpg"
                    alt="Cuaderno digital Bitnotas"
                >

            </div>


            <!-- TARJETA FLOTANTE -->

            <div class="floating-card">


                <div class="floating-icon">

                    <i class="fa-solid fa-shield-halved"></i>

                </div>


                <div>

                    <strong>
                        Información segura
                    </strong>

                    <small>
                        Protegemos tus datos
                    </small>

                </div>


            </div>


        </div>


    </div>

</section>



<!-- =====================================================
     QUIÉNES SOMOS
     ===================================================== -->

<section
    class="nosotros"
    id="nosotros"
>

    <div class="container">


        <!-- TÍTULO -->

        <div class="section-title">

            <span>
                CONÓCENOS
            </span>

            <h2>
                Tecnología con propósito
            </h2>

            <p>

                Creamos soluciones digitales que facilitan
                la organización, conservación y acceso
                a la información.

            </p>

        </div>



        <!-- TARJETAS -->

        <div class="cards">


            <!-- =========================================
                 MISIÓN
                 ========================================= -->

            <article class="info-card">


                <div class="card-icon">

                    <i class="fa-solid fa-bullseye"></i>

                </div>


                <h3>
                    Misión
                </h3>


                <div class="line"></div>


                <p>

                    Facilitar la recolección y organización
                    de información mediante herramientas
                    digitales innovadoras, accesibles y seguras.

                </p>


                <i class="fa-solid fa-leaf card-decoration"></i>


            </article>



            <!-- =========================================
                 VISIÓN
                 ========================================= -->

            <article class="info-card">


                <div class="card-icon">

                    <i class="fa-solid fa-eye"></i>

                </div>


                <h3>
                    Visión
                </h3>


                <div class="line"></div>


                <p>

                    Ser la empresa líder en Latinoamérica
                    en cuadernos digitales y soluciones de
                    registro para estudiantes, agricultores
                    e investigadores.

                </p>


                <i class="fa-solid fa-seedling card-decoration"></i>


            </article>



            <!-- =========================================
                 VALORES
                 ========================================= -->

            <article class="info-card">


                <div class="card-icon">

                    <i class="fa-solid fa-star"></i>

                </div>


                <h3>
                    Valores
                </h3>


                <div class="line"></div>


                <ul>

                    <li>

                        <i class="fa-solid fa-check"></i>

                        Innovación

                    </li>


                    <li>

                        <i class="fa-solid fa-check"></i>

                        Compromiso

                    </li>


                    <li>

                        <i class="fa-solid fa-check"></i>

                        Sostenibilidad

                    </li>


                    <li>

                        <i class="fa-solid fa-check"></i>

                        Inclusión

                    </li>


                    <li>

                        <i class="fa-solid fa-check"></i>

                        Responsabilidad

                    </li>

                </ul>


                <i class="fa-solid fa-leaf card-decoration"></i>


            </article>


        </div>

    </div>

</section>



<!-- =====================================================
     PROPÓSITO
     ===================================================== -->

<section class="purpose">

    <div class="container">


        <div class="purpose-box">


            <div class="purpose-icon">

                <i class="fa-solid fa-seedling"></i>

            </div>


            <div>

                <span>
                    NUESTRO PROPÓSITO
                </span>


                <h2>

                    Tecnología, educación y campo unidos
                    por un mismo propósito: construir un
                    futuro más organizado, sostenible y accesible.

                </h2>

            </div>


        </div>


    </div>

</section>



<!-- =====================================================
     ESTADÍSTICAS
     ===================================================== -->

<section class="statistics">

    <div class="container stats">


        <!-- USUARIOS -->

        <div class="stat">


            <div class="stat-icon">

                <i class="fa-solid fa-users"></i>

            </div>


            <div>

                <strong>
                    +5.000
                </strong>

                <span>
                    Usuarios activos
                </span>

            </div>


        </div>



        <!-- CUADERNOS -->

        <div class="stat">


            <div class="stat-icon">

                <i class="fa-solid fa-book"></i>

            </div>


            <div>

                <strong>
                    +20.000
                </strong>

                <span>
                    Cuadernos digitalizados
                </span>

            </div>


        </div>



        <!-- PAÍSES -->

        <div class="stat">


            <div class="stat-icon">

                <i class="fa-solid fa-globe"></i>

            </div>


            <div>

                <strong>
                    10
                </strong>

                <span>
                    Países impactados
                </span>

            </div>


        </div>



        <!-- SEGURIDAD -->

        <div class="stat">


            <div class="stat-icon">

                <i class="fa-solid fa-shield-halved"></i>

            </div>


            <div>

                <strong>
                    100%
                </strong>

                <span>
                    Comprometidos con la seguridad
                </span>

            </div>


        </div>


    </div>

</section>

<!-- FOOTER -->


<!-- =============================================
     HISTORIAL AUTOMATICO DE ACCIONES RECIENTES
     ============================================= -->





<!-- =====================================================
     FOOTER
     ===================================================== -->

<footer>

    <div class="container footer-content">


        <!-- LOGO -->

        <div class="footer-logo">


            <div class="logo-icon">

                <i class="fa-solid fa-book-open"></i>

                <i class="fa-solid fa-leaf"></i>

            </div>


            <strong>
                Bitnotas
            </strong>


            <p>

                Digitalizamos tus ideas,<br>
                organizamos tu futuro.

            </p>


        </div>



        <!-- ENLACES -->

        <div class="footer-column">


            <h3>
                Enlaces
            </h3>


            <a href="index.php">
                Inicio
            </a>


            <a href="2_quienes_somos.html">
                Quiénes somos
            </a>


            <a href="3_servicios.html">
                Servicios
            </a>


            <a href="6_contacto.html">
                Contacto
            </a>


        </div>



        <!-- CONTACTO -->

        <div class="footer-column">


            <h3>
                Contacto
            </h3>


            <p>

                <i class="fa-solid fa-envelope"></i>

                contacto@bitnotas.com

            </p>


            <p>

                <i class="fa-solid fa-phone"></i>

                +57 300 000 0000

            </p>


        </div>



        <!-- REDES SOCIALES -->

        <div class="footer-column">


            <h3>
                Síguenos
            </h3>


            <div class="social">


                <a href="#" aria-label="Facebook">

                    <i class="fa-brands fa-facebook-f"></i>

                </a>


                <a href="#" aria-label="Instagram">

                    <i class="fa-brands fa-instagram"></i>

                </a>


                <a href="#" aria-label="TikTok">

                    <i class="fa-brands fa-tiktok"></i>

                </a>


                <a href="#" aria-label="WhatsApp">

                    <i class="fa-brands fa-whatsapp"></i>

                </a>


            </div>


        </div>


    </div>



    <!-- COPYRIGHT -->

    <div class="copyright">

        © 2026 Bitnotas · Todos los derechos reservados

    </div>


</footer>



<!-- =====================================================
     BOOTSTRAP JS
     ===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>