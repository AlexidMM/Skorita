<?php
session_start();

// Incluir archivo de conexión
require_once 'conexion.php';

// Verificar si el usuario ha iniciado sesión y si es un cliente
if (!isset($_SESSION['nom_us']) || $_SESSION['tipo_us']!= '2') {
    echo '
        <script>
            alert("Se tiene que ingresar Sesión primero.");
            window.location = "../login.php";
        </script>
    ';
    exit;
}

// Verificar si el usuario está logueado
if (!isset($_SESSION["nom_us"])) {
    header("Location: login.php");
    exit;
}

// Obtener el ID del usuario logueado
$nom_us = $_SESSION["nom_us"];

// Consulta para obtener el ID del usuario
$stmt = mysqli_prepare($conexion, "SELECT id_clie FROM CLIENTE WHERE nom_us = ?");
mysqli_stmt_bind_param($stmt, "s", $nom_us);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) > 0) {
    $row = mysqli_fetch_assoc($resultado);
    $id_clie = $row["id_clie"];
    $_SESSION["id_clie"] = $id_clie; // Almacenar el ID en la sesión
} else {
    // Si no se encuentra el usuario, puedes redirigir a una página de error
    header("Location: ../login.php");
    exit;
}

// Consulta para obtener los datos del cliente
$stmt = mysqli_prepare($conexion, "SELECT * FROM CLIENTE WHERE id_clie = ?");
mysqli_stmt_bind_param($stmt, "i", $id_clie);
mysqli_stmt_execute($stmt);
$resultado_cliente = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($resultado_cliente);

// Consulta para obtener las direcciones del usuario
$stmt = mysqli_prepare($conexion, "SELECT d.* FROM DIRECCION d INNER JOIN CLIENTE c ON d.id_dir = c.id_dir WHERE c.id_clie =? AND c.nom_us =?");
mysqli_stmt_bind_param($stmt, "is", $id_clie, $nom_us);
mysqli_stmt_execute($stmt);
$direcciones = mysqli_stmt_get_result($stmt);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["agregar_dir"])) {
        $calle = $_POST["calle"];
        $col = $_POST["col"];
        $ni = $_POST["ni"];
        $ne = $_POST["ne"];
        $cp = $_POST["cp"];
        $cve_ciud = $_POST["cve_ciud"];
        $id_clie = $_SESSION["id_clie"];

        // Insertar nueva dirección en la tabla DIRECCION
        $stmt = mysqli_prepare($conexion, "INSERT INTO DIRECCION (calle, col, ni, ne, cp, cve_ciud) VALUES (?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "sssssi", $calle, $col, $ni, $ne, $cp, $cve_ciud);
        mysqli_stmt_execute($stmt);

        // Redirigir a la misma página para mostrar la nueva dirección
        header("Location: ". $_SERVER["PHP_SELF"]);
        exit;
    }
}

?>
<head>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" type="text/css" href="../assets/css/car_style.css">
    <title>Datos</title>
</head>
<div class="container" style="text-align: center;">
    <div class="row justify-content-center">
        <h2>Datos del cliente</h2>
        <p>Nombre: <?php echo $cliente['nom_clie']; ?></p>
        <p>Apellido Paterno: <?php echo $cliente['ap_clie']; ?></p>
        <p>Apellido Materno: <?php echo $cliente['am_clie']; ?></p>
        <p>Correo electrónico: <?php echo $cliente['email_clie']; ?></p>
        <p>Teléfono: <?php echo $cliente['tel_clie']; ?></p>
        <p>RFC: <?php echo $cliente['rfc_clie']; ?></p>
    </div>

    <div class="row justify-content-center">
    <h2>Carrito de compras</h2>
    <p>Nombre del usuario: <?php echo $_SESSION['nom_us'];?></p>
    <p>Sucursal: <?php echo $_SESSION['sucursal'];?></p>
    <table border="1">
        <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
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
                </tr>';
        }
       ?>
        <tr>
            <th colspan="3">Total</th>
            <td colspan="2">$<?php echo $total;?></td>
        </tr>
    </table>
</div>

    <div class="row justify-content-center">
        <h2>Datos de la Venta</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <?php if (mysqli_num_rows($direcciones) > 0) { ?>
                <!-- Mostrar direcciones existentes -->
                <label for="id_dir">Dirección:</label>
                <select id="id_dir" name="id_dir" required>
                    <?php while ($direccion = mysqli_fetch_assoc($direcciones)) { ?>
                        <option value="<?php echo $direccion['id_dir']; ?>"><?php echo $direccion['calle'] . ' ' . $direccion['col'] . ' ' . $direccion['ni'] . '-' . $direccion['ne'] . ' CP: ' . $direccion['cp']; ?></option>
                    <?php } ?>
                </select><br><br>
            <?php } else { ?>
                <!-- Mostrar formulario para agregar nueva dirección -->
                <h3>No tienes direcciones registradas</h3>
                <label for="calle">Calle:</label>
                <input type="text" id="calle" name="calle" required autocomplete="off"><br><br>
                <label for="col">Colonia:</label>
                <input type="text" id="col" name="col" required autocomplete="off"><br><br>
                <label for="ni">Número interior:</label>
                <input type="text" id="ni" name="ni" autocomplete="off"><br><br>
                <label for="ne">Número exterior:</label>
                <input type="text" id="ne" name="ne" required autocomplete="off"><br><br>
                <label for="cp">Código postal:</label>
                <input type="text" id="cp" name="cp" required autocomplete="off"><br><br>
                <label for="ciudad">Ciudad:</label>
                <select id="ciudad" name="ciudad" required>
                    <?php
                        $stmt = mysqli_prepare($conexion, "SELECT cve_ciud, nom_ciud FROM CIUDAD");
                        mysqli_stmt_execute($stmt);
                        $ciudades = mysqli_stmt_get_result($stmt);
                        while ($ciudad = mysqli_fetch_assoc($ciudades)) {
                            echo "<option value='" . $ciudad['cve_ciud'] . "'>" . $ciudad['nom_ciud'] . "</option>";
                        }
                    ?>
                </select><br><br>
                <input type="submit" name="agregar_dir" value="Agregar dirección">
            <?php } ?>
            <a type="button" class="btn btn-primary" href="compra.php">Comprar</a>
        </form>
        <div class="botones">
            <a href="carrito.php" class="btn btn-warning">Volver</a>
        </div>
    </div>
</div>

<script>
    document.getElementById('agregar_dir').addEventListener('click', function() {
        document.getElementById('formulario_dir').style.display = 'block';
    });
</script>