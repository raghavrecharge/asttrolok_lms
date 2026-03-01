<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubscriptionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $subscriptions;

    public function __construct($subscriptions)
    {
        $this->subscriptions = collect($subscriptions);
    }

    public function collection()
    {
        return $this->subscriptions;
    }

    public function headings(): array
    {
        return [
            trans('admin/main.id'),
            trans('admin/main.title'),
            trans('admin/main.instructor'),
            trans('admin/main.price'),
            trans('admin/main.sales'),
            trans('admin/main.income'),
            trans('admin/main.subscription_count'),
            trans('admin/main.created_at'),
            trans('admin/main.status'),
        ];
    }

    public function map($subscription): array
    {
        $salesCount = $subscription->sales ? $subscription->sales->count() : 0;
        $salesIncome = $subscription->sales ? $subscription->sales->sum('total_amount') : 0;

        return [
            $subscription->id ?? '-',
            $subscription->title ?? '-',
            $subscription->teacher ? $subscription->teacher->full_name : '-',
            $subscription->price ? handlePrice($subscription->price, true, true) : trans('public.free'),
            $salesCount,
            handlePrice($salesIncome),
            $subscription->subscription_webinars_count ?? 0,
            dateTimeFormat($subscription->created_at, 'Y M j | H:i') ?? '-',
            $subscription->status ?? '-',
        ];
    }
}
