<?php
session_start();
require './fpdf/fpdf.php';
include '../library/configServer.php';
include '../library/consulSQL.php';

$id = $_GET['id'];
$sVenta = ejecutarSQL::consultar("SELECT * FROM venta WHERE NumPedido='$id'");
$dVenta = mysqli_fetch_array($sVenta, MYSQLI_ASSOC);
$sCliente = ejecutarSQL::consultar("SELECT * FROM cliente WHERE NIT='".$dVenta['NIT']."'");
$dCliente = mysqli_fetch_array($sCliente, MYSQLI_ASSOC);

class PDF extends FPDF {
    // Encabezado personalizado
    function Header() {
        $this->Image('.../assets/img/xtreme.png', 10, 10, 30); // Cambia la ruta por la ubicación de tu logotipo
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, utf8_decode('Factura de Pedido'), 0, 1, 'C');
        $this->Ln(10);
    }

    // Pie de página personalizado
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, utf8_decode('Gracias por su compra en XTREME AI - Huanta, Ayacucho, Perú'), 0, 0, 'C');
    }
}

ob_end_clean();
$pdf = new PDF('P', 'mm', 'Letter');
$pdf->AddPage();
$pdf->SetFont("Times", "", 20);
$pdf->SetMargins(25, 20, 25);
$pdf->SetFillColor(240, 240, 240); // Color más suave para el fondo

$pdf->Ln(5);
$pdf->SetFont("Times", "", 14);
$pdf->Cell(0, 5, utf8_decode('Factura de pedido número '.$id), 0, 1, 'C');
$pdf->Ln(20);

$pdf->SetFont("Times", "B", 12);
$pdf->Cell(33, 5, utf8_decode('Fecha del pedido: '), 0);
$pdf->SetFont("Times", "", 12);
$pdf->Cell(37, 5, utf8_decode($dVenta['Fecha']), 0);
$pdf->Ln(12);

$pdf->SetFont("Times", "B", 12);
$pdf->Cell(37, 5, utf8_decode('Nombre del cliente: '), 0);
$pdf->SetFont("Times", "", 12);
$pdf->Cell(100, 5, utf8_decode($dCliente['NombreCompleto']." ".$dCliente['Apellido']), 0);
$pdf->Ln(12);

$pdf->SetFont("Times", "B", 12);
$pdf->Cell(30, 5, utf8_decode('DNI/CÉDULA: '), 0);
$pdf->SetFont("Times", "", 12);
$pdf->Cell(25, 5, utf8_decode($dCliente['NIT']), 0);
$pdf->Ln(12);

$pdf->SetFont("Times", "B", 12);
$pdf->Cell(20, 5, utf8_decode('Dirección: '), 0);
$pdf->SetFont("Times", "", 12);
$pdf->Cell(70, 5, utf8_decode($dCliente['Direccion']), 0);
$pdf->Ln(12);

$pdf->SetFont("Times", "B", 12);
$pdf->Cell(19, 5, utf8_decode('Teléfono: '), 0);
$pdf->SetFont("Times", "", 12);
$pdf->Cell(70, 5, utf8_decode($dCliente['Telefono']), 0);
$pdf->SetFont("Times", "B", 12);
$pdf->Cell(14, 5, utf8_decode('Email: '), 0);
$pdf->SetFont("Times", "", 12);
$pdf->Cell(40, 5, utf8_decode($dCliente['Email']), 0);
$pdf->Ln(10);

// Diseño de la tabla de productos
$pdf->SetFont("Times", "B", 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(76, 10, utf8_decode('Nombre'), 1, 0, 'C', true);
$pdf->Cell(30, 10, utf8_decode('Precio'), 1, 0, 'C', true);
$pdf->Cell(30, 10, utf8_decode('Cantidad'), 1, 0, 'C', true);
$pdf->Cell(30, 10, utf8_decode('Subtotal'), 1, 0, 'C', true);
$pdf->Ln(10);

$pdf->SetFont("Times", "", 12);
$suma = 0;
$sDet = ejecutarSQL::consultar("SELECT * FROM detalle WHERE NumPedido='".$id."'");
while ($fila1 = mysqli_fetch_array($sDet, MYSQLI_ASSOC)) {
    $consulta = ejecutarSQL::consultar("SELECT * FROM producto WHERE CodigoProd='".$fila1['CodigoProd']."'");
    $fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC);
    $pdf->Cell(76, 10, utf8_decode($fila['NombreProd']), 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode('S/.'.$fila1['PrecioProd']), 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode($fila1['CantidadProductos']), 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode('S/.'.$fila1['PrecioProd'] * $fila1['CantidadProductos']), 1, 0, 'C');
    $pdf->Ln(10);
    $suma += $fila1['PrecioProd'] * $fila1['CantidadProductos'];
    mysqli_free_result($consulta);
}

$pdf->SetFont("Times", "B", 12);
$pdf->Cell(76, 10, utf8_decode('Total'), 1, 0, 'C', true);
$pdf->Cell(30, 10, '', 1);
$pdf->Cell(30, 10, '', 1);
$pdf->Cell(30, 10, 'S/.'.number_format($suma, 2), 1, 0, 'C');
$pdf->Ln(10);

$pdf->Output('Factura-#'.$id, 'I');
mysqli_free_result($sVenta);
mysqli_free_result($sCliente);
mysqli_free_result($sDet);
?>