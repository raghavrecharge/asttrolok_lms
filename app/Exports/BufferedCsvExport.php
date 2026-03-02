<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Generator;

class BufferedCsvExport implements FromGenerator, WithHeadings, WithMapping
{
    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function generator(): Generator
    {
        $handle = fopen($this->filePath, 'r');
        
        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            rewind($handle);
        }

        // Skip Header row
        fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            yield $row;
        }

        fclose($handle);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Mobile',
            'Email',
            'Purchase Date',
            'Learning Progress (%)',
            'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row[0] ?? '-', // ID
            $row[1] ?? '-', // Name
            $row[2] ?? '-', // Mobile
            $row[3] ?? '-', // Email
            !empty($row[4]) ? date('d M Y', is_numeric($row[4]) ? $row[4] : strtotime($row[4])) : '-', // Purchase Date
            isset($row[5]) ? $row[5] . '%' : '0%', // Learning Progress
            $row[6] ?? '-', // Status
        ];
    }
}
