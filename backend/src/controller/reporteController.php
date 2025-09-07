<?php
namespace Src\Controller;

require_once __DIR__ . '/../model/reporte.php';
require_once __DIR__ . '/../model/conexion.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php'; // Ruta a FPDF
require_once __DIR__ . '/../lib/xlsxwriter.class.php'; // Ruta a XLSXWriter

use Src\Model\ReporteModel;

class ReporteController
{
    private $model;

    public function __construct()
    {
        global $pdo;
        $this->model = new ReporteModel($pdo);
    }

    // Endpoint: /api/reporte/inventario (GET)
    public function inventario()
    {
        header('Content-Type: application/json');
        $categoria = $_GET['categoria'] ?? null;
        try {
            $productos = $this->model->obtenerInventario($categoria);
            echo json_encode(['exito' => true, 'productos' => $productos]);
        } catch (\Exception $e) {
            echo json_encode(['exito' => false, 'mensaje' => 'Error al listar inventario', 'error' => $e->getMessage()]);
        }
    }

public function inventarioPDF()
{
    try {
        $categoria = $_GET['categoria'] ?? null;
        $productos = $this->model->obtenerInventario($categoria);

        // Zona horaria Perú
        date_default_timezone_set('America/Lima');
        $fechaGeneracion = date('d/m/Y, h:i:s a');

        $pdf = new \FPDF();
        $pdf->AddPage();

        // Reducir márgenes para aprovechar espacio
        $pdf->SetLeftMargin(10);
        $pdf->SetRightMargin(10);

        // Cabecera
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Elevadores Montacarga E.I.R.L', 0, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, 'Reporte de Inventario General', 0, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, 'Fecha de generacion: ' . $fechaGeneracion, 0, 1, 'R');

        $pdf->Ln(8);

        // Encabezados de tabla ajustados al ancho total de la hoja
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 8, 'Codigo', 1);
        $pdf->Cell(35, 8, 'Producto', 1);
        $pdf->Cell(50, 8, 'Categoria', 1);
        $pdf->Cell(20, 8, 'Costo', 1, 0, 'R');
        $pdf->Cell(15, 8, 'Stock', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Ult. Movimiento', 1, 0, 'C');
        $pdf->Ln();

        // Contenido de la tabla
        $pdf->SetFont('Arial', '', 10);
        foreach ($productos as $prod) {
            $ultimoMovimiento = 'N/A';
            if (!empty($prod['fecha_ultimo_movimiento'])) {
                $fecha = new \DateTime($prod['fecha_ultimo_movimiento']);
                $fecha->setTimezone(new \DateTimeZone('America/Lima'));
                $ultimoMovimiento = $fecha->format('d/m/Y, h:i:s a');
            }

            $pdf->Cell(20, 8, $prod['codigo'], 1);
            $pdf->Cell(35, 8, $prod['nombre'], 1);
            $pdf->Cell(50, 8, $prod['categoria'], 1);
            $pdf->Cell(20, 8, number_format($prod['costo'], 2), 1, 0, 'R');
            $pdf->Cell(15, 8, $prod['stock'], 1, 0, 'C');
            $pdf->Cell(50, 8, $ultimoMovimiento, 1, 0, 'C');
            $pdf->Ln();
        }

        // Generar PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="inventario.pdf"');
        $pdf->Output('D', 'inventario.pdf');
        exit;

    } catch (\Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'exito' => false,
            'mensaje' => 'Error al generar PDF',
            'error' => $e->getMessage()
        ]);
    }
}

// Endpoint: /api/reporte/inventario/excel (GET)
public function inventarioExcel()
{
    try {
        $categoria = $_GET['categoria'] ?? null;
        $productos = $this->model->obtenerInventario($categoria);

        $writer = new \XLSXWriter();

        // Definir estilos
        $headerStyle = [
            'font-style' => 'bold',
            'halign' => 'center',
            'border' => 'left,right,top,bottom'
        ];

        $cellStyle = ['border' => 'left,right,top,bottom'];
        $rightStyle = array_merge($cellStyle, ['halign' => 'right']);
        $centerStyle = array_merge($cellStyle, ['halign' => 'center']);

        // Zona horaria Perú
        date_default_timezone_set('America/Lima');
        $fechaGeneracion = date('d/m/Y, h:i:s a');

        // Título centrado (merge de 7 columnas)
        $writer->writeSheetRow('Inventario', ['Elevadores Montacarga E.I.R.L'], [
            'font-style' => 'bold', 'font-size' => 16, 'halign' => 'center', 'height' => 25
        ]);
        $writer->markMergedCell('Inventario', 0, 0, 0, 6);

        // Subtítulo
        $writer->writeSheetRow('Inventario', ['Reporte de Inventario General'], [
            'font-size' => 12, 'halign' => 'center'
        ]);
        $writer->markMergedCell('Inventario', 1, 0, 1, 6);

        // Fecha de generación
        $writer->writeSheetRow('Inventario', ['Fecha de generacion: ' . $fechaGeneracion], [
            'halign' => 'right'
        ]);
        $writer->markMergedCell('Inventario', 2, 0, 2, 6);

        // Espacio
        $writer->writeSheetRow('Inventario', [''], []);

        // Encabezado con la columna extra para "Últ. Movimiento"
        $writer->writeSheetHeader('Inventario', [
            'Código' => 'string',
            'Producto' => 'string',
            'Categoría' => 'string',
            'Costo' => '0.00',
            'Stock' => 'integer',
            'Últ. Movimiento' => 'string',
            '' => 'string' // columna vacía para combinar
        ], [
            'widths' => [20, 35, 80, 20, 15, 75, 1],
            'font-style' => 'bold',
            'halign' => 'center',
            'border' => 'left,right,top,bottom'
        ]);
$writer->markMergedCell('Inventario', 4, 5, 4, 6);
        // Datos
        $fila = 5; // Comienza después de los headers
        foreach ($productos as $prod) {
            $ultimoMovimiento = 'N/A';
            if (!empty($prod['fecha_ultimo_movimiento'])) {
                $fecha = new \DateTime($prod['fecha_ultimo_movimiento']);
                $fecha->setTimezone(new \DateTimeZone('America/Lima'));
                $ultimoMovimiento = $fecha->format('d/m/Y, h:i:s a');
            }

            $writer->writeSheetRow('Inventario', [
                $prod['codigo'],
                $prod['nombre'],
                $prod['categoria'],
                $prod['costo'],
                $prod['stock'],
                $ultimoMovimiento,
                '' // celda vacía para combinar
            ], [
                $cellStyle,
                $cellStyle,
                $cellStyle,
                $rightStyle,
                $centerStyle,
                $rightStyle,
                $rightStyle
            ]);

            // Combinar columnas 5 y 6 para "Últ. Movimiento"
            $writer->markMergedCell('Inventario', $fila, 5, $fila, 6);
            $fila++;
        }

        // Guardar temporalmente
        $filePath = sys_get_temp_dir() . '/inventario.xlsx';
        $writer->writeToFile($filePath);

        if (file_exists($filePath)) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="inventario.xlsx"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            unlink($filePath);
            exit;
        } else {
            throw new \Exception("No se pudo crear el archivo Excel");
        }

    } catch (\Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'exito' => false,
            'mensaje' => 'Error al generar Excel',
            'error' => $e->getMessage()
        ]);
    }
}


}
