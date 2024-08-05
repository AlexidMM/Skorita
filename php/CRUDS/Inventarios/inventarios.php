<?php
	session_start();
    require '../../../php/conexion.php';
    // Verificar si el usuario ha iniciado sesión y si es administrador
    if (!isset($_SESSION['nom_us']) || $_SESSION['tipo_us'] != '1') {
        header('Location: ../../../login.php');
        exit;
    }

    if (isset($_COOKIE['usuario'])) {
        $nom_us = $_COOKIE['usuario'];
        echo "Bienvenidx, $nom_us";
    }else{
        echo "Usuario no reconocido";
    }

    $sqlInventario = "SELECT i.no_inv, i.exist_inv,
    s.nom_suc,
    a.nom_art, 
    i.status
    FROM INVENTARIO i
    INNER JOIN SUCURSAL s ON i.no_suc = s.no_suc
    INNER JOIN ARTICULO a ON i.id_art = a.id_art
    GROUP BY no_inv ASC;";
    $Inventario = $conexion->query($sqlInventario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventarios</title>
        <link href="../../../assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="../../../assets/css/all.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column h-100">
	  
  <div class="container py-3">
        <h2 class="text-center">Inventarios</h2>
        <hr>
        <div class="row justify-content-center">
          <div class="col-auto">
            <a href="nuevoModal.php" class="btn btn-primary">Nuevo registro</a>
          </div>
          <div class="col-auto">
            <a href="actualizaModal.php" class="btn btn-primary btn-warning"> Editar</a>
          </div>
          <div class="col-auto">
            <a href="bajaModal.php" class="btn btn-primary btn-danger" data-bs-id="<?= $row['id_art']; ?>"> Eliminar</a>
          </div>
        </div>
        <table class="table table-sm table-striped table-hover mt-4">
            <thead class="table-dark">
                <tr>
                    <th width="10%">Número</th>
                    <th width="10%">Existencia</th>
                    <th width="10%">Sucursal</th>
                    <th width="10%">Artículo</th>
                    <th width="10%">Status</thwid>
                </tr>
            </thead>

            <tbody>
            <div class="row justify-content-center">
              <div class="col-auto">
                <form action="../../fpdf/inv-reporte-tablas.php">
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#reporteModal">Generar Reporte</button>
                </form>
              </div>
            </div>
                <?php while ($row = $Inventario->fetch_assoc()) { ?>
                
                    <tr>
                        <td width="5%"><?= $row['no_inv']; ?></td>
                        <td width="20%"><?= $row['exist_inv']; ?></td>
                        <td width="20%"><?= $row['nom_suc']; ?></td>
                        <td width="20%"><?= $row['nom_art']; ?></td> 
                        <td width="20%"><?= $row['status']; ?></td>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="../../../admin.php">Regresar</a>
    </div>
    <div class="modal fade" id="reporteModal" tabindex="-1" aria-labelledby="reporteModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="reporteModalLabel">Generar Reporte</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="../../fpdf/inv-reporte-tablas.php" method="post">
              <div class="row mb-3">
                <label for="sucursal" class="col-sm-2 col-form-label">Sucursal:</label>
              <div class="col-sm-10">
                  <select class="form-select" id="sucursal" name="sucursal">
                    <option value="todos">Todas las sucursales</option>
                    <?php 
                      $sqlSucursales = "SELECT i.no_suc, s.nom_suc
                      FROM INVENTARIO i
                      JOIN SUCURSAL s ON i.no_suc = s.no_suc;";
                      $sucursales = $conexion->query($sqlSucursales);
                      while ($row = $sucursales->fetch_assoc()) { 
                    ?>
                      <option value="<?= $row['no_suc']; ?>"><?= $row['nom_suc']; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">Generar Reporte</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
        </div>
    </footer>
    
  <script src="../../../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>