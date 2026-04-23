@extends('layouts.app')
@section('title', 'Контракты')

@section('header_left')
<div>
    <div class="page-title">Реестр договоров</div>
    <div class="page-subtitle">Все клиентские и поставщиковые договоры</div>
</div>
@endsection

@section('header_actions')
<button class="header-btn" onclick="exportContracts()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4m14-7l-5-5-5 5m5-5v12"/></svg>
    Экспорт
</button>
<button class="btn btn-primary" onclick="createContract()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
    Создать договор
</button>
@endsection

@section('content')

{{-- KPI ROW --}}
<div class="ops-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
    <div class="ops-card">
        <div class="ops-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h3M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg></div>
        <div class="ops-value" id="kpiTotal">—</div>
        <div class="ops-label">Всего договоров</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="ops-value" id="kpiActive">—</div>
        <div class="ops-label">Активные</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg></div>
        <div class="ops-value" id="kpiExpiring">—</div>
        <div class="ops-label">Истекают (30 дней)</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="ops-value" id="kpiExpired">—</div>
        <div class="ops-label">Завершённые</div>
    </div>
</div>

<div class="toolbar">
    <div class="tabs" id="statusTabs">
        <button class="tab active" data-status="">Все</button>
        <button class="tab" data-status="active">Активные</button>
        <button class="tab" data-status="expired">Истёкшие</button>
        <button class="tab" data-status="terminated">Расторгнутые</button>
    </div>
    <div class="toolbar-right">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" id="searchInput" placeholder="Поиск по номеру, контрагенту..." oninput="debounceLoad()">
        </div>
        <select class="form-select" id="typeFilter" onchange="loadContracts()" style="width:160px">
            <option value="">Все типы</option>
            <option value="client">Клиентские</option>
            <option value="supplier">Поставщиковые</option>
        </select>
    </div>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Номер договора</th>
                <th>Контрагент</th>
                <th>Тип</th>
                <th>Заключён</th>
                <th>Срок действия</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="contractsTable">
            <tr><td colspan="7" class="table-empty">Загрузка...</td></tr>
        </tbody>
    </table>
</div>

<div id="contractsPagination" class="pagination" style="margin-top:16px"></div>

@endsection

@push('scripts')
<script>
let currentPage = 1;
let currentStatus = '';
let debounce;

function debounceLoad() {
    clearTimeout(debounce);
    debounce = setTimeout(loadContracts, 350);
}

async function loadContracts(page) {
    currentPage = page || 1;
    const q    = document.getElementById('searchInput').value;
    const type = document.getElementById('typeFilter').value;
    let url = `/contracts?page=${currentPage}`;
    if (currentStatus) url += `&status=${currentStatus}`;
    if (q)    url += `&search=${encodeURIComponent(q)}`;
    if (type) url += `&type=${type}`;

    const res = await Starget.api('GET', url);
    if (!res) return;

    // KPI from meta stats
    if (res.stats) {
        Starget.dom.set('kpiTotal',    res.stats.total    || 0);
        Starget.dom.set('kpiActive',   res.stats.active   || 0);
        Starget.dom.set('kpiExpiring', res.stats.expiring || 0);
        Starget.dom.set('kpiExpired',  res.stats.expired  || 0);
    }

    const tbody = document.getElementById('contractsTable');
    const items = res.data || [];
    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="table-empty">Договоры не найдены</td></tr>';
        document.getElementById('contractsPagination').innerHTML = '';
        return;
    }

    const now = new Date();
    tbody.innerHTML = items.map(c => {
        const isExpiring = c.end_date && (new Date(c.end_date) - now) < 30 * 86400000 && c.status === 'active';
        const endDateHtml = c.end_date
            ? `<span style="color:${isExpiring ? 'var(--danger)' : 'inherit'}">${Starget.fmt.date(c.end_date)}${isExpiring ? ' ⚠' : ''}</span>`
            : '—';
        const party = c.client?.company_name || c.client?.name || c.supplier?.company_name || c.supplier?.name || '—';
        return `<tr>
            <td><strong>${c.number || '—'}</strong></td>
            <td>${party}</td>
            <td>${c.type === 'client' ? '<span class="badge badge-primary">Клиентский</span>' : '<span class="badge badge-secondary">Поставщик</span>'}</td>
            <td>${Starget.fmt.date(c.start_date) || '—'}</td>
            <td>${endDateHtml}</td>
            <td>${Starget.fmt.status(c.status)}</td>
            <td>
                <div class="row-actions">
                    <button class="action-link" onclick="viewContract(${c.id})">Открыть</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (res.meta) {
        Starget.renderPagination(res.meta, 'contractsPagination', loadContracts);
    }
}

function viewContract(id) {
    // TODO: open contract detail modal or page
    Starget.toast('Просмотр договора — в разработке', 'info');
}

function createContract() {
    Starget.toast('Создание договора — в разработке', 'info');
}

function exportContracts() {
    Starget.toast('Экспорт — в разработке', 'info');
}

document.getElementById('statusTabs').addEventListener('click', e => {
    const tab = e.target.closest('.tab');
    if (!tab) return;
    document.querySelectorAll('#statusTabs .tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    currentStatus = tab.dataset.status || '';
    loadContracts();
});

loadContracts();
</script>
@endpush
