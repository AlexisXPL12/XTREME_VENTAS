<?php
session_start();
require './fpdf/fpdf.php';
include '../library/configServer.php';
include '../library/consulSQL.php';
$id = $_GET['id'];
$sVenta = ejecutarSQL::consultar("SELECT * FROM venta WHERE NumPedido='$id'");
$dVenta = mysqli_fetch_array($sVenta, MYSQLI_ASSOC);
$sCliente = ejecutarSQL::consultar("SELECT * FROM cliente WHERE NIT='" . $dVenta['NIT'] . "'");
$dCliente = mysqli_fetch_array($sCliente, MYSQLI_ASSOC);

class PDF extends FPDF {
    // Encabezado personalizado
    function Header() {
        // Logo de la empresa
        $this->Image('../assets/img/xtreme.png', 10, 10, 30);
        
        // Información de la empresa (Parte derecha)
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 5, 'XTREME AI', 0, 1, 'R');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'R.U.C. 20611791471', 0, 1, 'R'); // RUC ficticio
        $this->Cell(0, 5, 'BOLETA DE VENTA ELECTRONICA', 0, 1, 'R');
        $this->Cell(0, 5, 'N B002-00001234', 0, 1, 'R'); // Número ficticio
        $this->Ln(10);

        // Información adicional de la empresa
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, utf8_decode('Av.San Martin 427, Huanta, Ayacuho, Perú'), 0, 1, 'L');
        $this->Cell(0, 5, 'Tel: 987 654 321', 0, 1, 'L');
        $this->Cell(0, 5, 'Email: xtremeai@gmail.com', 0, 1, 'L');
        $this->Cell(0, 5, 'Web: www.xtremeai.importecsolutions.com', 0, 1, 'L');
        $this->Ln(5);
    }

    // Pie de página personalizado
    function Footer() {
        $this->SetY(-40);
        // Código QR
        $this->Image('../assets/img/qr.png', 10, $this->GetY(), 30, 30);
        
        // Información adicional
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 5, 'Representacion Impresa de BOLETA DE VENTA ELECTRONICA', 0, 1, 'C');
        $this->Cell(0, 5, 'Autorizado por Resolucion 0340050007241/SUNAT', 0, 1, 'C');
        $this->Cell(0, 5, 'Gracias por su compra en XTREME AI - Huanta, Ayacucho, Peru', 0, 1, 'C');
    }
}

ob_end_clean();
$pdf = new PDF('P', 'mm', 'Letter');
$pdf->AddPage();
$pdf->SetFont("Arial", "", 10);
$pdf->SetMargins(10, 20, 10);

// Información del cliente
$pdf->SetFont("Arial", "B", 10);
$pdf->Cell(30, 5, 'Cliente:', 0, 0, 'L');
$pdf->SetFont("Arial", "", 10);
$pdf->Cell(50, 5, utf8_decode($dCliente['NombreCompleto']." ".$dCliente['Apellido']), 0, 1, 'L');
$pdf->Cell(30, 5, 'DNI:', 0, 0, 'L');
$pdf->Cell(50, 5, utf8_decode($dCliente['NIT']), 0, 1, 'L');
$pdf->Cell(30, 5, utf8_decode('Dirección:'), 0, 0, 'L');
$pdf->Cell(50, 5, utf8_decode($dCliente['Direccion']), 0, 1, 'L');
$pdf->Ln(10);

// Tabla de encabezado adicional (similar a la referencia)
// Tabla de encabezado adicional (similar a la referencia)
$pdf->SetFont("Arial", "B", 9);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(35, 7, utf8_decode('FECHA EMISIÓN'), 1, 0, 'C', true);
$pdf->Cell(35, 7, utf8_decode('FEC. VENCIMIENTO'), 1, 0, 'C', true);
$pdf->Cell(30, 7, utf8_decode('ORDEN COMPRA'), 1, 0, 'C', true); 
$pdf->Cell(35, 7, utf8_decode('GUÍA'), 1, 0, 'C', true);
$pdf->Cell(35, 7, utf8_decode('COND. DE PAGO'), 1, 0, 'C', true);
$pdf->Ln(7);

$pdf->SetFont("Arial", "", 9);
$pdf->Cell(35, 7, $dVenta['Fecha'], 1, 0, 'C');
$pdf->Cell(35, 7, '06/03/2024', 1, 0, 'C'); // Ejemplo de fecha de vencimiento
$pdf->Cell(30, 7, $id, 1, 0, 'C'); // Ajuste del ancho a 30
$pdf->Cell(35, 7, '---', 1, 0, 'C');
$pdf->Cell(35, 7, 'CONTADO', 1, 0, 'C');
$pdf->Ln(15);

// Tabla de productos
$pdf->SetFont("Arial", "B", 9);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(20, 7, 'CANTIDAD', 1, 0, 'C', true);
$pdf->Cell(20, 7, 'U.M', 1, 0, 'C', true);
$pdf->Cell(80, 7, 'DESCRIPCION', 1, 0, 'C', true);
$pdf->Cell(35, 7, 'PRECIO UNIT.', 1, 0, 'C', true);
$pdf->Cell(35, 7, 'IMPORTE (Inc. IGV)', 1, 0, 'C', true);
$pdf->Ln(7);

$pdf->SetFont("Arial", "", 9);
$suma = 0;
$sDet = ejecutarSQL::consultar("SELECT * FROM detalle WHERE NumPedido='" . $id . "'");
while ($fila1 = mysqli_fetch_array($sDet, MYSQLI_ASSOC)) {
    $consulta = ejecutarSQL::consultar("SELECT * FROM producto WHERE CodigoProd='" . $fila1['CodigoProd'] . "'");
    $fila = mysqli_fetch_array($consulta, MYSQLI_ASSOC);
    $pdf->Cell(20, 7, utf8_decode($fila1['CantidadProductos']), 1, 0, 'C');
    $pdf->Cell(20, 7, 'UNIDAD', 1, 0, 'C');
    $pdf->Cell(80, 7, utf8_decode($fila['NombreProd']), 1, 0, 'L');
    $pdf->Cell(35, 7, 'S/.'.utf8_decode($fila1['PrecioProd']), 1, 0, 'C');
    $pdf->Cell(35, 7, 'S/.'.utf8_decode($fila1['PrecioProd'] * $fila1['CantidadProductos']), 1, 0, 'C');
    $pdf->Ln(7);
    $suma += $fila1['PrecioProd'] * $fila1['CantidadProductos'];
    mysqli_free_result($consulta);
}

// Subtotales
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell(155, 7, 'OP. GRAVADA (S/)', 1, 0, 'R');
$pdf->Cell(35, 7, 'S/.'.number_format($suma, 2), 1, 0, 'C');
$pdf->Ln(7);
$pdf->Cell(155, 7, 'TOTAL IGV (S/)', 1, 0, 'R');
$pdf->Cell(35, 7, 'S/.'.number_format($suma * 0.18, 2), 1, 0, 'C');
$pdf->Ln(7);
$pdf->Cell(155, 7, 'IMPORTE TOTAL (S/)', 1, 0, 'R');
$pdf->Cell(35, 7, 'S/.'.number_format($suma * 1.18, 2), 1, 0, 'C');
$pdf->Ln(15);

$pdf->Output('Boleta-Electronica-#' . $id, 'I');
mysqli_free_result($sVenta);
mysqli_free_result($sCliente);
mysqli_free_result($sDet);
?>

