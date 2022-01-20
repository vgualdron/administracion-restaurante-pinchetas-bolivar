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
      	 $sql = "CALL depurar_pedidos(?); ";
            
          $sql = $conexion->prepare($sql);
          $sql->bindValue(1, $_GET['fecha']);
	  $result = $sql->execute();
	  if ($result) {
            echo "{'salida': 'success'}";
          } else {
	    echo "{'salida': 'error'}";
	  }
      }
        
  }

  

} catch (Exception $e) {
    echo 'Excepción capturada: ', $e->getMessage(), "\n";
}

//En caso de que ninguna de las opciones anteriores se haya ejecutado
// header("HTTP/1.1 400 Bad Request");

?>
