
<?php

$servidor = "localhost";
$usuario = "root";
$clave = "";
$base_datos = "bitnotas";

/* Código de autorización para crear administradores */
define("CODIGO_ADMIN", "solo nacional");

// Crear conexión
$conexion = new mysqli($servidor, $usuario, $clave, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar caracteres
$conexion->set_charset("utf8mb4");

?>
