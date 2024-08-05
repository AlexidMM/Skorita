<?php

// Incluir archivo de conexión
include 'conexion.php';
include_once 'carrito.php';


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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PayPal JS SDK Standard Integration</title>

    <script src="https://www.paypal.com/sdk/js?client-id=AdrUrZMoR1QBIRB31DsKw45k0wSxAJVx3pY6ovM_1kB4OIq3PrWS6ED5l8uXkeCf3jVUNFVVBHIPh2_2&currency=MXN"
    ></script>
  </head>
  <body>

    <div id="paypal-button-container"></div>
    
    <?php
        $total = 0;
        $items = array();
        foreach ($_SESSION['carrito'] as $item) {
            $subtotal = $item['prec_art'] * $item['cantidad'];
            $total += $subtotal;
            $items[] = array(
                'name' => $item['nom_art'],
                'unit_amount' => array(
                    'currency_code' => 'MXN',
                    'value' => $item['prec_art']
                ),
                'quantity' => $item['cantidad']
            );
        }
    ?>
    
    <script>
        paypal.Buttons({
            style:{
                color: 'blue',
                shape: 'pill',
                label: 'pay'
            },
            createOrder: function(data, actions){
                return actions.order.create({
                    purchase_units: [{
                        amount : {
                            currency_code: 'MXN',
                            value: '<?php echo $total; ?>',
                            breakdown: {
                                item_total: {
                                    currency_code: 'MXN',
                                    value: '<?php echo $total; ?>'
                                }
                            }
                        },
                        items: <?php echo json_encode($items); ?>
                    }]
                });
            },

            onApprove: function(data, actions){
                actions.order.capture().then(async function (detalles){
                    // Llamada a la función para procesar el pago
                    procesarPago(detalles);
                });
            },

            onCancel:function(data){
                alert("Pago cancelado :(");
                console.log(data);
            }
        }).render('#paypal-button-container');

        // Función para procesar el pago
        function procesarPago(detalles) {
            // Obtener la fecha y hora actual
            let fecha = new Date();
            let hora = fecha.getHours() + ':' + fecha.getMinutes() + ':' + fecha.getSeconds();

            // Obtener el ID del pago
            let paymentId = detalles.id;

            // Obtener el monto del pago
            let monto = detalles.purchase_units[0].amount.value;

            // Obtener el método de pago
            let metodoPago = detalles.payer.payment_method;

            // Obtener el ID del cliente desde la sesión
            let id_clie = <?php echo json_encode($_SESSION["id_clie"]);?>;

            // Enviar la información del pago al servidor
            $.ajax({
                type: "POST",
                url: "proc_vta.php",
                data: {
                    id_clie: id_clie,
                    paymentId: paymentId,
                    monto: monto,
                    metodoPago: metodoPago,
                    hora: hora,
                    carrito: JSON.stringify(<?php echo json_encode($_SESSION['carrito']); ?>),
                    sucursal: <?php echo json_encode($_SESSION['sucursal']); ?>
                },
                success: function(response) {
                    console.log(response);
                    if (response === 'ok') {
                        alert("Pago procesado correctamente.");
                        // Redirigir a una página de éxito o mostrar un mensaje de confirmación
                    } else {
                        alert("Error al procesar el pago.");
                        // Mostrar un mensaje de error
                    }
                }
            });
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  </body>
</html>