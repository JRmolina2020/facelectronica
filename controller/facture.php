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
    public $rsptacount;
    public $reg;
    public $fecha; //fecha actual.zip
    public $fechac; //fecha consulta parametro
    public $nofac; //parametro
    public $detalle;
    public $clear;
    //variables globales para producto
    public $tipo; //tipo_producto
    //descuentos
    public $descuento;
    public $cantidadunit;
    public $cantidadcajaunit;
    public $valor_unit;
    public $valor_unitcaja;

    public function __construct()
    {
        $this->fac = new Facture();
        //parametro para la consulta por fecha
        $this->fechac = isset($_POST["fecha"]) ? ($_POST["fecha"]) : "";
        $this->nofac = isset($_POST["facturaunica"]) ? ($_POST["facturaunica"]) : "";
        if (empty($this->nofac)) {
            $this->rspta = $this->fac->cabezera($this->fechac);
        } else {
            $this->rspta = $this->fac->cabezeraunica($this->nofac);
        }
        date_default_timezone_set("America/Bogota");
        $this->fecha = date("Y-m-d");
        //clear function
        $this->clear = new Clear();
        //inicializaciones
        $this->tipo = 1;
        $this->descuento = 0;
    }

    //consultar el numero de lineas de productos que contiene la factura
    function countlineafactura($id)
    {
        $totlinea = $this->fac->countlineafactura($id);
        $regc = $totlinea->fetch_object();
        return $regc->totallinea;
    }

    function detalle($id)
    {
        $id = $id;
        $this->detalle = array();
        $this->rsptad = $this->fac->detalle($id);
        while ($this->reg = $this->rsptad->fetch_object()) {

            //Valindando la cantidad de productos si es en caja o si es por unidad
            if ($this->reg->cantidad == 0) { //si la cantidad und es 0 es por que es una caja
                $cantidad = $this->reg->caja; //le asignamos a la cantidad el total de cajas
                $valor_unitario_bruto = $this->reg->valor_caja;
                $embalaje = 'caja';
                //descuento
                $this->valor_unitcaja = $valor_unitario_bruto;
                $this->cantidadcajaunit = $cantidad;
            } else {
                $cantidad = $this->reg->cantidad;
                $this->cantidadunit = $cantidad;
                $embalaje = 'und';
                if (
                    $this->reg->valor_unitario_bruto < 0.01 || $this->reg->valor_unitario_bruto == ""
                    || $this->reg->valor_unitario_bruto == 0
                ) {
                    $valor_unitario_bruto = 0.01;
                    $this->tipo = 4;
                } else {
                    $valor_unitario_bruto = $this->reg->valor_unitario_bruto;
                    $this->valor_unit = $valor_unitario_bruto; //obteniedo el valor de la unidad para el descuento 
                }
            }
            //Validando si el producto dado es regalo o no.
            if ($this->reg->totalvd == 0) {
                $this->tipo = 4;
                $this->reg->descuentoA = 0.0;
                $this->reg->descuentoB = 0.0;
                $valor_unitario_bruto = 0.01;
            } else {
                $this->tipo = 1;
                $this->reg->descuentoA =  $this->reg->descuentoA;
                $this->reg->descuentoB =  $this->reg->descuentoB;
            }

            //VALIDANDO DESCUENTO
            if ($this->reg->cantidad == 0) { //SI LA CANTIDAD ES 0 ES POR QUE ES UNA CAJA
                if ($this->reg->descuentoA == 0) {
                    $base = 0;
                } else {
                    $base1 = $this->valor_unitcaja * $this->cantidadcajaunit;
                    $db = $base1 * $this->reg->descuentoB / 100;
                    $base = $db;
                    $base = $base1 - $db;
                }
            } else {
                if ($this->reg->descuentoA == 0) { // ES UNA UNIDAD
                    $base = 0;
                } else {
                    $base1 = $this->valor_unit * $this->cantidadunit;
                    $db = $base1 * $this->reg->descuentoB / 100;
                    $base = $db;
                    $base = $base1 - $db;
                }
            }

            //##########################################################################################
            $this->detalle[] = array(
                "tipo" => $this->tipo,
                "marca" => "",
                "codigo" => $this->reg->codigo,
                "nombre" =>  $this->clear->cadena($this->reg->nombre),
                "cantidad" => $cantidad,
                "muestra_producto" => "01",
                "unidad_cantidad" => $embalaje,
                "impuestos" => array(
                    array(
                        "tipo" => "01",
                        "porcentaje" => $this->reg->iva
                    )
                ),
                "descuentos" => array(
                    array(
                        "razon" => "DescuentoB",
                        "valor" => 0.0,
                        "porcentaje" =>  $this->reg->descuentoB
                    ),
                    array(
                        "razon" => "DescuentoA",
                        "valor" => 0.0,
                        "base" => round($base, 2),
                        "porcentaje" =>  $this->reg->descuentoA
                    ),
                ),
                "extensibles" =>
                array(
                    "tipo_embalaje" => "",
                    "tipo_empaque" => $embalaje,
                    "bodega" => $this->reg->bodega
                ),

                "tipo_gravado" => 1,
                "valor_referencial" => 0.0,
                "valor_unitario_bruto" => $valor_unitario_bruto,
                "valor_unitario_sugerido" => $this->reg->valor_caja
            );
        }
        return ($this->detalle);
    }
    //#################################################################################
    function Consultas()
    {
        //VALIDACIONES
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
                $telefono = substr($this->reg->telefono, 0, 10); //recortando telefonos a 10 digitos
            }
            //validando el metodo de pago
            if (
                $this->reg->metodo_pago == 1 || $this->reg->metodo_pago == 13
                ||  $this->reg->metodo_pago == 14
            ) {
                $metodo_pago = 1; //contado
            } else {
                $metodo_pago = 2; //credito
            }
            //Vvalidando el tipo de regimen
            if ($this->reg->tipo_regimen == null || $this->reg->tipo_regimen == 0) {
                $tipo_regimen = 49;
            } else {
                $tipo_regimen = $this->reg->tipo_regimen;
                if ($tipo_regimen == 48) {
                    $responsable_iva = true;
                    $tipo_persona = '1';
                    $obligaciones = 'O-23';
                } else {
                    $responsable_iva = false;
                    $tipo_persona = '2';
                    $obligaciones = 'R-99-PN';
                }
            }

            //quintando prefijo al numero de la factura EJEM: B123 -> 123
            $numero = preg_replace('/[^0-9]/', '', $this->reg->numero);
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
            //Quitando las letras del pedido EJEM : APP123 -> 123
            $pedido = preg_replace('/[^0-9]/', '', $this->reg->pedido);
            //end
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
            //VALIDAR EL CORREO
            if (strlen($this->reg->correo) < 6) {
                $correo = "";
            } else {
                $correo = $this->reg->correo;
            }
            //ARRAYS
            $data[] =  array(
                "nota" =>  $this->clear->cadena($observacion),
                "numero" => $numero,
                "codigo_empresa" =>  80,
                "tipo_documento" => '01',
                "prefijo" =>  $this->reg->prefijo,
                'fecha_documento' => '2020-09-27', //$this->reg->fecha_documento,
                "valor_descuento" =>  $this->reg->valor_descuento,
                "anticipos" => null,
                "valor_ico" => 0.0,
                "valor_iva" => $this->reg->valor_iva,
                "valor_bruto" => $this->reg->valor_bruto,
                "valor_neto" => $this->reg->valor_neto,
                "metodo_pago" => $metodo_pago,
                "valor_retencion" => $this->reg->valor_retencion,
                "factura_afectada" => 0,
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
                    "correo" => $correo,
                    "telefono" => intval($telefono),
                    "direccion" =>  $this->clear->cadena($this->reg->direccion),
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
                        "metodo_pago" => $metodo_pago,
                        "detalle_pago" => "ZZZ",
                    )
                ),
                'descuentos'     => array(
                    array(
                        "razon" => null,
                        "valor" => $this->reg->valor_descuento,
                        "codigo" => 11,
                        "porcentaje" => 0.0
                    )
                ),
                'extensibles'     => array(
                    'digito' => $digito,
                    "resolucion" => $resolucion,
                    "manera_pago" => $this->reg->manera_pago,
                    "zona" => $this->reg->zona,
                    "asesor" => $this->clear->cadena($this->reg->asesor),
                    "pedido" => $pedido,
                    "peso" => 0.0,
                    "orden" => 0,
                    "canastas" => 0,
                    "planilla" => "",
                    "logistica" => "",
                    "recibo_caja" => 0.0,
                    "distribucion" => "",
                    "asesor_numero" => 0,
                    "logistica_numero" => 0,
                    'cantidad_productos'     =>  $this->countlineafactura($this->reg->IDF),
                    "distribucion_numero" => 0,
                ),
                'nota_debito'     => array(
                    "razon" => 4,
                    "factura" => $this->reg->facturap,
                    "id_felam" => 0,
                    "tipo_documento" => "",
                    "descripcion_razon" => ""
                ),
                'nota_credito'     => array(
                    "razon" => 6,
                    "factura" => $this->reg->facturap,
                    "id_felam" => 0,
                    "tipo_documento" => "20",
                    "descripcion_razon" => "En este apartado se genera la nota credito con fines internos entre la empresa y el cliente referente"
                ),
                //productos
                'productos'     =>  $this->detalle($this->reg->IDF)
            );
        }

        //END
        if (empty($data)) {
            header("Location: ../view/errfacture.php");;
            die();
        } else {
            // echo json_encode($data, JSON_UNESCAPED_UNICODE);
            $jstring =  json_encode($data, true);
            $zip = new ZipArchive();
            $filename = "archivo-" . $this->fecha . ".zip";
            if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
                exit("cannot open <$filename>\n");
            }
            $zip->addFromString("archivo-" . $this->fecha . ".txt", $jstring);
            $zip->close();
            $api = new Login();
            $api->Uploader($filename);
        }
    }
}

$app = new App();
$app->Consultas();