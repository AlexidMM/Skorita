<?php
include 'conexion.php';
session_start();

print_r($_POST);
print_r($_SESSION);

if (!isset($_SESSION['nom_us']) || $_SESSION['tipo_us']!= '2') {
    echo '
        <script>
            alert("Necesitas iniciar sesión primero.");
            window.location = "../login.php";
        </script>
    ';
    exit;
}

$id_clie = $_POST['id_clie'];
$paymentId = $_POST['paymentId'];
$monto = $_POST['monto'];
$metodoPago = $_POST['metodoPago'];
$hora = $_POST['hora'];

// Verificar si se están recibiendo los datos correctamente
if (!isset($_POST['id_clie']) ||!isset($_POST['paymentId']) ||!isset($_POST['monto']) ||!isset($_POST['metodoPago']) ||!isset($_POST['hora']) ||!isset($_POST['carrito']) ||!isset($_SESSION['sucursal'])) {
    echo '
        <script>
            alert("No se recibieron los datos correctamente.");
            window.location = "carrito.php";
        </script>
    ';
    exit;
}

// Obtener los datos del pago
$id_clie = $_POST['id_clie'];
$paymentId = $_POST['paymentId'];
$monto = $_POST['monto'];
$metodoPago = $_POST['metodoPago'];
$hora = $_POST['hora'];
$carrito = json_decode($_POST['carrito'], true);
$sucursal = $_SESSION['sucursal'];

// Iniciar transacción
$conexion->begin_transaction();

try {
    // Insertar venta
    $conexion->query("CALL sp_insert_venta('$id_clie', NOW(), '$sucursal')");
    $venta_id = $conexion->insert_id;
    if (!$venta_id) {
        throw new Exception("Error al insertar venta");
    }

    // Insertar pago
    $conexion->query("CALL sp_insert_pago('$monto', NOW(), '$venta_id')");
    if (!$conexion->affected_rows) {
        throw new Exception("Error al insertar pago");
    }

    //...

    // Recorrer cada artículo en el carrito
    foreach ($carrito as $item) {
        //...

        // Insertar venta_inventario
        $conexion->query("CALL sp_insert_venta_inv('$venta_id', '$no_inv', '$item[cantidad]', '$sucursal')");
        if (!$conexion->affected_rows) {
            throw new Exception("Error al insertar venta_inventario");
        }

        // Actualizar inventario
        $conexion->query("CALL trg_update_inventario('$venta_id', '$item[id_art]', '$item[cantidad]')");
        if (!$conexion->affected_rows) {
            throw new Exception("Error al actualizar inventario");
        }
    }

    // Commit transacción
    $conexion->commit();

    // Mostrar un alert de confirmación
    echo '
        <script>
            alert("La compra se realizó con éxito.");
            window.location = "fac.php";
        </script>
    ';
} catch (Exception $e) {
    // Rollback transacción en caso de error
    $conexion->rollback();
    error_log("Error al realizar la compra: " . $e->getMessage());
    echo '
        <script>
            alert("Error al realizar la compra. Inténtalo de nuevo.");
            window.location = "carrito.php";
        </script>
    ';
}