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
      if (isset($_GET['id'])) {
        $sql = $conexion->prepare("SELECT distinct
                                    prod.prod_id as id,
                                    prod.prod_descripcion as descripcion,
                                    prod.prod_orden as orden,
                                    prod.prod_costo as costo,
                                    prod.prod_precio as precio,
                                    prod.prod_cantidad as cantidad,
                                    prod.prod_estado as estado,
                                    prod.tipr_id as idtipoproducto,
                                    tipr.tipr_descripcion as descripciontipoproducto
                                    FROM pinchetas_restaurante.producto prod
                                    inner join pinchetas_restaurante.tipoproducto tipr on (tipr.tipr_id = prod.tipr_id)
                                    where prod.prod_id = ?
                                    order by prod.prod_orden; ");
                    							
        $sql->bindValue(1, $_GET['id']);                                
        $sql->execute();
        header("HTTP/1.1 200 OK");
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if ($result == false) {
          $data = (object) array();
          $data->mensaje = "No se encontraron registros.";
          header("HTTP/1.1 400 Bad Request");
          echo json_encode( $data );
          exit();
        } else {
          echo json_encode($result);
          exit();
        }
  	  } else if (isset($_GET['stock'])) {
        $sql = $conexion->prepare("SELECT distinct
                                    prod.prod_id as id,
                                    prod.prod_descripcion as descripcion,
                                    prod.prod_orden as orden,
                                    prod.prod_costo as costo,
                                    prod.prod_precio as precio,
                                    prod.prod_cantidad as cantidad,
                                    prod.prod_estado as estado,
                                    prod.tipr_id as idtipoproducto,
                                    tipr.tipr_descripcion as descripciontipoproducto
                                    FROM pinchetas_restaurante.producto prod
                                    inner join pinchetas_restaurante.tipoproducto tipr on (tipr.tipr_id = prod.tipr_id)
                                    where prod.prod_estado = 'ACTIVO'
                                    order by prod.prod_cantidad asc;  ");
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode( $sql->fetchAll() );
        exit();
  	  } else {
        $sql = $conexion->prepare("SELECT distinct
                                    prod.prod_id as id,
                                    prod.prod_descripcion as descripcion,
                                    prod.prod_orden as orden,
                                    prod.prod_costo as costo,
                                    prod.prod_precio as precio,
                                    prod.prod_cantidad as cantidad,
                                    prod.prod_estado as estado,
                                    prod.tipr_id as idtipoproducto,
                                    tipr.tipr_descripcion as descripciontipoproducto
                                    FROM pinchetas_restaurante.producto prod
                                    inner join pinchetas_restaurante.tipoproducto tipr on (tipr.tipr_id = prod.tipr_id)
                                    order by prod.prod_orden;  ");
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode( $sql->fetchAll() );
        exit();
  	  }
  }
  // Crear un nuevo post
  else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $input = $_POST;
          
      $descripcion = $frm['descripcion'];
      $orden = $frm['orden'];
      $costo = $frm['costo'];
      $precio = $frm['precio'];
      $cantidad = $frm['cantidad'];
      $estado = $frm['estado'];
      $idtipoproducto = $frm['idtipoproducto'];
      $registradopor = openCypher('decrypt', $frm['token']);
      $date = date("Y-m-d H:i:s");
      
      $conexion->beginTransaction();
      $sql = "INSERT INTO 
              pinchetas_restaurante.producto (prod_descripcion, prod_orden, prod_costo, prod_precio, prod_cantidad, prod_estado,  tipr_id, prod_registradopor, prod_fechacambio)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?); ";
            
      $sql = $conexion->prepare($sql);
      $sql->bindValue(1, $descripcion);
      $sql->bindValue(2, $orden);
      $sql->bindValue(3, $costo);
      $sql->bindValue(4, $precio);
      $sql->bindValue(5, $cantidad);
      $sql->bindValue(6, $estado);
      $sql->bindValue(7, $idtipoproducto);
      $sql->bindValue(8, $registradopor);
      $sql->bindValue(9, $date);
      $sql->execute();
      $postId = $conexion->lastInsertId();
      if ($postId > 0) {
          $sql2 = "INSERT INTO 
              pinchetas_restaurante.dependenciaproducto (prod_idpadre, prod_idhijo, depr_registradopor, depr_fechacambio)
              VALUES (?, ?, ?, ?); ";
            
              $sql2 = $conexion->prepare($sql2);
              $sql2->bindValue(1, $postId);
              $sql2->bindValue(2, $postId);
              $sql2->bindValue(3, $registradopor);
              $sql2->bindValue(4, $date);
              $sql2->execute();
              $postId2 = $conexion->lastInsertId();
              if ($postId2 > 0) {
                  $conexion->commit();
              } else {
                  $conexion->rollback();
              }
          
      } else {
          $conexion->rollback();
      }
    $input['id'] = $postId;
    $input['mensaje'] = "Registrado con éxito";
    header("HTTP/1.1 200 OK");
    echo json_encode($input);
    exit();
  	  
  }
  //Actualizar
  else if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
      $input = $_GET;
      
      $id = $frm['id'];
      $descripcion = $frm['descripcion'];
      $orden = $frm['orden'];
      $costo = $frm['costo'];
      $precio = $frm['precio'];
      $cantidad = $frm['cantidad'];
      $estado = $frm['estado'];
      $idtipoproducto = $frm['idtipoproducto'];
      $registradopor = openCypher('decrypt', $frm['token']);
      $date = date("Y-m-d H:i:s");
      
      $sql = "UPDATE pinchetas_restaurante.producto 
              SET prod_descripcion = ?, prod_orden = ?, prod_costo = ?, prod_precio = ?, prod_cantidad = ?, prod_estado = ?, tipr_id = ?, prod_registradopor = ?, prod_fechacambio = ?
              WHERE prod_id = ?; ";
            
      $sql = $conexion->prepare($sql);
      $sql->bindValue(1, $descripcion);
      $sql->bindValue(2, $orden);
      $sql->bindValue(3, $costo);
      $sql->bindValue(4, $precio);
      $sql->bindValue(5, $cantidad);
      $sql->bindValue(6, $estado);
      $sql->bindValue(7, $idtipoproducto);
      $sql->bindValue(8, $registradopor);
      $sql->bindValue(9, $date);
      $sql->bindValue(10, $id);
      $result = $sql->execute();
      
      if($result) {
        $input['id'] = $result;
        $input['mensaje'] = "Actualizado con éxito";
        header("HTTP/1.1 200 OK");
        echo json_encode($input);
        exit();
  	  } else {
        $input['id'] = $result;
        $input['mensaje'] = "Error actualizando";
        header("HTTP/1.1 400 Bad Request");
        echo json_encode($input);
        exit();
  	  }
  	  
  }
  // Eliminar
  else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
      $input = $_GET;
      $id = $input['id'];
      $registradopor = openCypher('decrypt', $input['token']);

      $date = date("Y-m-d H:i:s");
      
      $sql = "CALL procedimiento_eliminar_producto(?, ?); ";
            
      $sql = $conexion->prepare($sql);
      $sql->bindValue(1, $id);
      $sql->bindValue(2, $registradopor);
      $result = $sql->execute();
      if($result) {
        $output['id'] = $postId;
        $output['mensaje'] = "Eliminado con éxito";
        header("HTTP/1.1 200 OK");
        echo json_encode($output);
        exit();
  	  } else {
        $output['id'] = $postId;
        $output['mensaje'] = "Error eliminando";
        header("HTTP/1.1 400 Bad Request");
        echo json_encode($output);
        exit();
  	  }
  }

} catch (Exception $e) {
    echo 'Excepción capturada: ', $e->getMessage(), "\n";
}

//En caso de que ninguna de las opciones anteriores se haya ejecutado
// header("HTTP/1.1 400 Bad Request");

?>