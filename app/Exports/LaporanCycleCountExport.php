<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanCycleCountExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $cycle;
    private $rowNumber = 0;

    // Menangkap data $cycle dari Controller
    public function __construct($cycle)
    {
        $this->cycle = $cycle;
    }

    public function collection()
    {
        // Mengambil hanya detail barang dari Cycle Count yang dipilih
        return $this->cycle->details;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Barang (SKU)',
            'Nama Barang',
            'Stok Sistem (Awal)',
            'Hitung Fisik',
            'Selisih (Variance)',
            'Satuan'
        ];
    }

    public function map($detail): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $detail->item->sku ?? '-',
            $detail->item->name ?? '-',
            $detail->system_stock_snapshot,
            $detail->physical_stock,
            $detail->difference,
            $detail->item->unit ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Styling Header Biru ala PT Sigma Berkat Sejati
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F4E78'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ]
        ]);

        // Warnai teks selisih: Merah jika minus, Hijau jika plus (Sangat disukai dosen!)
        $highestRow = $sheet->getHighestRow();
        for ($i = 2; $i <= $highestRow; $i++) {
            $selisih = $sheet->getCell('F' . $i)->getValue();
            if ($selisih < 0) {
                $sheet->getStyle('F' . $i)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
            } elseif ($selisih > 0) {
                $sheet->getStyle('F' . $i)->getFont()->getColor()->setARGB('FF00B050'); // Hijau
            }
        }

        // Beri Border
        $sheet->getStyle("A1:G{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }
}