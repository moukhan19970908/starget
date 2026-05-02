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
@if(true)
<button class="btn btn-primary" id="btnCreateContract" onclick="createContract()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
    Создать договор
</button>
@endif
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
        const isExpiring = c.expires_at && (new Date(c.expires_at) - now) < 30 * 86400000 && c.status === 'active';
        const endDateHtml = c.expires_at
            ? `<span style="color:${isExpiring ? 'var(--danger)' : 'inherit'}">${Starget.fmt.date(c.expires_at)}${isExpiring ? ' ⚠' : ''}</span>`
            : '—';
        const party = c.client?.company_name || c.client?.name || c.supplier?.company_name || c.supplier?.name || '—';
        return `<tr>
            <td><strong>${c.number || '—'}</strong></td>
            <td>${party}</td>
            <td>${c.type === 'client' ? '<span class="badge badge-primary">Клиентский</span>' : '<span class="badge badge-secondary">Поставщик</span>'}</td>
            <td>${Starget.fmt.date(c.signed_date) || '—'}</td>
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
    location.href = `/contracts/${id}`;
}

// ── CREATE CONTRACT MODAL ─────────────────────────────────────
let _clientsLoaded = false;
let _suppliersLoaded = false;

async function createContract() {
    document.getElementById('createContractModal').style.display = 'flex';
    document.getElementById('createContractForm').reset();
    document.getElementById('cc_clientRow').style.display = 'none';
    document.getElementById('cc_supplierRow').style.display = 'none';
    document.getElementById('cc_fileNameLabel').textContent = 'Файл не выбран';

    if (!_clientsLoaded) {
        const res = await Starget.api('GET', '/clients?limit=200');
        if (res && res.data) {
            Starget.fillSelect('cc_client_id', res.data, 'id', i => i.company_name || i.name, 'Выберите клиента...');
            _clientsLoaded = true;
        }
    }
    if (!_suppliersLoaded) {
        const res = await Starget.api('GET', '/suppliers?limit=200');
        if (res && res.data) {
            Starget.fillSelect('cc_supplier_id', res.data, 'id', i => i.company_name || i.name, 'Выберите поставщика...');
            _suppliersLoaded = true;
        }
    }
}

function closeCreateContractModal() {
    document.getElementById('createContractModal').style.display = 'none';
}

function onContractTypeChange() {
    const type = document.getElementById('cc_type').value;
    document.getElementById('cc_clientRow').style.display   = type === 'client'   ? '' : 'none';
    document.getElementById('cc_supplierRow').style.display = type === 'supplier' ? '' : 'none';
}

async function submitCreateContract() {
    const number = document.getElementById('cc_number').value.trim();
    const type   = document.getElementById('cc_type').value;

    const signedDate = document.getElementById('cc_signed_date').value;
    const expiresAt  = document.getElementById('cc_expires_at').value;
    const status     = document.getElementById('cc_status').value;
    const fileInput  = document.getElementById('cc_file');

    if (!number)              { Starget.toast('Введите номер договора', 'error'); return; }
    if (!type)                { Starget.toast('Выберите тип договора', 'error'); return; }
    if (!signedDate)          { Starget.toast('Укажите дату подписания', 'error'); return; }
    if (!expiresAt)           { Starget.toast('Укажите дату истечения', 'error'); return; }
    if (!status)              { Starget.toast('Выберите статус', 'error'); return; }
    if (!fileInput.files[0])  { Starget.toast('Прикрепите файл договора', 'error'); return; }

    const fd = new FormData();
    fd.append('number',      number);
    fd.append('type',        type);
    fd.append('signed_date', signedDate);
    fd.append('expires_at',  expiresAt);
    fd.append('status',      status);
    fd.append('file',        fileInput.files[0]);

    const clientId   = document.getElementById('cc_client_id').value;
    const supplierId = document.getElementById('cc_supplier_id').value;
    if (type === 'client'   && clientId)   fd.append('client_id',   clientId);
    if (type === 'supplier' && supplierId) fd.append('supplier_id', supplierId);

    const btn = document.getElementById('cc_submitBtn');
    btn.disabled = true;
    btn.textContent = 'Сохранение...';

    const res = await Starget.api('POST', '/contracts', fd, true);

    btn.disabled = false;
    btn.textContent = 'Создать договор';

    if (res && res.success) {
        Starget.toast('Договор создан', 'success');
        closeCreateContractModal();
        loadContracts();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('createContractModal');
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) closeCreateContractModal(); });
    if (!Starget.auth.can('contracts.create')) {
        const btn = document.getElementById('btnCreateContract');
        if (btn) btn.style.display = 'none';
    }
});

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

{{-- CREATE CONTRACT MODAL --}}
<div id="createContractModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--surface);border-radius:var(--radius-lg);border:1px solid var(--border);width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25)">
        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--border-light)">
            <div>
                <div style="font-size:15px;font-weight:700;color:var(--text)">Новый договор</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Заполните реквизиты договора</div>
            </div>
            <button onclick="closeCreateContractModal()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);padding:4px" title="Закрыть">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Form --}}
        <form id="createContractForm" onsubmit="event.preventDefault();submitCreateContract()" style="padding:20px 24px">
            <div class="form-grid" style="margin-bottom:14px">
                <div class="form-group">
                    <label class="form-label">Номер договора <span style="color:var(--danger)">*</span></label>
                    <input type="text" class="form-input" id="cc_number" placeholder="ДГ-2026-001">
                </div>
                <div class="form-group">
                    <label class="form-label">Тип договора <span style="color:var(--danger)">*</span></label>
                    <select class="form-select" id="cc_type" onchange="onContractTypeChange()">
                        <option value="">Выберите тип...</option>
                        <option value="client">Клиентский</option>
                        <option value="supplier">Поставщиковый</option>
                    </select>
                </div>

                <div class="form-group full" id="cc_clientRow" style="display:none">
                    <label class="form-label">Клиент</label>
                    <select class="form-select" id="cc_client_id">
                        <option value="">Выберите клиента...</option>
                    </select>
                </div>

                <div class="form-group full" id="cc_supplierRow" style="display:none">
                    <label class="form-label">Поставщик</label>
                    <select class="form-select" id="cc_supplier_id">
                        <option value="">Выберите поставщика...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Дата подписания <span style="color:var(--danger)">*</span></label>
                    <input type="date" class="form-input" id="cc_signed_date">
                </div>
                <div class="form-group">
                    <label class="form-label">Дата истечения <span style="color:var(--danger)">*</span></label>
                    <input type="date" class="form-input" id="cc_expires_at">
                </div>
                <div class="form-group">
                    <label class="form-label">Статус <span style="color:var(--danger)">*</span></label>
                    <select class="form-select" id="cc_status">
                        <option value="active">Активен</option>
                        <option value="draft">Черновик</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label class="form-label">Файл договора (PDF, DOC, DOCX) <span style="color:var(--danger)">*</span></label>
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                        <span class="btn btn-outline" style="font-size:12px;padding:7px 14px;white-space:nowrap" onclick="document.getElementById('cc_file').click()">Выбрать файл</span>
                        <span id="cc_fileNameLabel" style="font-size:12px;color:var(--text-muted)">Файл не выбран</span>
                    </label>
                    <input type="file" id="cc_file" accept=".pdf,.doc,.docx" style="display:none" onchange="document.getElementById('cc_fileNameLabel').textContent = this.files[0]?.name || 'Файл не выбран'">
                </div>
            </div>

            {{-- Footer --}}
            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--border-light)">
                <button type="button" class="btn btn-outline" onclick="closeCreateContractModal()">Отмена</button>
                <button type="submit" class="btn btn-primary" id="cc_submitBtn">Создать договор</button>
            </div>
        </form>
    </div>
</div>

@endpush
