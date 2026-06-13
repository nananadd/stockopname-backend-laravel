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

    // Menangkap data $cycle dari Controller
    public function __construct($cycle)
    {
        $this->cycle = $cycle;
    }

    public function collection()
    {
        // Mengambil detail barang dari hasil laporan opname
        return $this->cycle->details;
    }

    public function headings(): array
    {
        // Membentuk 5 baris array untuk meniru Kop Surat laporan
        return [
            ['PT. SIGMA BERKAT SEJATI'],
            ['Laporan Hasil Cycle Count'],
            ['Per Tgl. ' . date('d M Y')],
            [''], // Baris ke-4 sengaja dikosongkan sebagai spasi
            ['Kode Barang', 'Nama Barang', 'Stok Sistem', 'Hitung Fisik', 'Selisih', 'Satuan']
        ];
    }

    public function map($detail): array
    {
        return [
            $detail->item->sku ?? '-',
            $detail->item->name ?? '-',
            $detail->system_stock_snapshot, // Memastikan data yang ditarik adalah snapshot saat opname
            $detail->physical_stock,
            $detail->difference,
            $detail->item->unit ?? 'PCS'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge (Gabungkan) Sel untuk Kop Surat dari kolom A sampai F
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');

        // Baris 1: PT SIGMA BERKAT SEJATI (Courier, Normal, 18pt)
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'name' => 'Courier', 
                'bold' => false, 
                'size' => 18
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // Baris 2: Judul Laporan (Arial, Bold, 18pt, Merah)
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'name' => 'Arial', 
                'bold' => true, 
                'size' => 18, 
                'color' => ['argb' => 'FFC00000']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // Baris 3: Tanggal Laporan (Courier, 11pt)
        $sheet->getStyle('A3')->applyFromArray([
            'font' => [
                'name' => 'Courier', 
                'size' => 11
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // Baris 5: Header Tabel (Arial, Bold, 11pt, Biru Muda)
        $sheet->getStyle('A5:F5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF000080']],
            'borders' => [
                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // Logika Warna Selisih & Garis Pembatas Tipis Data
        $highestRow = $sheet->getHighestRow();
        for ($i = 6; $i <= $highestRow; $i++) {
            $selisih = $sheet->getCell('E' . $i)->getValue(); 
            
            // Warnai teks jika ada selisih stok
            if (is_numeric($selisih)) {
                if ($selisih < 0) {
                    $sheet->getStyle('E' . $i)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                } elseif ($selisih > 0) {
                    $sheet->getStyle('E' . $i)->getFont()->getColor()->setARGB('FF00B050'); 
                }
            }

            // Beri garis bawah sangat tipis (hairline) untuk estetika cetak
            $sheet->getStyle("A{$i}:F{$i}")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
        }
    }
}