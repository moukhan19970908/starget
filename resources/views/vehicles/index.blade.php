@extends('layouts.app')
@section('title', 'Автопарк')

@section('header_left')
<div>
    <div class="page-title">Автопарк</div>
    <div class="page-subtitle">Управление транспортными средствами</div>
</div>
@endsection

@section('header_actions')
<a href="/vehicles/create" class="btn btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
    Добавить транспорт
</a>
@endsection

@section('content')

<div class="ops-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
    <div class="ops-card">
        <div class="ops-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .5M13 16h2l3-4.5V9h-5v7z"/></svg></div>
        <div class="ops-value" id="kpiTotal">—</div>
        <div class="ops-label">Всего единиц</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .5M13 16h2l3-4.5V9h-5v7z"/></svg></div>
        <div class="ops-value" id="kpiInTransit">—</div>
        <div class="ops-label">В рейсе</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
        <div class="ops-value" id="kpiIdle">—</div>
        <div class="ops-label">Простой</div>
        <div class="ops-sublabel">Готовы к назначению</div>
    </div>
    <div class="ops-card border-red">
        <div class="ops-icon red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
        <div class="ops-value" id="kpiRepair">—</div>
        <div class="ops-label">В ремонте</div>
        <div class="ops-sublabel" style="color:var(--danger)">Требует внимания</div>
    </div>
</div>

<div class="toolbar">
    <div class="tabs" id="statusTabs">
        <button class="tab active" data-status="">Все</button>
        <button class="tab" data-status="available">Свободные</button>
        <button class="tab" data-status="in_transit">В рейсе</button>
        <button class="tab" data-status="maintenance">Ремонт</button>
    </div>
    <div class="toolbar-right">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" id="searchInput" placeholder="Поиск по номеру, марке, владельцу..." oninput="debounceLoad()">
        </div>
        <select class="form-select" id="typeFilter" onchange="loadVehicles()" style="width:160px">
            <option value="">Все типы</option>
        </select>
    </div>
</div>

<div class="table-card">
    <div class="table-count" id="tableCount"></div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Тягач</th>
                <th>Прицеп</th>
                <th>Тип</th>
                <th>Тоннаж</th>
                <th>Владелец</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="vehiclesTable">
            <tr><td colspan="7" class="table-empty">Загрузка...</td></tr>
        </tbody>
    </table>
</div>

<div id="vehiclesPagination" class="pagination" style="margin-top:16px"></div>

@endsection

@push('scripts')
<script>
let debounce;
let currentStatus = '';

function debounceLoad() {
    clearTimeout(debounce);
    debounce = setTimeout(loadVehicles, 350);
}

async function init() {
    const types = await Starget.loadVehicleTypes();
    const sel = document.getElementById('typeFilter');
    types.forEach(t => {
        const opt = new Option(t.name, t.id);
        sel.add(opt);
    });
    loadVehicles();
}

async function loadVehicles(page) {
    const q    = document.getElementById('searchInput').value;
    const type = document.getElementById('typeFilter').value;
    let url = `/vehicles?page=${page || 1}`;
    if (currentStatus) url += `&status=${currentStatus}`;
    if (q)    url += `&search=${encodeURIComponent(q)}`;
    if (type) url += `&vehicle_type_id=${type}`;

    const res = await Starget.api('GET', url);
    if (!res) return;

    if (res.stats) {
        Starget.dom.set('kpiTotal',     res.stats.total     || 0);
        Starget.dom.set('kpiInTransit', res.stats.in_transit|| 0);
        Starget.dom.set('kpiIdle',      res.stats.idle      || 0);
        Starget.dom.set('kpiRepair',    res.stats.repair    || 0);
    }

    const tbody = document.getElementById('vehiclesTable');
    const items = res.data || [];

    if (res.meta) {
        document.getElementById('tableCount').textContent =
            `Показано ${items.length} из ${res.meta.total} записей`;
    }

    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="table-empty">Транспорт не найден</td></tr>';
        return;
    }

    tbody.innerHTML = items.map(v => {
        const ownerName = v.owner?.company_name || v.owner?.name || '—';
        const initials  = Starget.fmt.initials(ownerName);
        return `<tr>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="vehicle-icon-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .5M13 16h2l3-4.5V9h-5v7z"/></svg>
                    </div>
                    <div>
                        <div style="font-weight:500">${v.tractor_brand || '—'}</div>
                        <div class="plate-badge">${v.tractor_plate || '—'}</div>
                    </div>
                </div>
            </td>
            <td>
                <div style="font-weight:500">${v.trailer_brand || '—'}</div>
                <div class="plate-badge">${v.trailer_plate || '—'}</div>
            </td>
            <td>${v.vehicle_type?.name || '—'}</td>
            <td><span class="badge badge-secondary">${v.tonnage ? v.tonnage + ' т' : '—'}</span></td>
            <td>
                <div style="display:flex;align-items:center;gap:6px">
                    <div class="initials-avatar sm" style="background:#2563eb">${initials}</div>
                    <span>${ownerName}</span>
                </div>
            </td>
            <td>${Starget.fmt.status(v.status || 'available')}</td>
            <td><div class="row-actions"><button class="action-link" onclick="viewVehicle(${v.id})">Открыть</button></div></td>
        </tr>`;
    }).join('');

    if (res.meta) Starget.renderPagination(res.meta, 'vehiclesPagination', loadVehicles);
}

function viewVehicle(id) {
    Starget.toast('Просмотр транспорта — в разработке', 'info');
}

document.getElementById('statusTabs').addEventListener('click', e => {
    const tab = e.target.closest('.tab');
    if (!tab) return;
    document.querySelectorAll('#statusTabs .tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    currentStatus = tab.dataset.status || '';
    loadVehicles();
});

init();
</script>
@endpush
