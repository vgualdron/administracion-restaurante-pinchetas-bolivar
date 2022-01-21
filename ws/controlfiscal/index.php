<?php
session_start();
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
require_once("../conexion.php");
require_once("../encrypted.php");
$conexion = new Conexion();

$frm = json_decode(file_get_contents('php://input'), true);

$idusuario = openCypher('decrypt', $frm['token']);
$fechaReporte = $frm['fecha'];
$fechaImpresion = date("Y-m-d H:i:s");

$data = (object) array();
$data->fechaImpresion = $fechaImpresion;
$data->fechaReporte = $fechaReporte;

$conexion ->query("SET NAMES 'utf8';");


$use = $conexion->prepare(" select * from pinchetas_general.parametroano paan
			    where paan.paan_descripcion = ? ; "); 						
$use->bindValue(1, 'NOMBRE_EMPRESA');
$use ->execute();
$row = $use->fetch();

$data->nombreEmpresa = $row['paan_valor'];

$use = $conexion->prepare(" select * from pinchetas_general.parametroano paan
			    where paan.paan_descripcion = ? ; "); 						
$use->bindValue(1, 'NIT_EMPRESA');
$use ->execute();
$row = $use->fetch();

$data->nitEmpresa = $row['paan_valor'];

$use = $conexion->prepare(" select * from pinchetas_general.parametroano paan
			    where paan.paan_descripcion = ? ; "); 						
$use->bindValue(1, 'DIRECCION_EMPRESA');
$use ->execute();
$row = $use->fetch();

$data->direccionEmpresa = $row['paan_valor'];

$use = $conexion->prepare(" select * from pinchetas_general.parametroano paan
			    where paan.paan_descripcion = ? ; "); 						
$use->bindValue(1, 'RANGO_FACTURACION');
$use ->execute();
$row = $use->fetch();

$data->rangoAutorizadoFacturas = $row['paan_valor'];

$use = $conexion->prepare(" select * from pinchetas_general.parametroano paan
			    where paan.paan_descripcion = ? ; "); 						
$use->bindValue(1, 'PREFIJO_CAJA');
$use ->execute();
$row = $use->fetch();

$prefijoCaja = $row['paan_valor'];
$data->prefijoFactura = $prefijoCaja;

$use = $conexion->prepare(" select * from pinchetas_restaurante.pedido pedi
				inner join pinchetas_restaurante.estadopedido espe on (espe.espe_id = pedi.espe_id)
				where pedi.pedi_fecha BETWEEN DATE_ADD(?, INTERVAL 0 SECOND) AND DATE_ADD(?, INTERVAL 86399 SECOND)
				and pedi.pedi_numerofactura > 0
				and espe.espe_descripcion = ?
				order by pedi.pedi_numerofactura asc; "); 						
$use->bindValue(1, $fechaReporte);
$use->bindValue(2, $fechaReporte);
$use->bindValue(3, 'PAGO');
$use ->execute();
$cantidadFacturasUsadas = $use->rowCount();
$row = $use->fetchAll();

$data->cantidadFacturasUsadas = $cantidadFacturasUsadas;

$use = $conexion->prepare(" select * from pinchetas_restaurante.pedido pedi
				inner join pinchetas_restaurante.estadopedido espe on (espe.espe_id = pedi.espe_id)
				where pedi.pedi_fecha BETWEEN DATE_ADD(?, INTERVAL 0 SECOND) AND DATE_ADD(?, INTERVAL 86399 SECOND)
				and pedi.pedi_numerofactura > 0
				and espe.espe_descripcion = ?
				order by pedi.pedi_numerofactura asc limit 1; "); 						
$use->bindValue(1, $fechaReporte);
$use->bindValue(2, $fechaReporte);
$use->bindValue(3, 'PAGO');
$use ->execute();
$row = $use->fetch();
$numeroPrimeraFacturaUsada = $row['pedi_numerofactura'];
$data->numeroPrimeraFacturaUsada = $numeroPrimeraFacturaUsada;

$use = $conexion->prepare(" select * from pinchetas_restaurante.pedido pedi
				inner join pinchetas_restaurante.estadopedido espe on (espe.espe_id = pedi.espe_id)
				where pedi.pedi_fecha BETWEEN DATE_ADD(?, INTERVAL 0 SECOND) AND DATE_ADD(?, INTERVAL 86399 SECOND)
				and pedi.pedi_numerofactura > 0
				and espe.espe_descripcion = ?
				order by pedi.pedi_numerofactura desc limit 1; "); 						
$use->bindValue(1, $fechaReporte);
$use->bindValue(2, $fechaReporte);
$use->bindValue(3, 'PAGO');
$use ->execute();
$row = $use->fetch();
$numeroUltimaFacturaUsada = $row['pedi_numerofactura'];
$data->numeroUltimaFacturaUsada = $numeroUltimaFacturaUsada;

$data->rangoFacturasUsadas = 'DE ' . $prefijoCaja . ' ' . $numeroPrimeraFacturaUsada . ' A ' . $prefijoCaja . ' ' . $numeroUltimaFacturaUsada;

$use = $conexion->prepare(" select 
				pedi.pedi_fecha as fecha,
				sum(depe.prod_precio * depe.prod_cantidad) total 
				from pinchetas_restaurante.detallepedido depe
				inner join pinchetas_restaurante.pedido pedi on (pedi.pedi_id = depe.pedi_id)
				inner join pinchetas_restaurante.estadopedido espe on (espe.espe_id = pedi.espe_id)
				where pedi.pedi_fecha BETWEEN DATE_ADD(?, INTERVAL 0 SECOND) AND DATE_ADD(?, INTERVAL 86399 SECOND)
				and pedi.pedi_numerofactura > 0
				and espe.espe_descripcion = ?
				order by pedi.pedi_numerofactura asc; "); 	
					
$use->bindValue(1, $fechaReporte);
$use->bindValue(2, $fechaReporte);
$use->bindValue(3, 'PAGO');
$use ->execute();
$row = $use->fetch();
$totalVentas = number_format($row['total'],0,",",".");
$data->ventasNetas = $totalVentas;
$data->totalRegistrado = $totalVentas;
$data->totalFormaDePago = $totalVentas;

$use = $conexion->prepare(" select 
* 
from pinchetas_restaurante.tipoproducto tipr
where tipr_estado = ?
order by tipr_orden asc;"); 
							
$use->bindValue(1, 'ACTIVO');
$use ->execute();
$count = $use->rowCount();
$row = $use->fetchAll();

$arrayVentasPorDepartamento = array();

$totalValorBaseDepartamentos = 0;
$totalValorIcoDepartamentos = 0;

if ($count > 0) {
	foreach($row as $registro){
		$objetoDepartamento = (object) array();
		$grav = 0.08;
		$objetoDepartamento->nombre = $registro['tipr_descripcion'];
                $objetoDepartamento->grav = $grav;

		$use2 = $conexion->prepare(" select 
						pedi.pedi_fecha as fecha,
						sum(depe.prod_precio * depe.prod_cantidad) total 
						from pinchetas_restaurante.detallepedido depe
						inner join pinchetas_restaurante.pedido pedi on (pedi.pedi_id = depe.pedi_id)
						inner join pinchetas_restaurante.estadopedido espe on (espe.espe_id = pedi.espe_id)
						inner join pinchetas_restaurante.producto prod on (prod.prod_id = depe.prod_id)
						inner join pinchetas_restaurante.tipoproducto tipr on (tipr.tipr_id = prod.tipr_id)
						where pedi.pedi_fecha BETWEEN DATE_ADD(?, INTERVAL 0 SECOND) AND DATE_ADD(?, INTERVAL 86399 SECOND)
						and pedi.pedi_numerofactura > 0
						and espe.espe_descripcion = ?
						and tipr.tipr_id = ?
						order by pedi.pedi_numerofactura asc; "); 
						
		$use2->bindValue(1, $fechaReporte);
		$use2->bindValue(2, $fechaReporte);
		$use2->bindValue(3, 'PAGO');
		$use2->bindValue(4, $registro['tipr_id']);
	
		$use2 ->execute();
		$count2 = $use2->rowCount();
		$row2 = $use2->fetchAll();
		
		if ($count2 > 0) {
			foreach($row2 as $registro2){
				$valorNeto = $registro2['total'];
				$valorBase = $valorNeto / (1 + $grav);
				$valorIco = $valorBase * $grav;
				$totalValorBaseDepartamentos = $totalValorBaseDepartamentos + $valorBase;
				$totalValorIcoDepartamentos = $totalValorIcoDepartamentos + $valorIco;
				$objetoDepartamento->valorNeto = number_format($valorNeto,2,",",".");
				$objetoDepartamento->valorBase = number_format($valorBase,2,",",".");
				$objetoDepartamento->valorIco = number_format($valorIco,2,",",".");
			}
		}
		
		array_push($arrayVentasPorDepartamento, $objetoDepartamento);
		

			
	}
}

$data->departamentos = $arrayVentasPorDepartamento;


$use = $conexion->prepare(" select
				count(pedi.pedi_id) as cantidad,
				pedi.pedi_fecha as fecha
				from pinchetas_restaurante.pedido pedi
				inner join pinchetas_restaurante.estadopedido espe on (espe.espe_id = pedi.espe_id)
				where pedi.pedi_fecha BETWEEN DATE_ADD(?, INTERVAL 0 SECOND) AND DATE_ADD(?, INTERVAL 86399 SECOND)
				and pedi.pedi_numerofactura > 0
				and espe.espe_descripcion = ?
				and pedi.pedi_tipopago = ?
				order by pedi.pedi_numerofactura asc; "); 	
					
$use->bindValue(1, $fechaReporte);
$use->bindValue(2, $fechaReporte);
$use->bindValue(3, 'PAGO');
$use->bindValue(4, 'TARJETA');
$use ->execute();
$row = $use->fetch();

$cantidadVentasTarjeta = $row['cantidad'];

$use = $conexion->prepare(" select
				count(pedi.pedi_id) as cantidad,
				pedi.pedi_fecha as fecha,
				sum(depe.prod_precio * depe.prod_cantidad) total 
				from pinchetas_restaurante.detallepedido depe
				inner join pinchetas_restaurante.pedido pedi on (pedi.pedi_id = depe.pedi_id)
				inner join pinchetas_restaurante.estadopedido espe on (espe.espe_id = pedi.espe_id)
				where pedi.pedi_fecha BETWEEN DATE_ADD(?, INTERVAL 0 SECOND) AND DATE_ADD(?, INTERVAL 86399 SECOND)
				and pedi.pedi_numerofactura > 0
				and espe.espe_descripcion = ?
				and pedi.pedi_tipopago = ?
				order by pedi.pedi_numerofactura asc; "); 	
					
$use->bindValue(1, $fechaReporte);
$use->bindValue(2, $fechaReporte);
$use->bindValue(3, 'PAGO');
$use->bindValue(4, 'TARJETA');
$use ->execute();
$row = $use->fetch();

$totalVentasTarjeta = number_format($row['total'],0,",",".");
$porcentajeVentasTarjeta = (($cantidadVentasTarjeta / $cantidadFacturasUsadas) * 100); 

$data->totalVentasTarjeta = $totalVentasTarjeta;
$data->cantidadVentasTarjeta = $cantidadVentasTarjeta;
$data->porcentajeVentasTarjeta = number_format($porcentajeVentasTarjeta,2,",",".")."%";

$use = $conexion->prepare("  select 
				count(pedi.pedi_id) as cantidad,
				pedi.pedi_fecha as fecha
				from pinchetas_restaurante.pedido pedi
				inner join pinchetas_restaurante.estadopedido espe on (espe.espe_id = pedi.espe_id)
				where pedi.pedi_fecha BETWEEN DATE_ADD(?, INTERVAL 0 SECOND) AND DATE_ADD(?, INTERVAL 86399 SECOND)
				and pedi.pedi_numerofactura > 0
				and espe.espe_descripcion = ?
				and pedi.pedi_tipopago = ?
				order by pedi.pedi_numerofactura asc; "); 	
					
$use->bindValue(1, $fechaReporte);
$use->bindValue(2, $fechaReporte);
$use->bindValue(3, 'PAGO');
$use->bindValue(4, 'EFECTIVO');
$use ->execute();
$row = $use->fetch();

$cantidadVentasEfectivo = $row['cantidad'];

$use = $conexion->prepare(" select 
				count(pedi.pedi_id) as cantidad,
				pedi.pedi_fecha as fecha,
				sum(depe.prod_precio * depe.prod_cantidad) total 
				from pinchetas_restaurante.detallepedido depe
				inner join pinchetas_restaurante.pedido pedi on (pedi.pedi_id = depe.pedi_id)
				inner join pinchetas_restaurante.estadopedido espe on (espe.espe_id = pedi.espe_id)
				where pedi.pedi_fecha BETWEEN DATE_ADD(?, INTERVAL 0 SECOND) AND DATE_ADD(?, INTERVAL 86399 SECOND)
				and pedi.pedi_numerofactura > 0
				and espe.espe_descripcion = ?
				and pedi.pedi_tipopago = ?
				order by pedi.pedi_numerofactura asc; "); 	
					
$use->bindValue(1, $fechaReporte);
$use->bindValue(2, $fechaReporte);
$use->bindValue(3, 'PAGO');
$use->bindValue(4, 'EFECTIVO');
$use ->execute();
$row = $use->fetch();

$totalVentasEfectivo = number_format($row['total'],0,",",".");
$porcentajeVentasEfectivo = (($cantidadVentasEfectivo / $cantidadFacturasUsadas) * 100); 

$data->totalVentasEfectivo = $totalVentasEfectivo;
$data->cantidadVentasEfectivo = $cantidadVentasEfectivo;
$data->porcentajeVentasEfectivo = number_format($porcentajeVentasEfectivo,2,",",".")."%";

$data->totalValorBaseDepartamentos = number_format($totalValorBaseDepartamentos,2,",",".");
$data->totalValorIcoDepartamentos = number_format($totalValorIcoDepartamentos,2,",",".");

print_r(json_encode($data));
?>
