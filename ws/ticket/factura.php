<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
require __DIR__ . '/ticket/autoload.php'; 
//Nota: si renombraste la carpeta a algo diferente de "ticket" cambia el nombre en esta línea
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

date_default_timezone_set('America/Bogota');

$unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A',                                     'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
 
/*
	Este ejemplo imprime un
	ticket de venta desde una impresora térmica
*/
 
 
/*
	Una pequeña clase para
	trabajar mejor con
	los productos
	Nota: esta clase no es requerida, puedes
	imprimir usando puro texto de la forma
	que tú quieras
*/
$frm = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productos = $frm['productos'];
	$mesero = $frm['mesero'];
    $mesa = $frm['mesa'];
	$mesero = strtoupper($frm['mesero']);
	$cliente = strtoupper($frm['cliente']);
    /*
        Aquí, en lugar de "POS-58" (que es el nombre de mi impresora)
        escribe el nombre de la tuya. Recuerda que debes compartirla
        desde el panel de control
    */

    $nombre_impresora = "POS-80"; 


    $connector = new WindowsPrintConnector($nombre_impresora);
    $printer = new Printer($connector);


    /*
        Vamos a imprimir un logotipo
        opcional. Recuerda que esto
        no funcionará en todas las
        impresoras

        Pequeña nota: Es recomendable que la imagen no sea
        transparente (aunque sea png hay que quitar el canal alfa)
        y que tenga una resolución baja. En mi caso
        la imagen que uso es de 250 x 250
    */

    # Vamos a alinear al centro lo próximo que imprimamos
    $printer->setJustification(Printer::JUSTIFY_CENTER);

    /*
        Intentaremos cargar e imprimir
        el logo
    */
    try{
        $logo = EscposImage::load("logo_banner_menu_minimized.jpg", false);
        $printer->bitImage($logo);
    }catch(Exception $e){/*No hacemos nada si hay error*/}

    /*
        Ahora vamos a imprimir un encabezado
    */
    $printer->feed(1);
    $printer->setEmphasis(true);
    $printer->setTextSize(2,1);
    $printer->text("PINCHETAS" . "\n");
    $printer->setTextSize(1,1);
	$printer->text("NIT 13469599-0" . "\n");
    $printer->text($mesa. "\n");
    $printer->selectPrintMode();
    // $printer->text("Otra linea" . "\n");
    #La fecha también
    $printer->text(date("d-m-Y H:i:s") . "\n");
	$printer->setJustification(Printer::JUSTIFY_LEFT);
	$printer->text("ATENDIDO POR : " . $mesero. "\n");
	if (!empty($cliente)) {
		$printer->text("CLIENTE : " . $cliente. "\n");
	}
	$printer->selectPrintMode();
    $printer->text("------------------------------------------------\n");
    $printer->setEmphasis(true);
    $printer->text("CANT               PRODUCTO     PRECIO  SUBTOTAL\n");
    $printer->selectPrintMode();
    $printer->text("------------------------------------------------\n");
    
    /*
        Ahora vamos a imprimir los
        productos
    */

    # Para mostrar el total
    $total = 0;
    foreach ($productos as $clave => $producto) {
        $total += $producto["cantidadproducto"] * $producto["precioproducto"];

        /*Alinear a la izquierda para la cantidad y el nombre*/
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("  ".$producto["cantidadproducto"]. "   ".strtr( $producto["descripcionproducto"], $unwanted_array ). "\n");

        /*Y a la derecha para el importe*/
        $printer->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->text(' $' . number_format($producto["precioproducto"], 0, ',', '.') . '   $' . number_format(($producto["cantidadproducto"])*($producto["precioproducto"]), 0, ',', '.') ."\n");
    }

    /*
        Terminamos de imprimir
        los productos, ahora va el total
    */
    $printer->text("------------------------------------------------\n");
    $printer->setEmphasis(true);
    $printer->setJustification(Printer::JUSTIFY_RIGHT);
    $printer->text("TOTAL: $".  number_format($total, 0, ',', '.') ."\n");
    $printer->selectPrintMode();
    $printer->text("------------------------------------------------\n");


    /*
        Podemos poner también un pie de página
    */
    $printer->text("Muchas gracias por su compra.\n");



    /*Alimentamos el papel 3 veces*/
    $printer->feed(3);

    /*
        Cortamos el papel. Si nuestra impresora
        no tiene soporte para ello, no generará
        ningún error
    */
    $printer->cut();

    /*
        Por medio de la impresora mandamos un pulso.
        Esto es útil cuando la tenemos conectada
        por ejemplo a un cajón
    */
    // $printer->pulse();

    /*
        Para imprimir realmente, tenemos que "cerrar"
        la conexión con la impresora. Recuerda incluir esto al final de todos los archivos
    */
    $printer->close();
}
?>