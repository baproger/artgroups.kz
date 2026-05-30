<?php

namespace App\Filament\Widgets;

use App\Models\Gate;
use App\Models\GateCategory;
use App\Models\Lead;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalLeads = Lead::count();
        $newLeads = Lead::where('status', 'new')->count();
        $todayLeads = Lead::whereDate('created_at', today())->count();

        $totalGates = Gate::where('is_active', true)->count();
        $gateCount = Gate::where('type', 'gate')->where('is_active', true)->count();
        $wicketCount = Gate::where('type', 'wicket')->where('is_active', true)->count();

        $totalProjects = Project::whereNotNull('result_image')->count();
        $weekProjects = Project::whereNotNull('result_image')
            ->where('created_at', '>=', now()->subDays(7))->count();

        return [
            Stat::make('Заявок всего', $totalLeads)
                ->description("Новых: {$newLeads} · Сегодня: {$todayLeads}")
                ->descriptionIcon('heroicon-m-envelope')
                ->color('warning')
                ->chart(
                    Lead::selectRaw('COUNT(*) as count')
                        ->where('created_at', '>=', now()->subDays(7))
                        ->groupByRaw('DATE(created_at)')
                        ->orderByRaw('DATE(created_at)')
                        ->pluck('count')
                        ->toArray()
                ),

            Stat::make('Моделей ворот', $totalGates)
                ->description("Ворота: {$gateCount} · Калитки: {$wicketCount}")
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),

            Stat::make('Категорий', GateCategory::where('is_active', true)->count())
                ->description('Активных категорий')
                ->descriptionIcon('heroicon-m-tag')
                ->color('success'),

            Stat::make('Завершённых визуализаций', $totalProjects)
                ->description("За 7 дней: {$weekProjects}")
                ->descriptionIcon('heroicon-m-photo')
                ->color('primary'),
        ];
    }
}
