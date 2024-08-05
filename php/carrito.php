<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['nom_us']) || $_SESSION['tipo_us']!= '2') {
    echo '
        <script>
            alert("Se tiene que ingresar Sesión primero.");
            window.location = "../login.php";
        </script>
    ';
    exit;
}   

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

if (isset($_POST['id_art'])) {
    $id_art = $_POST['id_art'];
    $producto = mysqli_query($conexion, "SELECT * FROM ARTICULO WHERE id_art = '$id_art'");
    $producto = mysqli_fetch_assoc($producto);

    // Verificar si el producto ya está en el carrito
    $found = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id_art'] == $id_art) {
            $item['cantidad']++; // Incrementar cantidad en 1
            $found = true;
            break;
        }
    }

    // Agregar producto al carrito si no existe
    if (!$found) {
        $_SESSION['carrito'][] = array(
            'id_art' => $id_art,
            'nom_art' => $producto['nom_art'],
            'prec_art' => $producto['prec_art'],
            'cantidad' => 1
        );
    }

    // Actualizar contador de carrito
    $_SESSION['carrito_count'] = count($_SESSION['carrito']);

    header('Location: carrito.php');
    exit;
}

if (isset($_POST['id_art_eliminar'])) {
    $id_art_eliminar = $_POST['id_art_eliminar'];
    foreach ($_SESSION['carrito'] as $key => &$item) {
        if ($item['id_art'] == $id_art_eliminar) {
            $item['cantidad']--; // Disminuir cantidad en 1
            if ($item['cantidad'] <= 0) {
                unset($_SESSION['carrito'][$key]); // Eliminar registro si cantidad es 0
            }
            break;
        }
    }
    header('Location: carrito.php');
    exit;
}

if (isset($_POST['action']) && $_POST['action'] == 'clear_cart') {
    // Update cart quantity to 0 and remove items
    foreach ($_SESSION['carrito'] as $key => &$item) {
        $item['cantidad'] = 0;
        unset($_SESSION['carrito'][$key]);
    }
    $_SESSION['carrito'] = array(); // Reset the cart array
}

?>

<head>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" type="text/css" href="../assets/css/car_style.css">
    <title>Carrito de compras</title>
</head>

<h2>Carrito de compras</h2>

<table border="1">
    <tr>
        <th>Producto</th>
        <th>Precio</th>
        <th>Cantidad</th>
        <th>Subtotal</th>
        <th>Acción</th>
    </tr>
    <?php
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $subtotal = $item['prec_art'] * $item['cantidad'];
        $total += $subtotal;
        echo '<tr>
                <td>'.$item['nom_art'].'</td>
                <td>$'.$item['prec_art'].'</td>
                <td>'.$item['cantidad'].'</td>
                <td>$'.$subtotal.'</td>
                <td>
                    <form action="carrito.php" method="POST">
                        <input type="hidden" name="id_art_eliminar" value="'.$item['id_art'].'">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
              </tr>';
    }
?>
    <tr>
        <th colspan="3">Total</th>
        <td colspan="2">$<?php echo $total;?></td>
    </tr>
</table>

<h2>Seleccione la sucursal:</h2>
<form action="carrito.php" method="POST">
    <label for="sucursal">Sucursal:</label>
    <select id="sucursal" name="sucursal">
        <?php
        $sucursales = mysqli_query($conexion, "SELECT * FROM SUCURSAL");
        while ($sucursal = mysqli_fetch_assoc($sucursales)) {
            echo '<option value="'.$sucursal['no_suc'].'">'.$sucursal['nom_suc'].'</option>';
        }
       ?>
    </select>
    <input type="submit" value="Seleccionar sucursal">
</form>

<?php
if (isset($_POST['sucursal'])) {
    $_SESSION['sucursal'] = $_POST['sucursal'];
}
?>

<div class="botones">
    <div class="comprar-btn">
        <form action="datos.php" method="POST">
            <input type="submit" value="Comprar" class="btn btn-primary">
        </form>
    </div>
    <a href="../prod.php" onclick="history.back()" class="btn btn-warning">Volver</a>
    <form action="carrito.php" method="POST">
        <input type="hidden" name="action" value="clear_cart">
        <input type="submit" value="Vaciar carrito" class="btn btn-danger">
    </form>
</div>  
