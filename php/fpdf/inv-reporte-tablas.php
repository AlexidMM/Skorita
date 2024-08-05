<?php
session_start();
require '../conexion.php';

// Verificar si el usuario ha iniciado sesión y si es administrador
if (!isset($_SESSION['nom_us']) || $_SESSION['tipo_us'] != '1') {
    echo '
        <script>
            alert("Acceso denegado. Solo los administradores pueden acceder a esta página.");
            window.location = "../../../login.php";
        </script>
    ';
    exit;
}

ob_start();  

include ("../conexion.php");
include("fpdf186/html_table.php");

$pdf = new pdf();
$pdf->AddPage();

$pdf->SetFont('Arial','B',18);
$pdf->Cell(190,10,'Skora',0,0,'C');
$pdf->Ln();
$pdf->SetFont('Arial','',12);
date_default_timezone_set('America/Mexico_City');
$pdf->Cell(190,10,date('d/m/Y'),0,0,'C');
$pdf->Ln();
$pdf->SetFont('Arial','B',11);
$pdf->Cell(190,10,'REPORTE DE INVENTARIO',0,0,'C');
$pdf->Ln();

$miencabeza = "
<table border='1' style='margin: 0 auto;' width='100%'>
<tr>
<td width=75 bgcolor='#91DDCF'>Numero</td>
<td width=75 bgcolor='#91DDCF'>Existencia</td>
<td width=75 bgcolor='#91DDCF'>Sucursal</td>
<td width=109 bgcolor='#91DDCF'>Articulo</td>
<td width=70 bgcolor='#91DDCF'>Status</td>
</tr>
</table>
";

// Recibir el ID de la sucursal seleccionada
$sucursal_id = $_POST['sucursal'];

if ($sucursal_id == 'todos') {
    // Consulta SQL para obtener los datos del inventario
    $sqlInventario = "SELECT 
        i.no_inv,
        i.exist_inv,
        s.nom_suc,
        a.nom_art,
        i.Status
    FROM 
        Inventario i
        INNER JOIN SUCURSAL s ON i.no_suc = s.no_suc
        INNER JOIN ARTICULO a ON i.id_art = a.id_art";
} else {
    $sqlInventario = "SELECT 
        i.no_inv,
        i.exist_inv,
        s.nom_suc,
        a.nom_art,
        i.Status
    FROM 
        Inventario i
        INNER JOIN SUCURSAL s ON i.no_suc = s.no_suc
        INNER JOIN ARTICULO a ON i.id_art = a.id_art
        WHERE i.no_suc = '$sucursal_id'";
}

$resultado = mysqli_query($conexion, $sqlInventario);
$mitabla = "
<table border='1' style='margin: 0 auto;' width='100%'>
";
while ($fila = mysqli_fetch_array($resultado)){
    $mitabla.="
    <tr>
        <td width=20 align='center'>".$fila["no_inv"]."</td>
        <td width=110 align='center'>".$fila["exist_inv"]."</td>
        <td width=75 align='center'>".$fila["nom_suc"]."</td>
        <td width=75 align='center'>".$fila["nom_art"]."</td>
        <td width=109 align='center'>".$fila["Status"]."</td>
    </tr>
    ";
}
$mitabla.="</table>";
// Salida al PDF
$pdf->Ln();
$pdf->SetFont('Arial', '', 8);
$pdf->WriteHTML($miencabeza);
$pdf->WriteHTML($mitabla);
ob_end_clean();
$pdf->Output('Reporte_Inventario_Skora.pdf', 'I');
?>