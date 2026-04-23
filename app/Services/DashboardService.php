<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Setting;
use App\Models\Transportation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getKpi(string $period = 'this_month'): array
    {
        [$start, $end] = $this->getPeriodDates($period);
        $vatRate = (float) Setting::get('vat_rate', 12) / 100;

        $revenue = Application::whereIn('status', ['closed', 'in_transit'])
            ->whereBetween('created_at', [$start, $end])
            ->sum('client_rate');

        $cost = Transportation::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->sum('supplier_rate');

        $profit = $revenue - $cost;
        $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0;
        $vat = round($revenue * $vatRate, 2);

        return [
            'total_revenue' => round($revenue, 2),
            'total_cost'    => round($cost, 2),
            'gross_profit'  => round($profit, 2),
            'margin_percent' => $margin,
            'vat_total'     => $vat,
        ];
    }

    public function getOperational(): array
    {
        return [
            'total_applications'     => Application::count(),
            'active_transportations' => Transportation::where('status', 'in_transit')->count(),
            'closed_applications'    => Application::where('status', 'closed')->count(),
            'client_refusals'        => Application::where('status', 'client_refusal')->count(),
            'logistics_in_work'      => Transportation::whereIn('status', ['open', 'in_transit'])->count(),
        ];
    }

    public function getChart(string $period = 'this_month'): array
    {
        [$start, $end] = $this->getPeriodDates($period);

        $months = Application::whereIn('status', ['closed', 'in_transit'])
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('MONTH(created_at) as month_num'),
                DB::raw('YEAR(created_at) as year_num'),
                DB::raw('SUM(client_rate) as revenue')
            )
            ->groupBy('year_num', 'month_num')
            ->orderBy('year_num')
            ->orderBy('month_num')
            ->get();

        $costs = Transportation::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('MONTH(created_at) as month_num'),
                DB::raw('SUM(supplier_rate) as cost')
            )
            ->groupBy('month_num')
            ->pluck('cost', 'month_num');

        $ruMonths = [
            1 => 'ЯНВ', 2 => 'ФЕВ', 3 => 'МАР', 4 => 'АПР',
            5 => 'МАЙ', 6 => 'ИЮН', 7 => 'ИЮЛ', 8 => 'АВГ',
            9 => 'СЕН', 10 => 'ОКТ', 11 => 'НОЯ', 12 => 'ДЕК',
        ];

        $chart = $months->map(function ($m) use ($costs, $ruMonths) {
            $revenue = (float) $m->revenue;
            $cost = (float) ($costs[$m->month_num] ?? 0);
            return [
                'month'   => $ruMonths[$m->month_num] ?? $m->month_num,
                'revenue' => round($revenue, 2),
                'profit'  => round($revenue - $cost, 2),
            ];
        })->values()->toArray();

        // Peak month
        $peakMonth = null;
        $peakGrowth = 0;
        $profitEfficiency = 0;

        if (!empty($chart)) {
            $maxRevenue = max(array_column($chart, 'revenue'));
            $peakData = collect($chart)->firstWhere('revenue', $maxRevenue);
            $peakMonth = $peakData['month'] ?? null;

            $totalRevenue = array_sum(array_column($chart, 'revenue'));
            $totalProfit = array_sum(array_column($chart, 'profit'));
            $profitEfficiency = $totalRevenue > 0
                ? round(($totalProfit / $totalRevenue) * 100, 1)
                : 0;
        }

        return [
            'revenue_by_month'          => $chart,
            'peak_month'                => $peakMonth,
            'peak_growth_percent'       => $peakGrowth,
            'profit_efficiency_percent' => $profitEfficiency,
        ];
    }

    public function getRecentActivity(int $limit = 5): array
    {
        return ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($log) => [
                'type'              => $log->type,
                'description'       => $log->description,
                'created_at_human'  => $log->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    private function getPeriodDates(string $period): array
    {
        return match($period) {
            'last_month' => [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ],
            'this_year' => [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear(),
            ],
            default => [ // this_month
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ],
        };
    }
}
