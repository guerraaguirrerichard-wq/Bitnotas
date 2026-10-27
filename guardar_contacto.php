<?php

// Responder siempre en formato JSON
header('Content-Type: application/json; charset=utf-8');

require_once 'conexion.php';

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

// Recoger y limpiar los datos enviados
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

// ============================================================
// Validaciones en el servidor (nunca confiar solo en el HTML)
// ============================================================
$errores = [];

if ($correo === '') {
    $errores[] = 'El correo es obligatorio.';
} elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El formato del correo no es válido.';
}

if ($telefono === '') {
    $errores[] = 'El teléfono es obligatorio.';
} elseif (!preg_match('/^[0-9+\s()-]{7,20}$/', $telefono)) {
    $errores[] = 'El formato del teléfono no es válido.';
}

if ($direccion === '') {
    $errores[] = 'La dirección es obligatoria.';
} elseif (strlen($direccion) < 5) {
    $errores[] = 'La dirección es demasiado corta.';
}

if (!empty($errores)) {
    echo json_encode(['exito' => false, 'mensaje' => implode(' ', $errores)]);
    exit;
}

// ============================================================
// Insertar en la base de datos usando consulta preparada
// (evita inyección SQL)
// ============================================================
$stmt = $conexion->prepare(
    "INSERT INTO contactos (correo, telefono, direccion) VALUES (?, ?, ?)"
);
$stmt->bind_param("sss", $correo, $telefono, $direccion);

if ($stmt->execute()) {
    echo json_encode(['exito' => true, 'mensaje' => '¡Gracias! Tus datos fueron registrados correctamente.']);
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'Ocurrió un error al guardar. Intenta de nuevo.']);
}

$stmt->close();
$conexion->close();

?>