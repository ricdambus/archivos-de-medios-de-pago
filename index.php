<?php
require "classes\processFile.php";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Tabla archivos de medios de pagos</title>
    <!-- Bootstrap core CSS -->
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    
  </head>
 
  <body>
 
    <!-- Static navbar -->
    <div class="navbar navbar-default navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="#">Tabla archivos de medios de pagos</a>
        </div>
      </div>
    </div>
 
    <div class="container">
          <div class="row">
			<div class="col-lg-12">
				<div class="card">
					<div class="card-body">
					   <form class="well" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data" id="uploadForm">
						  <div class="form-group">
							<label for="file">Seleccione archivo de medio de pago</label>
							<input type="file" name="file">
							<p class="help-block">Se admiten archivos de texto.</p>
						  </div>
						  <input type="submit" class="btn btn-lg btn-primary" value="Procesar">
						</form>
					</div>
				</div>
			</div>
          </div> 
	<?php
	
	if(isset($_FILES) && strlen($_FILES["file"]["tmp_name"])) {

		$processFile = new processFile($_FILES);
		$data = $processFile->returnData();
		
		//var_dump($data);

	?>
		<div class="row">
		<div class="col-lg-12">
		<table class="table">
		  <thead>
			<tr>
			  <th scope="col">#</th>
			  <th scope="col">Nro. transacción</th>
			  <th scope="col">Monto</th>
			  <th scope="col">Identificador</th>
			  <th scope="col">Fecha de Pago</th>
			  <th scope="col">Medio de Pago</th>
			</tr>
		  </thead>
		  <tbody>
	<?php
	foreach($data["data"] as $index => $row) {
	?>
			<tr>
			  <th scope="row"><?php echo $index; ?></th>
			  <td><?php echo $row["transactionNum"]; ?></td>
			  <td>$<?php echo $row["amount"]; ?></td>
			  <td><?php echo $row["identifier"]; ?></td>
			  <td><?php echo $row["paymentDate"]; ?></td>
			  <td><?php echo $row["paymentMethod"]; ?></td>
			</tr>
	<?php
	}
	?>
		  </tbody>
		  <tfoot>
			<tr>
				<td scope="col" colspan="6">Total registros: <?php echo $data["totals"]["count"]; ?></td>
			</tr>
			<tr>
				<td scope="col" colspan="6">Monto total cobranza: $<?php echo $data["totals"]["totalAmount"]; ?></td>
			</tr>
			<?php
			foreach($data["totals"]["perPaymentMethod"] as $paymentMethod => $amount) {
				?>
				<tr>
					<td scope="col" colspan="6">Promedio por método de pago (<?php echo $paymentMethod; ?>): $<?php echo $amount; ?></td>
				</tr>
				<?php
			}
			?>
		  </tfoot>
		</table>
		</div>
		</div>
	<?php

	}
	
	?>
	</div> <!-- /container -->
  </body>
</html>