<?php
session_start();

// Si el usuario no ha iniciado sesión, lo redirige al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Bitnotas — Panel Principal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="container main">
        <section class="card" style="max-width: 500px; margin: 40px auto; text-align: center;">
            <h2>¡Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h2>
            <p>Has iniciado sesión correctamente como: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></p>
            
            <a href="cerrar_sesion.php"><button style="margin-top: 15px;">Cerrar Sesión</button></a>
        </section>
    </main>
</body>
</html>