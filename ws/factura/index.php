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
                                    pedi.pedi_id as id,
                                    pedi.pedi_fecha as fecha,
                                    pedi.pedi_fechacambio as fechacambio,
                                    pedi.pege_idmesero as idmesero,
                                    pedi.mesa_descripcion as mesa,
                                    pedi.mesa_id as idmesa,
                                    pedi.espe_id as idestado,
                                    espe.espe_descripcion as descripcionestado,
                                    espe.espe_editablepedido as editablepedido
                                    FROM pinchetas_restaurante.pedido pedi
                                    inner join pinchetas_restaurante.estadopedido espe using (espe_id)
                                    WHERE pedi.pedi_id = ? 
                                    AND espe.espe_descripcion = 'FACTURADO'
                                    ORDER BY pedi.pedi_fechacambio; ");
                    							
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
  	  } else {
        $date = date("Y-m-d");
        $sql = $conexion->prepare(" select 
                                    pedi.pedi_id as id,
                                    pedi.pedi_fechacambio as fechacambio,
                                    DATE(pedi.pedi_fechacambio) as fecha,
                                    TIME(pedi.pedi_fechacambio) as hora,
                                    pedi.pege_idmesero as idmesero,
                                    pedi.mesa_id as idmesa,
                                    mesa.mesa_descripcion as mesa,
                                    concat(pena.pena_primernombre, ' ', pena.pena_primerapellido) as mesero,
                                    pedi.espe_id as idestado,
                                    espe.espe_descripcion as descripcionestado,
                                    espe.espe_editablepedido as editablepedido
                                    FROM pinchetas_restaurante.pedido pedi
                                    inner join pinchetas_restaurante.estadopedido espe using (espe_id)
                                    inner join pinchetas_restaurante.mesa mesa using (mesa_id)
                                    inner join pinchetas_general.personanatural pena on (pedi.pege_idmesero = pena.pege_id)
                                    WHERE espe.espe_descripcion = 'FACTURADO'
                                    ORDER BY fechacambio DESC LIMIT 800;  ");

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