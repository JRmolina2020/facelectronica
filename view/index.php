<?php
require 'header.php';
require '../controller/listfacture.php'
?>
<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#home">Facturas</a></li>
  <li> <a data-toggle="tab" href="#home2">Factura Única</a></li>
  <li><a data-toggle="tab" href="#ngeneral">Nota crédito general </a></li>
  <li><a data-toggle="tab" href="#menunota">Nota crédito Única </a></li>
  <li><a data-toggle="tab" href="#menunotadebito">Nota debito Única </a></li>
  <li> <a data-toggle="tab" href="#jsonx">Json</a></li>
</ul>
<div class="tab-content">
  <div id="home" class="tab-pane fade in active">
    <H3>FACTURAS GENERAL</H3>
    <div class="row mt-5">
      <div class="col-lg-6 col-md-12">
        <form method="post" action="../controller/facture.php">
          <div class="row">
            <div class="col-lg-10">
              <div class="form-group">
                <input class="form-control" placeholder="Digitar fecha de facturacion" type="text" readonly name="fecha"
                  id="datepicker">
              </div>
            </div>
            <div class="col-lg-12">
              <input class="btn btn-danger" type="submit" value="Enviar facturas">
            </div>
          </div>
        </form>
      </div>
    </div><!-- Fin row -->
    <br><br>
    <div class="row">
      <div class="mt-5 col-lg-4">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>CANTIDAD</th>
              <th>TOTAL</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td scope="row"><?php echo $cantidad ?></td>
              <td><?php echo number_format($total, 0, '', '.'); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div id="home2" class="tab-pane fade ">
    <H3>FACTURA ÚNICA</H3>
    <div class="row mt-5">
      <div class="col-lg-6 col-md-12">
        <form method="post" action="../controller/facture.php" autocomplete="off">
          <div class="row">
            <div class="col-lg-10">
              <div class="form-group">
                <div class="form-group">
                  <label for=""></label>
                  <input type="text" class="form-control" name="facturaunica" id="facturaunica"
                    placeholder="NUMERO DE FACTURA: EJEM:B1229">
                </div>
              </div>
            </div>
            <div class="col-lg-12">
              <input class="btn btn-danger" type="submit" value="Enviar factura unica">
            </div>
          </div>
        </form>
      </div>
    </div><!-- Fin row -->
    <br><br>
    <div class="mt-5">
    </div>
    <table id="example" class="table table-striped table-bordered" style="width:100%">
      <thead>
        <tr>
          <th>FECHA</th>
          <th>NOFACTURA</th>
          <th>CLIENTE</th>
          <th>TOTAL</th>
          <th>OP</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($reg = $rspta->fetch_object()) {
          $nofactura = $reg->NOFACTURA;
          $fecha = $reg->FECHA;
          $cliente = $reg->CLIENTE;
          $total = $reg->TOTAL;
          $id = $reg->ID;

        ?>
        <tr>
          <td scope="row"><?php echo $fecha ?></td>
          <td><?php echo $nofactura ?></td>
          <td><?php echo $cliente; ?></td>
          <td><?php echo number_format($total, 0, '', '.'); ?></td>
          <td><button type="button" onclick="Store(<?php echo "'$nofactura'"; ?>)"
              class="btn btn-primary btn-xs">E</button></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div class="mt-5">
    </div>
    <br><br>
  </div>
  <div id="menunota" class="tab-pane fade">
    <H3>NOTA CRÉDITO ÚNICA</H3>
    <div class="row mt-5">
      <div class="col-lg-6 col-md-12">
        <!-- Contenido -->
        <form method="post" action="../controller/note_credit.php" autocomplete="off">
          <div class="row">
            <div class="col-lg-10">
              <label>consecutivo de la NC</label>
              <div class="form-group">
                <input class="form-control" id="notaunica" name="notaunica"
                  placeholder="Digitar # de la nota(Consecutivo) Ejem:43183" type="text">
              </div>
            </div>
            <div class="col-lg-12">
              <input class="btn btn-primary" type="submit" value="Enviar nota unica">
            </div>
          </div>
        </form>
      </div>
    </div>
    <br><br>
    <div class="mt-5">
    </div>
    <div class="table-responsive">
      <table id="examplenote" class="table table-striped table-bordered" style="width:100%">
        <thead>
          <tr>
            <th>FECHA</th>
            <th>TIPO</th>
            <th>CONSECUTIVO</th>
            <th>NOFACTURA</th>
            <th>CLIENTE</th>
            <th>TOTAL</th>
            <th>OP</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($regn = $rsptanote->fetch_object()) {
            $consecutivo = $regn->CONSECUTIVO;
            $nofactura = $regn->NOFACTURA;
            $fecha = $regn->FECHA;
            $cliente = $regn->CLIENTE;
            $total = $regn->TOTAL;
            $tipo = $regn->TIP;
            if ($tipo == 3) {
              $nombretipo = 'Devoluciones';
            } else {
              $nombretipo = 'Sin codigo';
            }
          ?>
          <tr>
            <td scope="row"><?php echo $fecha ?></td>
            <td>
              <span class="badge badge-warning"><?php echo $nombretipo ?></span>
            </td>
            <td><?php echo $consecutivo ?></td>
            <td><?php echo $nofactura ?></td>
            <td><?php echo $cliente; ?></td>
            <td><?php echo number_format($total, 0, '', '.'); ?></td>
            <td><button type="button" onclick="Storenote(<?php echo "'$consecutivo'"; ?>)"
                class="btn btn-warning btn-xs">E</button></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <div class="alert alert-danger mt-5" role="alert">
      <strong>Recuerda</strong> Primero haber enviado y validado La factura En Felam</strong>
    </div>
  </div>
  <div id="ngeneral" class="tab-pane fade">
    <H3>NOTAS CRÉDITO GENERAL</H3>
    <div class="row mt-5">
      <div class="col-lg-6 col-md-12">
        <form method="post" action="../controller/note_credit.php">
          <div class="row">
            <div class="col-lg-10">
              <div class="form-group">
                <input class="form-control" placeholder="Digitar fecha de envio" type="text" readonly name="fechanota"
                  id="datepickernota">
              </div>
            </div>
            <div class="col-lg-12">
              <input class="btn btn-danger" type="submit" value="Enviar notas créditos">
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="alert alert-danger mt-5" role="alert">
      <strong>NOTA:</strong>Digite la fecha del dia en curso y realice el envio, <strong>Verifique la fecha antes de
        enviar</strong>
    </div>
  </div>
  <div id="menunotadebito" class="tab-pane fade">
    <H3>NOTA DÉBITO ÚNICA</H3>
    <div class="row mt-5">
      <div class="col-lg-6 col-md-12">
        <form method="post" action="../controller/note_debit.php" autocomplete="off">
          <div class="row">
            <div class="col-lg-10">
              <label>consecutivo de la Nota débito</label>
              <div class="form-group">
                <input class="form-control" id="notaunica" name="notaunica"
                  placeholder="Digitar # de la nota(Consecutivo) Ejem:43183" type="text">
              </div>
            </div>
            <div class="col-lg-12">
              <input class="btn btn-primary" type="submit" value="Enviar nota debito unica">
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div id="jsonx" class="tab-pane fade">
    <div class="row">
      <div class="col-lg-4 mt-5">
        <form action="../controller/facturejson.php" method="POST">
          <div class="form-group">
            <label for="">Digite el número de factura</label>
            <input type="text" class="form-control" name="fan" id="fan">
          </div>
          <button type="submit" class="btn btn-primary btn-sm">VER</button>
        </form>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-4 mt-5">
        <form action="../controller/notajson.php" method="POST">
          <div class="form-group">
            <label for="">Digite el consecutivo de la nota crédito</label>
            <input type="text" class="form-control" name="not" id="not">
          </div>
          <button type="submit" class="btn btn-danger btn-sm">VER</button>
        </form>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-4 mt-5">
        <form action="../controller/notadebitjson.php" method="POST">
          <div class="form-group">
            <label for="">Digite el consecutivo de la nota débito</label>
            <input type="text" class="form-control" name="notdebito" id="notdebito">
          </div>
          <button type="submit" class="btn btn-success btn-sm">VER</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php
require 'footer.php';
?>

<script>
$(function() {
  $("#datepicker").datepicker({
    format: 'yyyy-mm-dd',
    language: 'es'
  });
  $("#datepickernota").datepicker({
    format: 'yyyy-mm-dd',
    language: 'es'
  });
});
</script>
<script type="text/javascript">
$(document).ready(function() {
  $('#example').DataTable();
});
$(document).ready(function() {
  $('#examplenote').DataTable();
});

function Store(id) {
  document.getElementById("facturaunica").value = id;
}

function Storenote(id) {
  document.getElementById("notaunica").value = id;
}
</script>