@extends('layouts.app')
@section('title', 'Поставщики')

@section('header_left')
<div>
    <div class="page-title">Справочник: Поставщики</div>
    <div class="page-subtitle">Транспортные компании и ИП-перевозчики</div>
</div>
@endsection

@section('header_actions')
<a href="/suppliers/create" class="btn btn-primary" id="btnCreateSupplier">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
    Создать поставщика
</a>
@endsection

@section('content')

<div class="ops-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
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
            <td class="text-mono">${s.bin_iin || '—'}</td>
            <td>
                <div>${s.contact_name || '—'}</div>
                <div style="font-size:11px;color:var(--text-muted)">${s.phone || ''}</div>
            </td>
            <td><span style="font-weight:600">${s.transportations_count || 0}</span></td>
            <td>${Starget.fmt.status(s.status || 'active')}</td>
            <td><div class="row-actions"><button class="action-link" onclick="viewSupplier(${s.id})">Открыть</button></div></td>
        </tr>`;
    }).join('');

    if (res.meta) Starget.renderPagination(res.meta, 'suppliersPagination', loadSuppliers);
}

// ── SUPPLIER DETAIL PANEL ────────────────────────────────────
async function viewSupplier(id) {
    const panel = document.getElementById('supplierPanel');
    const body  = document.getElementById('supplierPanelBody');
    panel.style.display = 'flex';
    body.innerHTML = '<div style="padding:32px;text-align:center;color:var(--text-muted)">Загрузка...</div>';

    const res = await Starget.api('GET', `/suppliers/${id}`);
    if (!res || !res.success) { panel.style.display = 'none'; return; }

    const s = res.data;
    const typeLabel = s.type === 'legal' ? 'Юридическое лицо' : 'Физическое лицо / ИП';
    const vehicles = (s.vehicles || []).length
        ? (s.vehicles).map(v =>
            `<div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--border-light)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;flex-shrink:0"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .5M13 16h2l3-4.5V9h-5v7z"/></svg>
                <div>
                    <div style="font-size:12px;font-weight:500">${v.tractor_brand || '—'} ${v.tractor_plate ? '<span class="text-mono" style="font-size:11px;color:var(--text-muted)">' + v.tractor_plate + '</span>' : ''}</div>
                    ${v.trailer_brand || v.trailer_plate ? '<div style="font-size:11px;color:var(--text-muted)">Прицеп: ' + (v.trailer_brand || '') + ' ' + (v.trailer_plate || '') + '</div>' : ''}
                </div>
                <div style="margin-left:auto">${Starget.fmt.status(v.status || 'idle')}</div>
            </div>`
        ).join('')
        : '<div style="font-size:12px;color:var(--text-muted)">Транспорт не добавлен</div>';

    const docs = (s.documents || []).map(d =>
        `<div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--border-light)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;flex-shrink:0"><path d="M9 12h6m-6 4h3M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
            <a href="/storage/${d.file_path}" target="_blank" style="font-size:12px;color:var(--primary);text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${d.original_name || d.file_path}</a>
        </div>`
    ).join('') || '<div style="font-size:12px;color:var(--text-muted)">Документы не загружены</div>';

    body.innerHTML = `
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
            <div style="width:44px;height:44px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:var(--primary);flex-shrink:0">
                ${Starget.fmt.initials(s.name || '?')}
            </div>
            <div>
                <div style="font-size:15px;font-weight:700;color:var(--text)">${s.name || '—'}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">${typeLabel}</div>
            </div>
            <div style="margin-left:auto">${Starget.fmt.status(s.status)}</div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            ${field('БИН/ИИН', s.bin_iin)}
            ${field('Контактное лицо', s.contact_name)}
            ${field('Телефон', s.phone)}
            ${field('Email', s.email)}
        </div>

        ${s.comment ? `<div style="margin-bottom:16px">${field('Комментарий', s.comment, true)}</div>` : ''}

        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:8px">Транспорт</div>
        ${vehicles}

        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin:16px 0 8px">Документы</div>
        ${docs}

        <div style="font-size:11px;color:var(--text-xs);margin-top:16px">Создан: ${Starget.fmt.datetime(s.created_at)}</div>`;
}

function field(label, value, full = false) {
    return `<div style="${full ? 'grid-column:1/-1' : ''}">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:4px">${label}</div>
        <div style="font-size:13px;color:var(--text)">${value || '—'}</div>
    </div>`;
}

function closeSupplierPanel() {
    document.getElementById('supplierPanel').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('supplierPanel');
    if (panel) panel.addEventListener('click', e => { if (e.target === panel) closeSupplierPanel(); });
    if (!Starget.auth.can('suppliers.create')) {
        const btn = document.getElementById('btnCreateSupplier');
        if (btn) btn.style.display = 'none';
    }
});

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

{{-- SUPPLIER DETAIL PANEL --}}
<div id="supplierPanel" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:flex-start;justify-content:flex-end">
    <div style="background:var(--surface);width:100%;max-width:440px;height:100%;overflow-y:auto;box-shadow:-8px 0 40px rgba(0,0,0,.15);display:flex;flex-direction:column">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--border-light);flex-shrink:0">
            <div style="font-size:14px;font-weight:700;color:var(--text)">Поставщик</div>
            <button onclick="closeSupplierPanel()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);padding:4px" title="Закрыть">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="supplierPanelBody" style="padding:20px 24px;flex:1"></div>
    </div>
</div>

@endpush
