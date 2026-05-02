@extends('layouts.app')
@section('title', 'Клиент')

@section('header_left')
<div style="display:flex;align-items:center;gap:12px">
    <a href="/clients" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    </a>
    <div>
        <div class="page-title" id="clientTitle">Клиент</div>
        <div class="page-subtitle" id="clientSubtitle">Карточка клиента</div>
    </div>
</div>
@endsection

@section('content')

<div id="loadingState" style="padding:60px;text-align:center;color:var(--text-muted)">Загрузка...</div>

<div id="clientLayout" class="driver-create-layout" style="display:none">

    {{-- LEFT --}}
    <div>
        <div class="form-section">
            <div class="form-section-title">Реквизиты</div>
            <div class="detail-grid">
                <div class="detail-item form-group-full">
                    <div class="detail-label">Название компании</div>
                    <div class="detail-value" id="cName">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">БИН / ИИН</div>
                    <div class="detail-value text-mono" id="cBinIin">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Тип клиента</div>
                    <div class="detail-value" id="cType">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Контактное лицо</div>
                    <div class="detail-value" id="cContactName">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Телефон</div>
                    <div class="detail-value" id="cPhone">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value" id="cEmail">—</div>
                </div>
                <div class="detail-item form-group-full">
                    <div class="detail-label">Юридический адрес</div>
                    <div class="detail-value" id="cLegalAddress">—</div>
                </div>
                <div class="detail-item form-group-full">
                    <div class="detail-label">Фактический адрес</div>
                    <div class="detail-value" id="cActualAddress">—</div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">Договоры</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Номер</th>
                        <th>Тип</th>
                        <th>Дата подписания</th>
                        <th>Действует до</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody id="contractsTable">
                    <tr><td colspan="5" class="table-empty">Загрузка...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- RIGHT --}}
    <div>
        <div class="side-info-card">
            <div class="side-info-title">Статус</div>
            <div id="cStatus" style="margin:10px 0">—</div>
            <div class="side-info-row">
                <span>Добавлен</span>
                <span id="cCreatedAt">—</span>
            </div>
            <div class="side-info-row">
                <span>Активных договоров</span>
                <span id="cActiveContracts">—</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const clientId = location.pathname.split('/').pop();

const typeLabels = { corporate: 'Корпоративный', supplier: 'Поставщик', archive: 'Архив' };

async function load() {
    const res = await Starget.api('GET', `/clients/${clientId}`);
    if (!res || !res.success) {
        document.getElementById('loadingState').textContent = 'Ошибка загрузки данных';
        return;
    }
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('clientLayout').style.display = '';

    const c = res.data;

    document.getElementById('clientTitle').textContent    = c.name || 'Клиент';
    document.getElementById('clientSubtitle').textContent = 'БИН/ИИН: ' + (c.bin_iin || '—');

    document.getElementById('cName').textContent          = c.name           || '—';
    document.getElementById('cBinIin').textContent        = c.bin_iin        || '—';
    document.getElementById('cType').textContent          = typeLabels[c.type] || c.type || '—';
    document.getElementById('cContactName').textContent   = c.contact_name   || '—';
    document.getElementById('cPhone').textContent         = c.phone          || '—';
    document.getElementById('cEmail').textContent         = c.email          || '—';
    document.getElementById('cLegalAddress').textContent  = c.legal_address  || '—';
    document.getElementById('cActualAddress').textContent = c.actual_address || '—';
    document.getElementById('cStatus').innerHTML          = Starget.fmt.status(c.status || 'active');
    document.getElementById('cCreatedAt').textContent     = Starget.fmt.date(c.created_at);

    loadContracts();
}

async function loadContracts() {
    const res = await Starget.api('GET', `/clients/${clientId}/contracts`);
    const tbody = document.getElementById('contractsTable');
    if (!res || !res.data || !res.data.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="table-empty">Договоры не найдены</td></tr>';
        document.getElementById('cActiveContracts').textContent = '0';
        return;
    }
    const contracts = res.data;
    const active = contracts.filter(c => c.status === 'active').length;
    document.getElementById('cActiveContracts').textContent = active;

    tbody.innerHTML = contracts.map(ct => {
        const today   = new Date();
        const expires = ct.expires_at ? new Date(ct.expires_at) : null;
        const isExpired  = expires && expires < today;
        const isExpiring = expires && expires <= new Date(today.getTime() + 30 * 86400000);
        const expiresHtml = expires
            ? `<span class="${isExpired ? 'text-danger' : isExpiring ? 'text-warning' : ''}">${Starget.fmt.date(ct.expires_at)}</span>`
            : '—';
        return `<tr>
            <td class="text-mono">${ct.number || '—'}</td>
            <td>${ct.type || '—'}</td>
            <td>${Starget.fmt.date(ct.signed_date)}</td>
            <td>${expiresHtml}</td>
            <td>${Starget.fmt.status(ct.status || 'active')}</td>
        </tr>`;
    }).join('');
}

load();
</script>
@endpush
