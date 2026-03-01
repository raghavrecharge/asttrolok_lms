<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubscriptionStudentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $students;

    public function __construct($students)
    {
        $this->students = collect($students);
    }

    public function collection()
    {
        return $this->students;
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

    public function map($student): array
    {
        return [
            $student->id ?? '-',
            $student->full_name ?? '-',
            $student->mobile ?? '-',
            $student->email ?? '-',
            !empty($student->purchase_date) ? date('d M Y', is_numeric($student->purchase_date) ? $student->purchase_date : strtotime($student->purchase_date)) : '-',
            isset($student->learning) ? round($student->learning, 1) . '%' : '0%',
            $student->status ?? '-',
        ];
    }
}
