<?php

namespace App\Services;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerExportService
{
    private const HEADERS = [
        'Name', 'Email', 'Phone', 'Country', 'Segment', 'Status',
        'Stays', 'Nights', 'Total revenue (VND)', 'Next stay',
    ];

    public function streamXlsx(Collection $rows): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Customers');

        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

        $rowIndex = 2;
        foreach ($rows as $row) {
            $sheet->fromArray([
                $row['name'],
                $row['email'],
                $row['phone'],
                $row['country'],
                $row['segment'],
                $row['status'],
                $row['bookings_count'],
                $row['nights_total'],
                $row['total_revenue'],
                $row['next_stay']['date'] ?? '',
            ], null, "A{$rowIndex}");
            $rowIndex++;
        }

        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'customers-'.now()->format('Y-m-d').'.xlsx';

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
