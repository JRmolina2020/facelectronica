<?php
require "../config/conexion.php";

class Facture
{
    public function __construct()
    {
    }
    public function index() //TABLA FACTURAS
    {
        $sql = "SELECT
        V.ID AS ID,V.NOFACTURA as NOFACTURA,V.FEC_COMPRA as FECHA ,C.REPRESENTANTE as CLIENTE,
        V.TOTAL as TOTAL 
        FROM ventas V
        INNER JOIN clientes C
        ON V.TERCERO = C.CODIGO
        INNER JOIN tipos_facturas TP
        ON V.IDTIPO = TP.ID 
        WHERE DATE(V.FEC_COMPRA)=curdate() and TP.ID NOT IN(7)";
        return ejecutarConsulta($sql);
    }
    public function tablenote()
    {
        $sql = "SELECT
        CO.ID as ID,CO.IDTIPO as TIP,CO.CONSECUTIVO as CONSECUTIVO,CO.NOFACTURA as NOFACTURA,CO.FEC_COMPRA as FECHA,
        C.REPRESENTANTE as CLIENTE,CO.TOTAL as TOTAL 
        FROM compras CO
        INNER JOIN ventas V
        ON V.NOFACTURA = CO.NOFACTURA
        INNER JOIN clientes C
        ON V.TERCERO = C.CODIGO
        WHERE DATE(CO.FEC_COMPRA)=curdate() and CO.TOTAL >0 and CO.IDTIPO BETWEEN 3 and 6 and V.IDTIPO NOT IN(7) and V.IDTIPO NOT IN(1) and V.IDTIPO NOT IN(2)";
        return ejecutarConsulta($sql);
    }

    public function indicador()
    {
        $sql = "SELECT COUNT(V.ID) as CANTIDAD,SUM(V.TOTAL) as TOTAL 
        FROM ventas V
        INNER JOIN clientes C
        ON V.TERCERO = C.CODIGO
        INNER JOIN tipos_facturas TP
        ON V.IDTIPO = TP.ID 
        WHERE DATE(V.FEC_COMPRA)=curdate() and TP.ID NOT IN(7)";
        return ejecutarConsulta($sql);
    }

    public function cabezera($fecha)
    {
        $sql = "SELECT 
        V.ID as IDF, V.NOFACTURA as numero,V.FEC_COMPRA as fecha_documento,TP.PREFIJO as prefijo,
        V.NOFACTURA as facturap,V.PEDIDO as pedido, 
        V.IDFORMPAGO as metodo_pago, FO.DESCRIPCION as manera_pago,
        V.SUBTOTAL as valor_bruto, V.IVA as valor_iva,
        V.RETEFUENTE as valor_retencion,DCTO as valor_descuento, 
        V.TOTAL as valor_neto,V.FEC_VENC as fecha_expiracion,V.OBSERVACION as observacion,
        C.CODIGO as codigo, C.NIT as nit ,C.REPRESENTANTE as nombres ,C.TIPO_REGIMEN as tipo_regimen,
        C.COD_POSTAL as postal,
        CI.CODDIAN as ciudad,CI.CIUDAD as ubicacion_envio ,B.NOMBRE as barrio,
        C.TELEFONOS as telefono,C.DIRECCION as direccion,
        C.ID as documento, C.EMPRESA as punto_venta,C.EMAIL as correo,
        C.DPTO as departamento,C.CLIENTE as tipo_persona,U.NOMBRE as asesor,U.DOC as zona
        FROM ventas V 
        INNER JOIN clientes C 
        ON V.TERCERO = C.CODIGO 
        LEFT JOIN barrios B 
        ON C.BARRIO = B.codigo 
        INNER JOIN tipos_facturas TP
        ON V.IDTIPO = TP.ID 
        INNER JOIN usuarios U
        ON U.USUARIO = V.VENDEDOR
        INNER JOIN ciudades CI
        ON CI.CODIGO = C.CIUDAD
        INNER JOIN formas_pagos FO
        ON FO.ID = V.IDFORMPAGO
        WHERE  V.FEC_COMPRA = '$fecha' and TP.ID NOT IN(7)";
        return ejecutarConsulta($sql);
    }
    public function cabezeraunica($nofactura)
    {
        $sql = "SELECT 
        V.ID as IDF, V.NOFACTURA as numero,V.FEC_COMPRA as fecha_documento,TP.PREFIJO as prefijo,
        V.NOFACTURA as facturap,V.PEDIDO as pedido, 
        V.IDFORMPAGO as metodo_pago, FO.DESCRIPCION as manera_pago,
        V.SUBTOTAL as valor_bruto, V.IVA as valor_iva,
        V.RETEFUENTE as valor_retencion,DCTO as valor_descuento, 
        V.TOTAL as valor_neto,V.FEC_VENC as fecha_expiracion,V.OBSERVACION as observacion,
        C.CODIGO as codigo, C.NIT as nit ,C.REPRESENTANTE as nombres ,C.TIPO_REGIMEN as tipo_regimen,
        C.COD_POSTAL as postal,CI.CODDIAN as ciudad,CI.CIUDAD as ubicacion_envio ,B.NOMBRE as barrio,
        C.TELEFONOS as telefono,C.DIRECCION as direccion,
        C.ID as documento, C.EMPRESA as punto_venta,C.EMAIL as correo,
        C.DPTO as departamento,C.CLIENTE as tipo_persona,U.NOMBRE as asesor,U.DOC as zona
        FROM ventas V 
        INNER JOIN clientes C 
        ON V.TERCERO = C.CODIGO 
        LEFT JOIN barrios B 
        ON C.BARRIO = B.codigo 
        INNER JOIN tipos_facturas TP
        ON V.IDTIPO = TP.ID 
        INNER JOIN usuarios U
        ON U.USUARIO = V.VENDEDOR
        INNER JOIN ciudades CI
        ON CI.CODIGO = C.CIUDAD
        INNER JOIN formas_pagos FO
        ON FO.ID = V.IDFORMPAGO
        WHERE TP.ID NOT IN(7) AND V.NOFACTURA = '$nofactura'";
        return ejecutarConsulta($sql);
    }

    public function notacreditogeneral($fecha)
    {
        $sql = "SELECT 
        CO.ID AS id, TP.PREFIJO as prefijo, CO.CONSECUTIVO as consecutivo, 
        CO.NOFACTURA as facturap,CO.NOFACTURA as vnot,
        CO.FEC_COMPRA as fecha_documento,CO.IDTIPO as tipo_documento,
        CO.SUBTOTAL as valor_bruto, CO.IVA as valor_iva,
        CO.RETEFUENTE as valor_retencion,CO.TOTAL as valor_neto,
        CO.FEC_VENC as fecha_expiracion,CO.OBSERVACION as observacion,
        FO.DESCRIPCION as manera_pago,
        C.CODIGO as codigo,C.NIT as nit,C.REPRESENTANTE as nombres,
        C.TIPO_REGIMEN as tipo_regimen,C.COD_POSTAL as postal,
        C.TELEFONOS as telefono,C.DIRECCION as direccion,
        C.ID as documento, C.EMPRESA as punto_venta,
        C.DPTO as departamento,C.CLIENTE as tipo_persona,C.EMAIL as correo,
        CI.CODDIAN as ciudad,CI.CIUDAD as ubicacion_envio ,B.NOMBRE as barrio,
        U.NOMBRE as asesor,U.DOC as zona,V.PEDIDO as pedido
        FROM compras  CO
        INNER JOIN ventas V
        ON V.NOFACTURA = CO.NOFACTURA
        INNER JOIN formas_pagos FO
        ON FO.ID = V.IDFORMPAGO
        INNER JOIN tipos_facturas TP
        ON V.IDTIPO = TP.ID 
        INNER JOIN usuarios U
        ON U.USUARIO = V.VENDEDOR
        INNER JOIN clientes C
        ON V.TERCERO = C.CODIGO
        LEFT JOIN barrios B 
        ON C.BARRIO = B.CODIGO
        INNER JOIN ciudades CI
        ON CI.CODIGO = C.CIUDAD
        WHERE CO.FEC_COMPRA  = '$fecha' and TP.ID NOT IN(1) and TP.ID NOT IN(2) and TP.ID NOT IN(7) and CO.TOTAL >0 and CO.IDTIPO BETWEEN 3 and 6";
        return ejecutarConsulta($sql);
    }

    public function notacreditounica($numeronota)
    {
        $sql = "SELECT 
        CO.ID AS id, TP.PREFIJO as prefijo, CO.CONSECUTIVO as consecutivo, 
        CO.NOFACTURA as facturap,CO.NOFACTURA as vnot,
        CO.FEC_COMPRA as fecha_documento,CO.IDTIPO as tipo_documento,
        CO.SUBTOTAL as valor_bruto, CO.IVA as valor_iva,
        CO.RETEFUENTE as valor_retencion,CO.TOTAL as valor_neto,
        CO.FEC_VENC as fecha_expiracion,CO.OBSERVACION as observacion,
        FO.DESCRIPCION as manera_pago,
        C.CODIGO as codigo,C.NIT as nit,C.REPRESENTANTE as nombres,
        C.TIPO_REGIMEN as tipo_regimen, C.COD_POSTAL as postal,
        C.TELEFONOS as telefono,C.DIRECCION as direccion,
        C.ID as documento, C.EMPRESA as punto_venta,
        C.DPTO as departamento,C.CLIENTE as tipo_persona,C.EMAIL as correo,
        CI.CODDIAN as ciudad,CI.CIUDAD as ubicacion_envio ,B.NOMBRE as barrio,
        U.NOMBRE as asesor,U.DOC as zona,V.PEDIDO as pedido
        FROM compras  CO
        INNER JOIN ventas V
        ON V.NOFACTURA = CO.NOFACTURA
        INNER JOIN formas_pagos FO
        ON FO.ID = V.IDFORMPAGO
        INNER JOIN tipos_facturas TP
        ON V.IDTIPO = TP.ID 
        INNER JOIN usuarios U
        ON U.USUARIO = V.VENDEDOR
        INNER JOIN clientes C
        ON V.TERCERO = C.CODIGO
        LEFT JOIN barrios B 
        ON C.BARRIO = B.CODIGO
        INNER JOIN ciudades CI
        ON CI.CODIGO = C.CIUDAD
        WHERE CO.CONSECUTIVO  = '$numeronota'  and TP.ID NOT IN(1) and TP.ID NOT IN(2) and TP.ID NOT IN(7) and CO.TOTAL >0 and CO.IDTIPO BETWEEN 3 and 6";
        return ejecutarConsulta($sql);
    }
    //DETALLE VENTA
    public function detalle($id)
    {
        $sql = "SELECT P.IDMARCA as tipo,P.CODIGO as codigo,P.NOMBRE as nombre,
        VD.UNID as cantidad ,VD.CAJA as caja, VD.VRCAJA as valor_caja,
        VD.IDBODEGA as bodega, VD.VRUNITARIO  as valor_referencial,VD.VRUNITARIO as valor_unitario_bruto, 
        VD.TOTAL as subtotal,VD.IVA as iva,VD.DESCUENTOA as descuentoA,VD.DESCUENTOB as descuentoB,
        VD.TOTAL as totalvd
        FROM ventas_detalles VD
        INNER JOIN productos P 
        ON VD.IDPROD = P.REFERENCIA
        WHERE VD.IDVENTA ='$id'";
        return ejecutarConsulta($sql);
    }

    //DETALLE COMPRA
    public function compraDetalle($id)
    {
        $sql = "SELECT 
        P.CODIGO as codigo,P.NOMBRE as nombre,
        CD.UNID as cantidad,CD.CAJA as caja,
        CD.IDBODEGA as bodega,CD.VRUNITARIO as valor_referencial,
        CD.VRUNITARIO as valor_unitario_bruto,CD.TOTAL as subtotal,
        CD.IVA as iva,CD.TOTAL as totalcd
        FROM compras_detalles CD
        INNER JOIN productos P 
        ON CD.IDPROD = P.REFERENCIA
        WHERE CD.IDCOMPRA = '$id'";
        return ejecutarConsulta($sql);
    }
    public function compraDetalleSincodigo($id)
    {
        $sql = "SELECT P.NOMBRE as nombre, P.CODIGO as codigo,P.UNID_EMPAQ as tempaque,
        CD.UNID as cantidad,CD.CAJA as caja,CD2.UNID as cantidad2,CD2.CAJA as caja2,
        CD.IDBODEGA as bodega,CD.VRUNITARIO as valor_referencial,
        CD.VRUNITARIO as valor_unitario_bruto,CD.TOTAL as subtotal,CD2.TOTAL as cd2total,
        CD.IVA as iva,CD.TOTAL as totalcd
        FROM compras_detalles CD
        INNER JOIN compras_detalles_dev CD2
        ON CD.IDCOMPRA = CD2.IDCOMPRA
        INNER JOIN productos P
        ON P.REFERENCIA = CD2.IDPROD
        WHERE CD.IDCOMPRA = '$id' AND CD.TOTAL >0";
        return ejecutarConsulta($sql);
    }

    public function cantidadproductoCompradetalle($id)
    {
        $sql = "SELECT COUNT(*) as tot FROM compras_detalles_dev WHERE IDCOMPRA = '$id'";
        return ejecutarConsulta($sql);
    }
    public function traertotal($id)
    {
        $sql = "SELECT TOTAL as total,IVA as iva FROM compras_detalles WHERE IDCOMPRA = '$id' AND TOTAL >0";
        return ejecutarConsulta($sql);
    }

    //contar las cantidades de linea de los productos
    public function countlineafactura($id)
    {
        $sql = "SELECT COUNT(*) as totallinea FROM ventas_detalles VD
        INNER JOIN productos P 
        ON VD.IDPROD = P.REFERENCIA
        WHERE VD.IDVENTA ='$id'";
        return ejecutarConsulta($sql);
    }

    public function countlineanotasdevolucion($id)
    {
        $sql = "SELECT COUNT(*) as totallinea FROM compras_detalles CD
        INNER JOIN productos P 
        ON CD.IDPROD = P.REFERENCIA
        WHERE CD.IDCOMPRA = '$id'";
        return ejecutarConsulta($sql);
    }
    public function countlineanotasincodigo($id)
    {
        $sql = "SELECT COUNT(*) as totallinea FROM compras_detalles CD
        INNER JOIN compras_detalles_dev CD2
        ON CD.IDCOMPRA = CD2.IDCOMPRA
        INNER JOIN productos P
        ON P.REFERENCIA = CD2.IDPROD
        WHERE CD.IDCOMPRA = '$id' AND CD.TOTAL >0";
        return ejecutarConsulta($sql);
    }
    public function notadebito($numeronota)
    {
        $sql = "SELECT 
        CO.ID AS id, TP.PREFIJO as prefijo, CO.CONSECUTIVO as consecutivo, 
        CO.FACT as facturap,CO.FEC_COMPRA as fecha_documento,CO.IDTIPO as tipo_documento,
        CO.SUBTOTAL as valor_bruto, CO.IVA as valor_iva,
        CO.RETEFUENTE as valor_retencion,CO.TOTAL as valor_neto,
        CO.FEC_VENC as fecha_expiracion,CO.OBSERVACION as observacion,
        FO.DESCRIPCION as manera_pago,
        C.CODIGO as codigo,C.NIT as nit,C.REPRESENTANTE as nombres,
        C.TIPO_REGIMEN as tipo_regimen, C.COD_POSTAL as postal,
        C.TELEFONOS as telefono,C.DIRECCION as direccion,
        C.ID as documento, C.EMPRESA as punto_venta,
        C.DPTO as departamento,C.CLIENTE as tipo_persona,C.EMAIL as correo,
        CI.CODDIAN as ciudad,CI.CIUDAD as ubicacion_envio ,B.NOMBRE as barrio,
        U.NOMBRE as asesor,U.DOC as zona,V.PEDIDO as pedido
        FROM compras  CO
        INNER JOIN ventas V
        ON V.NOFACTURA = CO.FACT
        INNER JOIN formas_pagos FO
        ON FO.ID = V.IDFORMPAGO
        INNER JOIN tipos_facturas TP
        ON V.IDTIPO = TP.ID 
        INNER JOIN usuarios U
        ON U.USUARIO = V.VENDEDOR
        INNER JOIN clientes C
        ON V.TERCERO = C.CODIGO
        LEFT JOIN barrios B 
        ON C.BARRIO = B.CODIGO
        INNER JOIN ciudades CI
        ON CI.CODIGO = C.CIUDAD
        WHERE CO.CONSECUTIVO  = '$numeronota' and CO.TOTAL >0 and CO.IDTIPO BETWEEN 4 AND 5";
        return ejecutarConsulta($sql);
    }
}