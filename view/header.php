<?php include '../config/conexion.php';
if (!isset($_SESSION['nombre'])) {
    header('location:../');
}
?>
<!doctype html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../public/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../public/bootstrap.css" />
    <link rel="stylesheet" href="../public/bootstrapValidator.css" />
    <link rel="stylesheet" href="../public/dataTables.bootstrap4.min.css">
    <title>Atrato</title>
</head>

<body>
    <ul class="nav nav-pills nav-fill">
        <li class="nav-item">
            <a class="nav-link active" href="../controller/auth.php?op=exit">SALIR</a>
        </li>
    </ul>
    <div class="container mt-5">