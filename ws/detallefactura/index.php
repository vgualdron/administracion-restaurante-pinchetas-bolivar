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
      if (isset($_GET['id'])) {
        $sql = $conexion->prepare(" select 
                                    depe.depe_id as id,
                                    depe.depe_descripcion as descripcion,
                                    depe.pedi_id as idpedido,
                                    tipr.tipr_id as idtipoproducto,
                                    tipr.tipr_descripcion as descripciontipoproducto,
                                    depe.prod_id as idproducto,
                                    prod.prod_descripcion as descripcionproducto,
                                    depe.prod_costo as costoproducto,
                                    depe.prod_cantidad as cantidadproducto,
                                    depe.prod_precio as precioproducto
                                    FROM pinchetas_restaurante.detallepedido depe
                                    inner join pinchetas_restaurante.pedido pedi on (pedi.pedi_id = depe.pedi_id)
                                    inner join pinchetas_restaurante.producto prod on (prod.prod_id = depe.prod_id)
                                    inner join pinchetas_restaurante.tipoproducto tipr on (tipr.tipr_id = prod.tipr_id)
                                    WHERE depe.depe_id = ? 
                                    ORDER BY depe_fechacambio;");
                    							
        $sql->bindValue(1, $_GET['id']);
        $sql->execute();
        header("HTTP/1.1 200 OK");
        $result = $sql->fetch(PDO::FETCH_ASSOC);
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
  	  } else if (isset($_GET['idPedido'])) {
        $date = date("Y-m-d");
        $sql = $conexion->prepare(" select 
                                    depe.depe_id as id,
                                    depe.depe_descripcion as descripcion,
                                    depe.pedi_id as idpedido,
                                    tipr.tipr_id as idtipoproducto,
                                    tipr.tipr_descripcion as descripciontipoproducto,
                                    depe.prod_id as idproducto,
                                    prod.prod_descripcion as descripcionproducto,
                                    depe.prod_costo as costoproducto,
                                    depe.prod_cantidad as cantidadproducto,
                                    depe.prod_precio as precioproducto,
                                    (depe.prod_cantidad * depe.prod_precio) as subtotal
                                    FROM pinchetas_restaurante.detallepedido depe
                                    inner join pinchetas_restaurante.pedido pedi on (pedi.pedi_id = depe.pedi_id)
                                    inner join pinchetas_restaurante.producto prod on (prod.prod_id = depe.prod_id)
                                    inner join pinchetas_restaurante.tipoproducto tipr on (tipr.tipr_id = prod.tipr_id)
                                    WHERE depe.pedi_id = ?
                                    ORDER BY depe_fechacambio;");
        $sql->bindValue(1, $_GET['idPedido']);
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