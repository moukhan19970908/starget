@extends('layouts.app')
@section('title', 'Договор')

@section('header_left')
<div style="display:flex;align-items:center;gap:12px">
    <a href="/contracts" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    </a>
    <div>
        <div class="page-title" id="contractTitle">Договор</div>
        <div class="page-subtitle" id="contractSubtitle">Карточка договора</div>
    </div>
</div>
@endsection

@section('content')

<div id="loadingState" style="padding:60px;text-align:center;color:var(--text-muted)">Загрузка...</div>

<div id="contractLayout" class="driver-create-layout" style="display:none">

    {{-- LEFT --}}
    <div>
        <div class="form-section">
            <div class="form-section-title">Реквизиты договора</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Номер договора</div>
                    <div class="detail-value text-mono" id="cNumber">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Тип</div>
                    <div class="detail-value" id="cType">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Дата подписания</div>
                    <div class="detail-value" id="cSignedDate">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Дата истечения</div>
                    <div class="detail-value" id="cExpiresAt">—</div>
                </div>
                <div class="detail-item form-group-full">
                    <div class="detail-label">Клиент</div>
                    <div class="detail-value" id="cClient">—</div>
                </div>
                <div class="detail-item form-group-full">
                    <div class="detail-label">Поставщик</div>
                    <div class="detail-value" id="cSupplier">—</div>
                </div>
            </div>
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
        </div>

        <div class="side-info-card" id="fileCard" style="margin-top:12px;display:none">
            <div class="side-info-title">Файл договора</div>
            <div style="margin-top:8px">
                <a id="cFileLink" href="#" target="_blank" class="action-link">Скачать файл</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const contractId = location.pathname.split('/').pop();

const typeLabels = { client: 'Клиентский', supplier: 'Поставщиковый' };

async function load() {
    const res = await Starget.api('GET', `/contracts/${contractId}`);
    if (!res || !res.success) {
        document.getElementById('loadingState').textContent = 'Ошибка загрузки данных';
        return;
    }
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('contractLayout').style.display = '';

    const c = res.data;

    document.getElementById('contractTitle').textContent    = c.number || 'Договор';
    document.getElementById('contractSubtitle').textContent = typeLabels[c.type] || c.type || 'Карточка договора';

    document.getElementById('cNumber').textContent     = c.number      || '—';
    document.getElementById('cType').textContent       = typeLabels[c.type] || c.type || '—';
    document.getElementById('cSignedDate').textContent = Starget.fmt.date(c.signed_date) || '—';

    const today    = new Date();
    const expires  = c.expires_at ? new Date(c.expires_at) : null;
    const isExpired  = expires && expires < today;
    const isExpiring = expires && expires <= new Date(today.getTime() + 30 * 86400000);
    document.getElementById('cExpiresAt').innerHTML = expires
        ? `<span class="${isExpired ? 'text-danger' : isExpiring ? 'text-warning' : ''}">${Starget.fmt.date(c.expires_at)}</span>`
        : '—';

    document.getElementById('cClient').textContent   = c.client?.name   || '—';
    document.getElementById('cSupplier').textContent = c.supplier?.name || '—';
    document.getElementById('cStatus').innerHTML     = Starget.fmt.status(c.status || 'active');
    document.getElementById('cCreatedAt').textContent = Starget.fmt.date(c.created_at);

    if (c.file_path) {
        document.getElementById('fileCard').style.display = '';
        document.getElementById('cFileLink').href = `/storage/${c.file_path}`;
    }
}

load();
</script>
@endpush
