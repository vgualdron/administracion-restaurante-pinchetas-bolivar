<?php


error_reporting(0);
session_start();
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

require_once("../conexion.php");
require_once("../encrypted.php");
$conexion = new Conexion();

$frm = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      if (isset($_GET['id'])) {
        $sql = $conexion->prepare(" SELECT distinct
                                    repo.repo_id as id,
				    repo.repo_nombre as nombre,
                                    repo.repo_descripcion as descripcion,
                                    repo.repo_sql as sqal,
                                    repo.repo_tipo as tipo,
				    repo.anle_id as anleid
                                    FROM pinchetas_general.reporte repo
                                    where repo.repo_id = ?
                                    order by repo.repo_nombre; ");
                    							
        $sql->bindValue(1, $_GET['id']);                                
        $sql->execute();
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if ($result == false) {
          $data = (object) array();
          $data->mensaje = "No se encontraron registros.";
        } else {
          $data = json_encode($result);
        }
     }
}

$query = $result['sqal'];
$name = $result['nombre'].'.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.$name);

$stmt = $conexion->prepare($query);
$stmt->execute();

$colcount = $stmt->columnCount();
$headers = array();
for ($i = 0; $i < $colcount; $i++) {
     $columArray = $stmt->getColumnMeta($i);
     array_push($headers, $columArray['name']);
}
$data = fopen('php://output', 'w');
fputcsv($data, $headers);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($data, $row);
}
fclose($data);

?>
