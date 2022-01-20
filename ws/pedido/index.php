<?php
session_start();
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

require_once("../conexion.php");
require_once("../encrypted.php");
$conexion = new Conexion();

$frm = json_decode(file_get_contents('php://input'), true);

try {
  
  //  listar todos los posts o solo uno
  if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      $registradopor = openCypher('decrypt', $_GET['token']);
      if (isset($_GET['fecha'])) {
         $sql = $conexion->prepare(" select 
					pedi.pedi_id as id,
					pedi.pedi_fecha as fecha,
					pedi.pege_idmesero as idmesero,
					pedi.mesa_id as idmesa,
					mesa.mesa_descripcion as descripcionmesa,
					pedi.espe_id as idestado,
					espe.espe_descripcion as descripcionestado,
					espe.espe_editablepedido as editablepedido,
					pedi.pedi_nombrecliente as nombrecliente,
					pedi.pedi_direccioncliente as direccioncliente,
					pedi.pedi_telefonocliente as telefonocliente,
					pedi.pedi_tipopago as tipopago,
					pedi.pedi_bandera as bandera,
					pedi.pedi_numerofactura as numerofactura,
					pedi.pedi_prefijofactura as prefijofactura
					FROM pinchetas_restaurante.pedido pedi
					inner join pinchetas_restaurante.estadopedido espe using (espe_id)
					inner join pinchetas_restaurante.mesa mesa on (pedi.mesa_id = mesa.mesa_id)
					WHERE pedi.pedi_fecha BETWEEN DATE_ADD(?, INTERVAL 0 SECOND) AND DATE_ADD(?, INTERVAL 86399 SECOND)
					AND espe.espe_descripcion = ?
					AND pedi.pedi_numerofactura > 0
					ORDER BY pedi.pedi_id DESC;");
                    							
        $sql->bindValue(1, $_GET['fecha']);
		$sql->bindValue(2, $_GET['fecha']);
		$sql->bindValue(3, 'PAGO');
        $sql->execute();
        header("HTTP/1.1 200 OK");
        $result = $sql->fetchAll(PDO::FETCH_ASSOC);
        if ($result == false) {
          $data = (object) array();
          $data->mensaje = "No se encontró el registro.";
          header("HTTP/1.1 400 Bad Request");
          echo json_encode( $data );
          exit();
        } else {
          echo json_encode($result);
          exit();
        }
     }
  }
  

} catch (Exception $e) {
    echo 'Excepción capturada: ', $e->getMessage(), "\n";
}

//En caso de que ninguna de las opciones anteriores se haya ejecutado
// header("HTTP/1.1 400 Bad Request");

?>
