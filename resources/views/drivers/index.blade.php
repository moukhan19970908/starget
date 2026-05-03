@extends('layouts.app')
@section('title', 'Водители')

@section('header_left')
<div>
    <div class="breadcrumb">Справочники <span>›</span> Водители</div>
    <div class="page-title">Реестр водителей</div>
    <div class="page-subtitle">Управление персоналом перевозок</div>
</div>
@endsection

@section('header_actions')
<button class="header-btn" onclick="toggleFilters()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h18M7 8h10M11 12h2"/></svg>
    Фильтры
</button>
@if(true)
<a href="/drivers/create" class="btn btn-primary" id="btnCreateDriver">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
    Создать водителя
</a>
@endif
@endsection

@section('content')

<div class="ops-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
    <div class="ops-card">
        <div class="ops-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m8-10a4 4 0 100-8 4 4 0 000 8z"/></svg></div>
        <div class="ops-value" id="kpiTotal">—</div>
        <div class="ops-label">Всего в базе</div>
        <div class="ops-sublabel" id="kpiTotalSub" style="color:var(--success);font-size:10px"></div>
    </div>
    <div class="ops-card">
        <div class="ops-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .5M13 16h2l3-4.5V9h-5v7z"/></svg></div>
        <div class="ops-value" id="kpiOnRoute">—</div>
        <div class="ops-label">На рейсе</div>
        <div class="ops-sublabel" id="kpiOnRouteSub" style="color:var(--text-muted);font-size:10px"></div>
    </div>
    <div class="ops-card">
        <div class="ops-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg></div>
        <div class="ops-value" id="kpiExpiring">—</div>
        <div class="ops-label">Истекают документы</div>
        <div class="ops-sublabel" style="color:var(--warning);font-size:10px">Требует внимания</div>
    </div>
    <div class="ops-card">
        <div class="ops-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0112 2a8 8 0 018 8.2c0 7.3-8 11.8-8 11.8z"/></svg></div>
        <div class="ops-value" id="kpiReserve">—</div>
        <div class="ops-label">Резерв</div>
        <div class="ops-sublabel" style="color:var(--success);font-size:10px">Готовы к назначению</div>
    </div>
</div>

<div class="filter-panel" id="filterPanel" style="display:none;margin-bottom:16px">
    <div class="filter-grid">
        <div class="filter-group">
            <label>Статус документов</label>
            <select id="docFilter" onchange="loadDrivers()" class="form-select">
                <option value="">Все</option>
                <option value="valid">Валидны</option>
                <option value="expiring">Истекают</option>
                <option value="expired">Истекли</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Класс лицензии</label>
            <select id="licFilter" onchange="loadDrivers()" class="form-select">
                <option value="">Все</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="CE">CE</option>
            </select>
        </div>
        <div style="align-self:end">
            <button class="btn btn-outline" onclick="resetFilters()">Сбросить</button>
        </div>
    </div>
</div>

<div class="toolbar">
    <div class="tabs" id="statusTabs">
        <button class="tab active" data-status="">Все</button>
        <button class="tab" data-status="available">В резерве</button>
        <button class="tab" data-status="on_route">На рейсе</button>
        <button class="tab" data-status="sick">Больничный</button>
    </div>
    <div class="toolbar-right">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" id="searchInput" placeholder="Поиск по ФИО, ИИН, телефону..." oninput="debounceLoad()">
        </div>
    </div>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Водитель</th>
                <th>ИИН</th>
                <th>Телефон</th>
                <th>Роль</th>
                <th>Документы</th>
                <th>Транспорт</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="driversTable">
            <tr><td colspan="8" class="table-empty">Загрузка...</td></tr>
        </tbody>
    </table>
</div>

<div id="driversPagination" class="pagination" style="margin-top:16px"></div>

@endsection

@push('scripts')
<script>
let debounce;
let currentStatus = '';
const colors = ['#2563eb','#16a34a','#d97706','#7c3aed','#dc2626'];

function debounceLoad() {
    clearTimeout(debounce);
    debounce = setTimeout(loadDrivers, 350);
}

function toggleFilters() {
    const p = document.getElementById('filterPanel');
    p.style.display = p.style.display === 'none' ? 'block' : 'none';
}

function resetFilters() {
    document.getElementById('docFilter').value = '';
    document.getElementById('licFilter').value = '';
    loadDrivers();
}

async function loadDrivers(page) {
    const q   = document.getElementById('searchInput').value;
    const doc = document.getElementById('docFilter').value;
    const lic = document.getElementById('licFilter').value;
    let url = `/drivers?page=${page || 1}`;
    if (currentStatus) url += `&status=${currentStatus}`;
    if (q)   url += `&search=${encodeURIComponent(q)}`;
    if (doc) url += `&doc_status=${doc}`;
    if (lic) url += `&license_class=${lic}`;

    const res = await Starget.api('GET', url);
    if (!res) return;

    if (res.stats) {
        Starget.dom.set('kpiTotal',    res.stats.total    || 0);
        Starget.dom.set('kpiOnRoute',  res.stats.on_route || 0);
        Starget.dom.set('kpiExpiring', res.stats.expiring_docs || 0);
        Starget.dom.set('kpiReserve',  res.stats.available || 0);
        if (res.stats.total_vs_prev) {
            document.getElementById('kpiTotalSub').textContent = '+' + res.stats.total_vs_prev + '% vs пред. мес.';
        }
        if (res.stats.load_percent) {
            document.getElementById('kpiOnRouteSub').textContent = res.stats.load_percent + '% загрузка';
        }
    }

    const tbody = document.getElementById('driversTable');
    const items = res.data || [];
    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="table-empty">Водители не найдены</td></tr>';
        return;
    }

    tbody.innerHTML = items.map((d, i) => {
        const name = d.full_name || d.name || '—';
        const initials = Starget.fmt.initials(name);
        const color = colors[i % colors.length];
        const licenses = d.license_classes || [];
        const docStatusHtml = Starget.fmt.docStatus(d.documents || []);

        const vehicle = d.current_vehicle
            ? `<div style="display:flex;align-items:center;gap:4px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .5M13 16h2l3-4.5V9h-5v7z"/></svg>
                <span>${d.current_vehicle.tractor_brand || ''}</span>
                <span class="plate-badge">${d.current_vehicle.tractor_plate || ''}</span>
               </div>`
            : `<span class="text-muted">Не назначена</span>`;

        return `<tr>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="initials-avatar" style="background:${color}">${initials}</div>
                    <div>
                        <div style="font-weight:500">${name}</div>
                        <div style="font-size:10px;color:var(--text-muted)">${licenses.map(l => `<span class="badge badge-secondary" style="font-size:9px;padding:1px 4px">${l}</span>`).join(' ')}</div>
                    </div>
                </div>
            </td>
            <td>
                ${d.is_owner ? '<span class="badge" style="font-size:11px;background:#dcfce7;color:#15803d">Владелец</span>' : '<span class="badge badge-secondary" style="font-size:11px">Водитель</span>'}
            </td>
            <td class="text-mono">${d.iin || '—'}</td>
            <td>${d.phone || '—'}</td>
            <td>${docStatusHtml}</td>
            <td>${vehicle}</td>
            <td>${Starget.fmt.status(d.status || 'available')}</td>
            <td>
                <div class="row-actions">
                    <button class="action-link" onclick="viewDriver(${d.id})">Открыть</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (res.meta) Starget.renderPagination(res.meta, 'driversPagination', loadDrivers);
}

function viewDriver(id) {
    location.href = `/drivers/${id}`;
}

document.getElementById('statusTabs').addEventListener('click', e => {
    const tab = e.target.closest('.tab');
    if (!tab) return;
    document.querySelectorAll('#statusTabs .tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    currentStatus = tab.dataset.status || '';
    loadDrivers();
});

loadDrivers();
</script>
@endpush
