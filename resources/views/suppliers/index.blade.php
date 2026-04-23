@extends('layouts.app')
@section('title', 'Поставщики')

@section('header_left')
<div>
    <div class="page-title">Справочник: Поставщики</div>
    <div class="page-subtitle">Транспортные компании и ИП-перевозчики</div>
</div>
@endsection

@section('header_actions')
<a href="/suppliers/create" class="btn btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
    Создать поставщика
</a>
@endsection

@section('content')

<div class="ops-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
    <div class="ops-card">
        <div class="ops-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0H5m14 0v-2H5v2"/></svg></div>
        <div class="ops-value" id="kpiTotal">—</div>
        <div class="ops-label">Всего поставщиков</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .5M13 16h2l3-4.5V9h-5v7z"/></svg></div>
        <div class="ops-value" id="kpiActiveTrans">—</div>
        <div class="ops-label">Активные перевозки</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg></div>
        <div class="ops-value" id="kpiLegal">—</div>
        <div class="ops-label">Юридических лиц</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg></div>
        <div class="ops-value" id="kpiPending">—</div>
        <div class="ops-label">На проверке</div>
    </div>
</div>

<div class="toolbar">
    <div class="tabs" id="statusTabs">
        <button class="tab active" data-status="">Все</button>
        <button class="tab" data-status="active">Активные</button>
        <button class="tab" data-status="pending">На проверке</button>
        <button class="tab" data-status="blocked">Заблокированные</button>
    </div>
    <div class="toolbar-right">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" id="searchInput" placeholder="Поиск по названию, БИН..." oninput="debounceLoad()">
        </div>
        <select class="form-select" id="typeFilter" onchange="loadSuppliers()" style="width:160px">
            <option value="">Все типы</option>
            <option value="legal">Юр. лица</option>
            <option value="individual">Физ. лица</option>
        </select>
    </div>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Тип</th>
                <th>Компания / ИП</th>
                <th>БИН/ИИН</th>
                <th>Контакт</th>
                <th>Перевозки</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="suppliersTable">
            <tr><td colspan="7" class="table-empty">Загрузка...</td></tr>
        </tbody>
    </table>
</div>

<div id="suppliersPagination" class="pagination" style="margin-top:16px"></div>

@endsection

@push('scripts')
<script>
let debounce;
let currentStatus = '';

function debounceLoad() {
    clearTimeout(debounce);
    debounce = setTimeout(loadSuppliers, 350);
}

async function loadSuppliers(page) {
    const q    = document.getElementById('searchInput').value;
    const type = document.getElementById('typeFilter').value;
    let url = `/suppliers?page=${page || 1}`;
    if (currentStatus) url += `&status=${currentStatus}`;
    if (q)    url += `&search=${encodeURIComponent(q)}`;
    if (type) url += `&type=${type}`;

    const res = await Starget.api('GET', url);
    if (!res) return;

    if (res.stats) {
        Starget.dom.set('kpiTotal',       res.stats.total        || 0);
        Starget.dom.set('kpiActiveTrans', res.stats.active_trans || 0);
        Starget.dom.set('kpiLegal',       res.stats.legal        || 0);
        Starget.dom.set('kpiPending',     res.stats.pending      || 0);
    }

    const tbody = document.getElementById('suppliersTable');
    const items = res.data || [];
    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="table-empty">Поставщики не найдены</td></tr>';
        return;
    }

    tbody.innerHTML = items.map(s => {
        const isLegal = s.type === 'legal';
        const typeIcon = isLegal
            ? `<div class="type-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg></div>`
            : `<div class="type-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m8-10a4 4 0 100-8 4 4 0 000 8z"/></svg></div>`;
        return `<tr>
            <td>${typeIcon}</td>
            <td>
                <div style="font-weight:500">${s.company_name || s.name || '—'}</div>
                <div style="font-size:11px;color:var(--text-muted)">${s.specialization || ''}</div>
            </td>
            <td class="text-mono">${s.bin || s.iin || '—'}</td>
            <td>
                <div>${s.contact_person || '—'}</div>
                <div style="font-size:11px;color:var(--text-muted)">${s.phone || ''}</div>
            </td>
            <td><span style="font-weight:600">${s.transportations_count || 0}</span></td>
            <td>${Starget.fmt.status(s.status || 'active')}</td>
            <td><div class="row-actions"><button class="action-link" onclick="viewSupplier(${s.id})">Открыть</button></div></td>
        </tr>`;
    }).join('');

    if (res.meta) Starget.renderPagination(res.meta, 'suppliersPagination', loadSuppliers);
}

function viewSupplier(id) {
    Starget.toast('Просмотр поставщика — в разработке', 'info');
}

document.getElementById('statusTabs').addEventListener('click', e => {
    const tab = e.target.closest('.tab');
    if (!tab) return;
    document.querySelectorAll('#statusTabs .tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    currentStatus = tab.dataset.status || '';
    loadSuppliers();
});

loadSuppliers();
</script>
@endpush
