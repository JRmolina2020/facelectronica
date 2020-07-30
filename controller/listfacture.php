<?php
require_once "../model/Facture.php";
$fac = new Facture();
$rspta = $fac->index(); //listado de las facturas de hoy
$rsptanote = $fac->tablenote(); //listado de las notas de hoy

//TABLERO INDICADOR DE LA FACTURA
$rsptaI = $fac->indicador();
$regi = $rsptaI->fetch_object();
$total = $regi->TOTAL;
$cantidad = $regi->CANTIDAD;