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
      if (isset($_GET['fecha']) && isset($_GET['ventas'])) {
        $sql = $conexion->prepare("SELECT 
          DISTINCT 
          prod.prod_descripcion,
          SUM(depe.prod_cantidad) as cantidad,
          SUM(depe.prod_precio * depe.prod_cantidad) as total, 
          pedi.*,
          depe.prod_cantidad,
          depe.prod_precio
          FROM pinchetas_restaurante.producto prod
          left join pinchetas_restaurante.detallepedido depe on (depe.prod_id = prod.prod_id)
          INNER join pinchetas_restaurante.pedido pedi on (pedi.pedi_id = depe.pedi_id)
          inner join pinchetas_restaurante.estadopedido espe on (espe.espe_id = pedi.espe_id)
          WHERE espe.espe_descripcion = 'PAGO'
          AND pedi.pedi_fecha BETWEEN DATE_ADD(?, INTERVAL 0 SECOND) AND DATE_ADD(?, INTERVAL 86399 SECOND)
          GROUP BY prod.prod_id
          ORDER BY prod.prod_descripcion;");

        $sql->bindValue(1, $_GET['fecha']);
		    $sql->bindValue(2, $_GET['fecha']);
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode( $sql->fetchAll() );
        exit(); 
     } else if (isset($_GET['fecha']) && isset($_GET['gastos'])) {
	      $sql = $conexion->prepare(" SELECT distinct
          gast.gast_id as id,
          gast.gast_descripcion as descripcion,
          gast.gast_valor as valor,
          gast.gast_fecha as fecha,
          CONCAT(pena.pena_primernombre,' ', pena.pena_primerapellido) as nombrepersona
          FROM pinchetas_restaurante.gasto gast
          inner join pinchetas_general.personageneral pege on (pege.pege_id = gast.pege_idregistrador)
          inner join pinchetas_general.personanatural pena on (pena.pege_id = pege.pege_id)
          where gast.gast_fecha = ?
          order by gast.gast_fecha; ;");
        $sql->bindValue(1, $_GET['fecha']);
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode( $sql->fetchAll() );
        exit(); 
     }
  }

} catch (Exception $e) {
    echo 'Excepción capturada: ', $e->getMessage(), "\n";
}

//En caso de que ninguna de las opciones anteriores se haya ejecutado
// header("HTTP/1.1 400 Bad Request");

?>
