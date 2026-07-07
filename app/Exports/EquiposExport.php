<?php

namespace App\Exports;

use App\Models\Equipo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EquiposExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle, WithColumnFormatting
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Equipo::with(['categoria', 'unidadMedida'])->get();
    }

    /**
     * Nombre de la pestaña (hoja) en Excel
     */
    public function title(): string
    {
        return 'Inventario General';
    }

    /**
     * Encabezados de la tabla
     */
    public function headings(): array
    {
        return [
            'Código', 
            'Nombre del Equipo', 
            'Categoría', 
            'Unidad', 
            'Tipo Operación',
            'Precio Día (Renta)', 
            'Precio de Venta', 
            'Stock Actual', 
            'Stock Mínimo', 
            'Descripción'
        ];
    }

    /**
     * Mapeo de los datos por cada fila
     */
    public function map($equipo): array
    {
        return [
            $equipo->codigo,
            $equipo->nombre,
            $equipo->categoria ? $equipo->categoria->nombre : 'Sin categoría',
            $equipo->unidadMedida ? $equipo->unidadMedida->abreviatura : 'Sin unidad',
            strtoupper($equipo->tipo_operacion), // Se pone en mayúsculas para mejor vista
            $equipo->precio_dia,
            $equipo->precio_venta,
            $equipo->stock,
            $equipo->stock_minimo,
            $equipo->descripcion
        ];
    }

    /**
     * Formato específico para columnas (ej. Moneda para precios)
     */
    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Columna de Precio Día
            'G' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Columna de Precio Venta
        ];
    }

    /**
     * Estilos visuales de la hoja
     */
    public function styles(Worksheet $sheet)
    {
        // 1. Obtener la última fila y columna para aplicar bordes a toda la tabla
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $rangoTabla = "A1:{$highestColumn}{$highestRow}";

        // 2. Aplicar bordes a toda la tabla y centrado vertical
        $sheet->getStyle($rangoTabla)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFBFBFBF'], // Gris claro
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 3. Estilo profesional para la fila de Encabezados (Fila 1)
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // Texto Blanco
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0D6EFD'], // Azul corporativo (Bootstrap Primary)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 4. Centrar columnas de datos numéricos y códigos (A, E, F, G, H, I)
        $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E2:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 5. Ajustar altura de la fila de encabezados para darle más espacio
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }
}