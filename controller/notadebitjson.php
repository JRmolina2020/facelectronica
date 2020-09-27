<?php

require_once "../model/Facture.php";
require "inc/zipfile.inc.php";
require "authapi.php";
require "clear.php";
class App
{
  public $fac;
  public $id;
  public $rspta;
  public $reg;
  public $fecha; //fecha actual.zip
  public $fechac; //fecha consulta parametro
  public $detalle;
  public $clear;
  public $nnota; //input consecutivo
  public $rsptadn;


  //END
  public function __construct()
  {
    $this->fac = new Facture();
    $this->nnota = isset($_POST["notdebito"]) ? ($_POST["notdebito"]) : "";
    $this->rspta = $this->fac->notadebito($this->nnota);
    $this->clear = new Clear();
    date_default_timezone_set("America/Bogota");
    $this->fecha = date("Y-m-d");
  }


  function detalle($iva, $bruto, $neto)
  {

    $this->detalle = array();
    if ($iva == null || $iva = '' || $iva == 0) {
      $iva = 0;
    } else {
      $iva = 19;
    }
    return array(array(
      "tipo" => 1,
      "marca" => "",
      "codigo" => 499901,
      "nombre" =>  'Anulacíon de nota crédito',
      "cantidad" => 1,
      "muestra_producto" => "01",
      "unidad_cantidad" => 'zzz',
      "impuestos" => array(
        array(
          "tipo" => "01",
          "porcentaje" => $iva
        )
      ),
      "descuentos" => array(
        array(
          "razon" => "DescuentoB",
          "valor" => 0.0,
          "porcentaje" => ''
        ),
      ),
      "extensibles" =>
      array(
        "tipo_embalaje" => "",
        "tipo_empaque" => '',
        "bodega" => ''
      ),
      "tipo_gravado" => 1,
      "valor_referencial" => 0.0,
      "valor_unitario_bruto" => $bruto,
      "valor_unitario_sugerido" => $bruto
    ));
    //END

  }
  function Consultas()
  {
    while ($this->reg = $this->rspta->fetch_object()) {

      //validando departamento
      if ($this->reg->departamento == null) {
        $departamento = 20;
      } else {
        $departamento = $this->reg->departamento;
      }
      //Validando la ciudad del cliente.
      if ($this->reg->ciudad == "") {
        $ciudad = 20001;
      } else {
        $ciudad = $this->reg->ciudad;
      }
      //validando el barrio del cliente
      $barrio = $this->reg->barrio;
      //Valindado el telefono del cliente 
      if ($this->reg->telefono == "" || $this->reg->telefono == 0 || $this->reg->telefono == 1) {
        $telefono = 11111111;
      } else {
        $telefono = substr($this->reg->telefono, 0, 10);
      }

      if ($this->reg->tipo_documento == 3 || $this->reg->tipo_documento == 6) {
        $tipo_documento = 91;
      }
      //validandon tipo de regimen
      if ($this->reg->tipo_regimen == null || $this->reg->tipo_regimen == 0) {
        $tipo_regimen = 49;
      } else {
        $tipo_regimen = $this->reg->tipo_regimen;
        if ($tipo_regimen == 48) {
          $responsable_iva = true;
          $obligaciones = 'O-23';
          $tipo_persona = '1';
        } else {
          $responsable_iva = false;
          $obligaciones = 'R-99-PN';
          $tipo_persona = '2';
        }
      }
      //Validando el departamento
      if ($this->reg->departamento == null) {
        $departamento = 20;
      } else {
        $departamento = $this->reg->departamento;
      }
      //Validando nit
      $nit  = str_replace('.', '', $this->reg->nit);
      $datanit = explode('-', $nit);
      $nit = $datanit[0];
      //se cuenta el numero de elementos , si es mayor a 1 , contiene el prefijo y el tipo 
      //de regimen cambia a 31 , si es menor a 1 no contiene el prefijo
      if (count($datanit) == 1) {
        $tipo_identi = 13;
        $digito = '';
      } else {
        $tipo_identi = 31;
        $digito = $datanit[1];
      }
      //end nit
      //Quitando las letras del pedido EJEM : APP123 -> 123
      $pedido = preg_replace('/[^0-9]/', '', $this->reg->pedido);
      //Validar el mensaje de resolucion 
      if ($this->reg->prefijo == "B") {
        $resolucion = "RESOLUCION DIAN 18762009353951 FECHA: 2018/07/25 DEL No. b109728 AL No. b200000 prefijo[B] habilita.";
      } elseif ($this->reg->prefijo == "C") {
        $resolucion = "RESOLUCION DIAN 18762009353951 FECHA: 2018/07/25 DEL No. c17612 AL No. c30000 PREFIJO [C] habilita.";
      } elseif ($this->reg->prefijo == "TAT") {
        $resolucion = "Res. Dian No. 18762010933894 Fecha : 2018-10-25 Del TAT 19229 al tat 30000 habilita FACTURA POR COMPUTADOR.";
      } elseif ($this->reg->prefijo == "F") {
        $resolucion = "RESOLUCION DIAN 240000035883 FECHA: 2015/09/21 DEL No. 776 AL No. 10000 PREFIJO [F] HABILITA.";
      } elseif ($this->reg->prefijo == "V") {
        $resolucion = "Res. Dian No. 240000018505 Fecha : 2009-07-10 Del V-1 al 4000 HABILITA FACTURA POR COMPUTADOR.";
      } elseif ($this->reg->prefijo == "FF") {
        $resolucion = "RESOLUCION DIAN 18762015697813 FECHA: 2019/07/15 DEL No. 30001 AL No. 50000 PREFIJO [FF] habilita.";
      } else if ($this->reg->prefijo == "EB") {
        $resolucion = "RESOLUCION DIAN 18763004383832 FECHA: 2020/02/18 DEL NO. EB1 AL NO. EB394463 PREFIJO[EB] HABILITA.";
      } elseif ($this->reg->prefijo == "EC") {
        $resolucion = "RESOLUCION DIAN 18763004383832 FECHA: 2020/02/18 DEL NO. EC1 AL NO. EC394463 PREFIJO[EC] HABILITA.";
      } elseif ($this->reg->prefijo == "ETT") {
        $resolucion = "RESOLUCION DIAN 18763004383832 FECHA: 2020/02/18 DEL NO. ETT1 AL NO. ETT394463 PREFIJO[ETT] HABILITA.";
      } elseif ($this->reg->prefijo == "EFF") {
        $resolucion = "RESOLUCION DIAN 18763004383832 FECHA: 2020/02/18 DEL NO. EFF1 AL NO. EFF394463 PREFIJO[EFF] HABILITA.";
      } else {
        $resolucion = "";
      }
      $observacion = str_replace("\r\n", '', $this->reg->observacion);

      $data[] = array(
        "nota" =>  $this->clear->cadena($observacion),
        "numero" => $this->reg->consecutivo,
        "codigo_empresa" => 80,
        "tipo_documento" => 92,
        "prefijo" => $this->reg->prefijo,
        'fecha_documento' =>  '2020-09-27',
        "valor_descuento" =>  0,
        "anticipos" => null,
        "valor_ico" => 0.0,
        "valor_iva" => $this->reg->valor_iva,
        "valor_bruto" => $this->reg->valor_bruto,
        "valor_neto" => $this->reg->valor_neto,
        "metodo_pago" => 1,
        "valor_retencion" => $this->reg->valor_retencion,
        "factura_afectada" => strtoupper($this->reg->facturap),
        "fecha_expiracion" =>  $this->reg->fecha_expiracion,
        "subtipo_factura" => '10',
        //CLIENTES ARRAY
        'cliente'     => array(
          "codigo" => $this->reg->codigo,
          "nombres" =>  $this->clear->cadena($this->reg->nombres),
          "apellidos" => $this->clear->cadena($this->reg->nombres),
          "departamento" => $departamento,
          "ciudad" => $ciudad,
          "barrio" => $this->clear->cadena($barrio) . "-" . $this->clear->cadena($this->reg->ubicacion_envio),
          "correo" => $this->reg->correo,
          "telefono" => intval($telefono),
          "direccion" => $this->clear->cadena($this->reg->direccion),
          "documento" => $nit,
          "punto_venta" =>  $this->reg->codigo,
          "obligaciones" => [$obligaciones],
          "razon_social" => $this->clear->cadena($this->reg->nombres),
          "punto_venta_nombre" => $this->clear->cadena($this->reg->punto_venta),
          "codigo_postal" => $this->reg->postal,
          "nombre_comercial" => $this->clear->cadena($this->reg->punto_venta),
          "numero_mercantil" => 0,
          "informacion_tributaria" => "ZY",
          "tipo_persona" => $tipo_persona,
          "tipo_regimen" => $tipo_regimen,
          "es_responsable_iva" => $responsable_iva,
          "tipo_identificacion" => $tipo_identi,

        ),
        'factura'     => array(
          "moneda" => null,
          "subtipo_factura" => "10",
          "intercambio_acordado" => 0.0
        ),
        'pagos'     => array(
          array(
            "fecha" =>  $this->reg->fecha_documento,
            "valor" => 0.0,
            "metodo_pago" => 1,
            "detalle_pago" => "ZZZ"

          )
        ),
        'descuentos'     => array(
          array(
            "razon" => null,
            "valor" => 0,
            "codigo" => 11,
            "porcentaje" => 0.0
          )
        ),
        'extensibles'     => array(
          'digito' => $digito,
          "resolucion" => $resolucion,
          "asesor" => $this->clear->cadena($this->reg->asesor),
          "pedido" => $pedido,
          "zona" => $this->reg->zona,
          'cantidad_productos'     =>  '0'
        ),
        'nota_debito'     => array(
          "razon" => 4,
          "factura" =>   strtoupper($this->reg->facturap),
          "id_felam" => '',
          "tipo_documento" => 32,
          "descripcion_razon" =>  $this->clear->cadena($observacion),
        ),
        //productos
        'productos'  =>  $this->detalle($this->reg->valor_iva, $this->reg->valor_bruto, $this->reg->valor_neto)

      );
    }
    //end productos
    if (empty($data)) {
      header("Location: ../view/errnote.php");;
      die();
    } else {
      echo json_encode($data);
    }
  }
}

$app = new App();
$app->Consultas();