<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerExportService
{
    private const HEADERS = [
        'Name', 'Email', 'Phone', 'Country', 'Segment', 'Status',
        'Stays', 'Nights', 'Customer value (VND)', 'Next stay', 'Next apartment',
    ];

    /**
     * @param  array<int, array<string, mixed>>  $customers  Rows as returned by
     *                                                        CustomerAggregationService::listCustomers()
     */
    public function streamXlsx(array $customers): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Customers');

        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);

        $rowIndex = 2;
        foreach ($customers as $customer) {
            $sheet->fromArray([
                $customer['name'] ?? '',
                $customer['rawEmail'] ?? '',
                $customer['phone'] ?? '',
                $customer['country'] ?? '',
                $customer['segment'] ?? '',
                $customer['status'] ?? '',
                $customer['stays'] ?? 0,
                $customer['nights'] ?? 0,
                $customer['revenue'] ?? 0,
                $customer['nextLabel'] ?? '',
                $customer['nextApt'] ?? '',
            ], null, "A{$rowIndex}");
            $rowIndex++;
        }

        foreach (range('A', 'K') as $column) {
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
