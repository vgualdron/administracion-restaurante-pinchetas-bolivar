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
                                    depr.depr_id as id,
                                    depr.prod_idpadre as idproductopadre,
                                    depr.prod_idhijo as idproductohijo
                                    FROM pinchetas_restaurante.dependenciaproducto depr
                                    where depr.depr_id = ?
                                    order by depr.depr_id; ");
                    							
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
  	  } else if (isset($_GET['idpadre'])) {
        $sql = $conexion->prepare("SELECT distinct
                                    depr.prod_idhijo as idhijo
                                    FROM pinchetas_restaurante.dependenciaproducto depr
                                    where depr.prod_idpadre = ?
                                    order by depr.depr_id;  ");
        $sql->bindValue(1, $_GET['idpadre']);
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode( $sql->fetchAll() );
        exit();
  	  } else {
        $sql = $conexion->prepare("SELECT distinct
                                    depr.depr_id as id,
                                    depr.prod_idpadre as idproductopadre,
                                    depr.prod_idhijo as idproductohijo
                                    FROM pinchetas_restaurante.dependenciaproducto depr
                                    order by depr.depr_id;  ");
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode( $sql->fetchAll() );
        exit();
  	  }
  }
  // Crear un nuevo post
  else if ($_SERVER['REQUEST_METHOD'] == 'POST') {  
      
      $idpadre = $frm['id'];
      $hijos = $frm['hijos'];
      $registradopor = openCypher('decrypt', $frm['token']);
      $date = date("Y-m-d H:i:s");
      
      $conexion->beginTransaction();
      
      $sql = "DELETE FROM pinchetas_restaurante.dependenciaproducto
              WHERE prod_idpadre = ?;";
            
      $sql = $conexion->prepare($sql);
      $sql->bindValue(1, $idpadre);
      $result = $sql->execute();
      if($result) {
          $mensaje = "eliminadas todas las dependencias del producto";
          $bandera = true;
          foreach ($hijos as $clave => $hijo) {
              if ($bandera) {
                  $sql2 = "INSERT INTO 
                      pinchetas_restaurante.dependenciaproducto (prod_idpadre, prod_idhijo, depr_registradopor, depr_fechacambio)
                      VALUES (?, ?, ?, ?); ";

                  $sql2 = $conexion->prepare($sql2);
                  $sql2->bindValue(1, $idpadre);
                  $sql2->bindValue(2, $hijo["idhijo"]);
                  $sql2->bindValue(3, $registradopor);
                  $sql2->bindValue(4, $date);
                  $result2 = $sql2->execute();
                  $postId = $conexion->lastInsertId();
                  if ($postId > 0) {
                      $mensaje = "dependencia insetada con éxito";
                  } else {
                      $bandera = false;
                      $mensaje = "error al insertar dependencia hija -> ". $hijo["idhijo"];
                      $conexion->rollback();
                  }
              }
          }
          $output['mensaje'] = $mensaje;
          if ($bandera) {
              $conexion->commit();
              header("HTTP/1.1 200 OK");
          } else {
              header("HTTP/1.1 400 Bad Request");
          }
          echo json_encode($output);
          exit();
          
  	  } else {
        $conexion->rollback();
        $input['id'] = $postId;
        $input['mensaje'] = "Error eliminando";
        header("HTTP/1.1 400 Bad Request");
        echo json_encode($input);
        exit();
  	  }
  	  
  }
  //Actualizar
  else if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $input['mensaje'] = "No se ha implementado el metodo";
    header("HTTP/1.1 200 OK");
    echo json_encode($input);
    exit();
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
        $output['id'] = $result;
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
    $output['mensaje'] = 'Excepción capturada: '. $e->getMessage(). "\n";
    header("HTTP/1.1 400 Bad Request");
    echo json_encode($output);
}

//En caso de que ninguna de las opciones anteriores se haya ejecutado
// header("HTTP/1.1 400 Bad Request");

?>