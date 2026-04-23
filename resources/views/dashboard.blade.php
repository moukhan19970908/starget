@extends('layouts.app')
@section('title', 'Дашборд')

@section('header_left')
<div>
    <div class="page-title">Панель управления</div>
    <div class="page-subtitle">Аналитика в реальном времени и KPI логистики</div>
</div>
@endsection

@section('header_actions')
<select class="period-select" id="periodSelect" onchange="loadDashboard()">
    <option value="this_month">Этот месяц</option>
    <option value="last_month">Прошлый месяц</option>
    <option value="this_year">Этот год</option>
    <option value="last_year">Прошлый год</option>
</select>
<button class="header-btn" onclick="exportDashboard()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4m14-7l-5-5-5 5m5-5v12"/></svg>
    Экспорт отчета
</button>
@endsection

@section('content')

{{-- KPI GRID --}}
<div class="kpi-grid" id="kpiGrid">
    <div class="kpi-card border-blue">
        <div class="kpi-label">Общая выручка</div>
        <div class="kpi-value" id="kpiRevenue">—</div>
        <div class="kpi-subtext"><span id="kpiRevenueSub" class="kpi-up">...</span></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Себестоимость</div>
        <div class="kpi-value" id="kpiCost">—</div>
        <div class="kpi-subtext" id="kpiCostSub">...</div>
    </div>
    <div class="kpi-card border-green">
        <div class="kpi-label">Валовая прибыль</div>
        <div class="kpi-value" id="kpiProfit">—</div>
        <div class="kpi-subtext"><span id="kpiProfitSub" class="kpi-up">...</span></div>
    </div>
    <div class="kpi-card border-blue">
        <div class="kpi-label">Маржа (%)</div>
        <div class="kpi-value" id="kpiMargin">—</div>
        <div class="kpi-subtext"><span class="kpi-up">Выше цели</span></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">НДС</div>
        <div class="kpi-value" id="kpiVat">—</div>
        <div class="kpi-subtext text-muted" id="kpiVatSub">Налоговый период Q3</div>
    </div>
</div>

{{-- OPERATIONAL STATS --}}
<div class="ops-grid">
    <div class="ops-card">
        <div class="ops-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
        <div class="ops-value" id="opsTotalApps">—</div>
        <div class="ops-label">Всего заявок</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .5M13 16h2l3-4.5V9h-5v7z"/></svg></div>
        <div class="ops-value" id="opsActiveTrans">—</div>
        <div class="ops-label">Активные перевозки</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="ops-value" id="opsClosedApps">—</div>
        <div class="ops-label">Закрытые заявки</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="ops-value" id="opsRefusals">—</div>
        <div class="ops-label">Отказы клиентов</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
        <div class="ops-value" id="opsLogistics">—</div>
        <div class="ops-label">Логистика в работе</div>
    </div>
</div>

{{-- CHART + ACTIVITY --}}
<div class="dashboard-grid">
    <div class="chart-card">
        <div class="chart-card-header">
            <div>
                <div class="chart-title">Динамика выручки и прибыли</div>
                <div class="chart-subtitle">Обзор эффективности за последние 12 месяцев</div>
            </div>
            <div class="chart-legend">
                <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--text-muted)">
                    <div class="legend-dot" style="background:#2563eb"></div> Выручка
                </div>
                <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--text-muted)">
                    <div class="legend-dot" style="background:#16a34a"></div> Прибыль
                </div>
            </div>
        </div>
        <div class="chart-container"><canvas id="revenueChart"></canvas></div>
        <div class="chart-footer">
            <div>
                <div class="chart-stat-label">Пик производительности</div>
                <div class="chart-stat-value" id="chartPeak">—</div>
                <div class="chart-stat-sub" id="chartPeakSub"></div>
            </div>
            <div>
                <div class="chart-stat-label">Эффективность прибыли</div>
                <div class="chart-stat-value" id="chartEfficiency">—</div>
                <div class="chart-stat-sub">Лучший Q3</div>
            </div>
        </div>
    </div>

    <div class="activity-panel">
        <div class="activity-header">Последняя активность</div>
        <div class="activity-list" id="activityList">
            <div style="padding:30px;text-align:center;color:var(--text-muted);font-size:13px">Загрузка...</div>
        </div>
        <div class="activity-footer"><a href="/transportations">ВСЕ ОПЕРАЦИИ</a></div>
    </div>
</div>

@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
@endpush

@push('scripts')
<script>
let chart = null;

async function loadDashboard() {
    const period = document.getElementById('periodSelect').value;
    const res = await Starget.api('GET', '/dashboard?period=' + period);
    if (!res || !res.success) return;
    const d = res.data;
    renderFinancial(d.financial);
    renderOperational(d.operational);
    renderChart(d.chart);
    renderActivity(d.recent_activity);
}

function renderFinancial(f) {
    if (!f) return;
    Starget.dom.set('kpiRevenue', Starget.fmt.money(f.total_revenue));
    Starget.dom.set('kpiCost', Starget.fmt.money(f.total_cost));
    Starget.dom.set('kpiProfit', Starget.fmt.money(f.gross_profit));
    Starget.dom.set('kpiMargin', (f.margin_percent || 0).toFixed(1) + '%');
    Starget.dom.set('kpiVat', Starget.fmt.money(f.vat_total));
}

function renderOperational(o) {
    if (!o) return;
    Starget.dom.set('opsTotalApps',   o.total_applications || 0);
    Starget.dom.set('opsActiveTrans', o.active_transportations || 0);
    Starget.dom.set('opsClosedApps',  o.closed_applications || 0);
    Starget.dom.set('opsRefusals',    o.client_refusals || 0);
    Starget.dom.set('opsLogistics',   o.logistics_in_work || 0);
}

function renderChart(data) {
    if (!data) return;
    const ctx = document.getElementById('revenueChart').getContext('2d');
    if (chart) chart.destroy();
    chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels || [],
            datasets: [
                {
                    label: 'Выручка',
                    data: data.revenue || [],
                    backgroundColor: '#2563eb',
                    borderRadius: 4,
                    barPercentage: 0.6,
                },
                {
                    label: 'Прибыль',
                    data: data.profit || [],
                    backgroundColor: '#16a34a',
                    borderRadius: 4,
                    barPercentage: 0.6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
                y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, color: '#94a3b8',
                    callback: v => v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v } },
            },
        },
    });

    // Peak month
    if (data.revenue && data.revenue.length) {
        const maxIdx = data.revenue.indexOf(Math.max(...data.revenue));
        Starget.dom.set('chartPeak', (data.labels || [])[maxIdx] || '—');
    }
    // Efficiency
    const totalRev = (data.revenue || []).reduce((a, b) => a + b, 0);
    const totalPrf = (data.profit  || []).reduce((a, b) => a + b, 0);
    const eff = totalRev > 0 ? ((totalPrf / totalRev) * 100).toFixed(1) + '%' : '—';
    Starget.dom.set('chartEfficiency', eff);
}

const activityIconMap = {
    completed:       { icon: Starget.icon.truck,  cls: 'blue' },
    transportation_completed: { icon: Starget.icon.truck, cls: 'blue' },
    created:         { icon: Starget.icon.doc,    cls: 'green' },
    application_created: { icon: Starget.icon.doc, cls: 'green' },
    warning:         { icon: Starget.icon.warn,   cls: 'amber' },
    delay:           { icon: Starget.icon.warn,   cls: 'amber' },
};

function renderActivity(items) {
    const el = document.getElementById('activityList');
    if (!items || !items.length) {
        el.innerHTML = '<div style="padding:30px;text-align:center;color:var(--text-muted);font-size:13px">Нет активности</div>';
        return;
    }
    el.innerHTML = items.map(a => {
        const ico = activityIconMap[a.type] || { icon: Starget.icon.doc, cls: 'gray' };
        const badgeMap = {
            completed: '<span class="badge badge-success" style="font-size:9.5px">ЗАВЕРШЕНО</span>',
            warning:   '<span class="badge badge-warning" style="font-size:9.5px">ТРЕБУЕТ ВНИМАНИЯ</span>',
            created:   '<span class="badge badge-primary" style="font-size:9.5px">НА РАССМОТРЕНИИ</span>',
        };
        return `<div class="activity-item">
            <div class="activity-icon ${ico.cls}">${ico.icon}</div>
            <div>
                <div class="activity-title">${a.title || a.description || '—'}</div>
                <div class="activity-desc">${a.description || ''}</div>
                <div class="activity-time">${Starget.fmt.timeAgo(a.created_at)} ${badgeMap[a.type] || ''}</div>
            </div>
        </div>`;
    }).join('');
}

loadDashboard();
</script>
@endpush
