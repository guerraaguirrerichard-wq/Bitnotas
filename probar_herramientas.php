
<?php
require_once "conexion.php";

$sql = "SELECT * FROM herramientas";
$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error al consultar: " . $conexion->error);
}

echo "<h1>Herramientas registradas en Bitnotas</h1>";

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        echo "ID: " . $fila["id_herramientas"] . "<br>";
        echo "Nombre: " . $fila["nombre"] . "<br>";
        echo "Tipo: " . $fila["tipo"] . "<br>";
        echo "Estado: " . $fila["estado"] . "<hr>";
    }
} else {
    echo "Todavía no hay herramientas registradas.";
}

$conexion->close();
?>

