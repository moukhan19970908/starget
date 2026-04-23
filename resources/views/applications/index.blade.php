@extends('layouts.app')
@section('title', 'Заявки')

@section('header_left')
<div>
    <div class="page-title">Заявки</div>
    <div class="page-subtitle">Управление транспортными заявками</div>
</div>
@endsection

@section('header_actions')
<button class="header-btn" onclick="exportApps()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4m14-7l-5-5-5 5m5-5v12"/></svg>
    Экспорт
</button>
<a href="/applications/create" class="btn btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
    Создать заявку
</a>
@endsection

@section('content')

<div class="toolbar">
    <div class="tabs" id="statusTabs">
        <button class="tab active" data-status="">Все</button>
        <button class="tab" data-status="new">Новые</button>
        <button class="tab" data-status="in_progress">В работе</button>
        <button class="tab" data-status="completed">Завершённые</button>
        <button class="tab" data-status="cancelled">Отказано</button>
    </div>
    <div class="toolbar-right">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" id="searchInput" placeholder="Поиск по ID, клиенту, маршруту..." oninput="debounceLoad()">
        </div>
        <button class="header-btn" onclick="toggleFilters()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h18M7 8h10M11 12h2"/></svg>
            Фильтры
        </button>
    </div>
</div>

<div class="filter-panel" id="filterPanel" style="display:none">
    <div class="filter-grid">
        <div class="filter-group">
            <label>Менеджер</label>
            <select id="fManager" onchange="loadApps()"><option value="">Все</option></select>
        </div>
        <div class="filter-group">
            <label>Дата (от)</label>
            <input type="date" id="fDateFrom" onchange="loadApps()">
        </div>
        <div class="filter-group">
            <label>Дата (до)</label>
            <input type="date" id="fDateTo" onchange="loadApps()">
        </div>
        <div style="align-self:end">
            <button class="btn btn-outline" onclick="resetFilters()">Сбросить</button>
        </div>
    </div>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID заявки</th>
                <th>Клиент</th>
                <th>Маршрут</th>
                <th>Статус</th>
                <th>Менеджер</th>
                <th>Транспорт</th>
                <th>Стоимость</th>
                <th>Дата</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="appsTableBody">
            <tr><td colspan="9" class="table-empty">Загрузка...</td></tr>
        </tbody>
    </table>
</div>

<div id="appsPagination" class="pagination" style="margin-top:16px"></div>

<div class="mini-kpi-row" id="appsMiniKpi" style="display:none">
    <div class="mini-kpi">
        <div class="mini-kpi-label">% отказов</div>
        <div class="mini-kpi-value" id="mkRefusalRate">—</div>
    </div>
    <div class="mini-kpi">
        <div class="mini-kpi-label">Ожидаемые выплаты</div>
        <div class="mini-kpi-value" id="mkExpectedPayouts">—</div>
    </div>
    <div class="mini-kpi">
        <div class="mini-kpi-label">На рассмотрении</div>
        <div class="mini-kpi-value" id="mkPending">—</div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentPage = 1;
let currentStatus = '';
let debounce;

function debounceLoad() {
    clearTimeout(debounce);
    debounce = setTimeout(loadApps, 350);
}

function toggleFilters() {
    const p = document.getElementById('filterPanel');
    p.style.display = p.style.display === 'none' ? 'block' : 'none';
}

function resetFilters() {
    document.getElementById('fDateFrom').value = '';
    document.getElementById('fDateTo').value = '';
    document.getElementById('fManager').value = '';
    loadApps();
}

async function loadApps(page) {
    currentPage = page || 1;
    const q      = document.getElementById('searchInput').value;
    const from   = document.getElementById('fDateFrom').value;
    const to     = document.getElementById('fDateTo').value;
    let url = `/applications?page=${currentPage}`;
    if (currentStatus) url += `&status=${currentStatus}`;
    if (q)    url += `&search=${encodeURIComponent(q)}`;
    if (from) url += `&date_from=${from}`;
    if (to)   url += `&date_to=${to}`;

    const res = await Starget.api('GET', url);
    if (!res) return;

    const tbody = document.getElementById('appsTableBody');
    const items = res.data || [];

    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="table-empty">Заявки не найдены</td></tr>';
        document.getElementById('appsPagination').innerHTML = '';
        return;
    }

    tbody.innerHTML = items.map(a => {
        const route = [a.from_city?.name, a.to_city?.name].filter(Boolean).join(' → ') || '—';
        const transport = a.active_transportation?.vehicle ?
            `${a.active_transportation.vehicle.tractor_brand || ''} ${a.active_transportation.vehicle.tractor_plate || ''}`.trim() : '—';
        return `<tr onclick="location.href='/applications/${a.id}'" style="cursor:pointer">
            <td><span class="app-id">#${a.id}</span></td>
            <td>${a.client?.company_name || a.client?.name || '—'}</td>
            <td>${route}</td>
            <td>${Starget.fmt.status(a.status)}</td>
            <td>${a.manager?.name || '—'}</td>
            <td><span class="text-muted">${transport}</span></td>
            <td>${Starget.fmt.money(a.total_cost, a.currency)}</td>
            <td>${Starget.fmt.date(a.created_at)}</td>
            <td>
                <div class="row-actions">
                    <a href="/applications/${a.id}" class="action-link" onclick="event.stopPropagation()">Открыть</a>
                    <a href="/transportations/create?application_id=${a.id}" class="action-link" onclick="event.stopPropagation()">Перевозка</a>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (res.meta) {
        Starget.renderPagination(res.meta, 'appsPagination', loadApps);
    }

    // Show mini KPI
    document.getElementById('appsMiniKpi').style.display = 'flex';
    if (res.stats) {
        Starget.dom.set('mkRefusalRate', (res.stats.refusal_rate || 0).toFixed(1) + '%');
        Starget.dom.set('mkExpectedPayouts', Starget.fmt.money(res.stats.expected_payouts || 0));
        Starget.dom.set('mkPending', res.stats.pending || 0);
    }
}

// Tab switching
document.getElementById('statusTabs').addEventListener('click', e => {
    const tab = e.target.closest('.tab');
    if (!tab) return;
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    currentStatus = tab.dataset.status || '';
    loadApps();
});

loadApps();
</script>
@endpush
