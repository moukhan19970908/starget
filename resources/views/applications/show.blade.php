@extends('layouts.app')
@section('title', 'Заявка')

@section('header_left')
<div style="display:flex;align-items:center;gap:12px">
    <a href="/applications" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    </a>
    <div>
        <div class="page-title" id="appTitle">Просмотр заявки</div>
        <div class="page-subtitle" id="appSubtitle">Детальная информация о заявке</div>
    </div>
</div>
@endsection

@section('header_actions')
<button class="header-btn" onclick="window.print()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-6 0h-4v-6h4v6z"/></svg>
    Печать
</button>
<button class="header-btn red" onclick="cancelApp()" id="cancelBtn" style="display:none">Отменить заявку</button>
<a href="#" class="btn btn-primary" id="createTransBtn" style="display:none">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
    Создать перевозку
</a>
@endsection

@section('content')
<div class="app-detail-layout" id="detailLayout" style="display:none">
    {{-- LEFT COLUMN --}}
    <div>
        {{-- ОСНОВНАЯ ИНФОРМАЦИЯ --}}
        <div class="form-section">
            <div class="form-section-title">Основная информация</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Клиент</div>
                    <div class="detail-value" id="dClient">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Контракт</div>
                    <div class="detail-value" id="dContract">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Маршрут</div>
                    <div class="detail-value" id="dRoute">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Дата загрузки</div>
                    <div class="detail-value" id="dLoadDate">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Дата выгрузки</div>
                    <div class="detail-value" id="dUnloadDate">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Грузоотправитель</div>
                    <div class="detail-value" id="dShipper">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Грузополучатель</div>
                    <div class="detail-value" id="dConsignee">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Менеджер</div>
                    <div class="detail-value" id="dManager">—</div>
                </div>
            </div>
            <div id="dNotes" class="detail-notes" style="display:none"></div>
        </div>

        {{-- ПАРАМЕТРЫ ГРУЗА --}}
        <div class="form-section">
            <div class="form-section-title">Параметры груза</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Наименование</div>
                    <div class="detail-value" id="dCargoName">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Тип загрузки</div>
                    <div class="detail-value" id="dLoadType">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Вес</div>
                    <div class="detail-value" id="dWeight">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Объём</div>
                    <div class="detail-value" id="dVolume">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Стоимость груза</div>
                    <div class="detail-value" id="dCargoValue">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Особые условия</div>
                    <div class="detail-value" id="dSpecial">—</div>
                </div>
            </div>
        </div>

        {{-- STOPS --}}
        <div class="form-section" id="stopsSection" style="display:none">
            <div class="form-section-title">Точки маршрута</div>
            <div id="stopsTimeline"></div>
        </div>

        {{-- TRANSPORTATIONS --}}
        <div class="form-section" id="transSection" style="display:none">
            <div class="form-section-title">Перевозки по заявке</div>
            <div id="transList"></div>
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div>
        {{-- ФИНАНСЫ --}}
        <div class="finance-panel" style="margin-bottom:16px">
            <div class="finance-panel-title">Финансовые условия</div>
            <div class="finance-row">
                <span>Ставка клиента</span>
                <strong id="fRate">—</strong>
            </div>
            <div class="finance-row">
                <span>Валюта</span>
                <strong id="fCurrency">—</strong>
            </div>
            <div class="finance-row">
                <span>Курс</span>
                <strong id="fExchange">—</strong>
            </div>
            <div class="finance-row">
                <span>НДС</span>
                <strong id="fVat">—</strong>
            </div>
            <div class="finance-divider"></div>
            <div class="finance-row total">
                <span>Итого</span>
                <strong id="fTotal">—</strong>
            </div>
        </div>

        {{-- СТАТУС --}}
        <div class="side-info-card">
            <div class="side-info-title">Статус заявки</div>
            <div id="dStatus" style="margin:12px 0"></div>
            <div class="side-info-row">
                <span>Создана</span>
                <span id="dCreatedAt">—</span>
            </div>
            <div class="side-info-row">
                <span>Обновлена</span>
                <span id="dUpdatedAt">—</span>
            </div>
        </div>
    </div>
</div>

<div id="loadingState" style="padding:60px;text-align:center;color:var(--text-muted)">Загрузка...</div>
@endsection

@push('scripts')
<script>
const appId = location.pathname.split('/').pop();

async function loadApp() {
    const res = await Starget.api('GET', `/applications/${appId}`);
    if (!res || !res.success) {
        document.getElementById('loadingState').textContent = 'Ошибка загрузки данных';
        return;
    }
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('detailLayout').style.display = '';

    const a = res.data;

    document.getElementById('appTitle').textContent   = `Заявка №${a.id}`;
    document.getElementById('appSubtitle').textContent = `Создана ${Starget.fmt.date(a.created_at)}`;

    document.getElementById('dClient').innerHTML   = a.client ? `<strong>${a.client.company_name || a.client.name}</strong>` : '—';
    document.getElementById('dContract').textContent = a.contract?.number || '—';
    document.getElementById('dRoute').textContent  = [a.departure_city, a.destination_city].filter(Boolean).join(' → ') || '—';
    document.getElementById('dLoadDate').textContent   = Starget.fmt.date(a.departure_date) || '—';
    document.getElementById('dUnloadDate').textContent = Starget.fmt.date(a.arrival_date) || '—';
    document.getElementById('dShipper').textContent  = a.shipper   || '—';
    document.getElementById('dConsignee').textContent = a.consignee || '—';
    document.getElementById('dManager').textContent  = a.author ? (a.author.name + (a.author.surname ? ' ' + a.author.surname : '')) : '—';

    if (a.comment) {
        const n = document.getElementById('dNotes');
        n.style.display = 'block';
        n.textContent = a.comment;
    }

    document.getElementById('dCargoName').textContent = a.cargo_name    || '—';
    document.getElementById('dLoadType').textContent  = a.loading_type  || '—';
    document.getElementById('dWeight').textContent    = a.weight  ? `${a.weight} кг` : '—';
    document.getElementById('dVolume').textContent    = a.volume  ? `${a.volume} м³` : '—';
    document.getElementById('dCargoValue').textContent = a.cargo_cost ? Starget.fmt.money(a.cargo_cost, a.cargo_currency) : '—';
    document.getElementById('dSpecial').textContent  = a.special_conditions || '—';

    // Finance
    const rateKzt = a.client_rate_currency === 'KZT' || !a.client_rate_exchange
        ? a.client_rate
        : (a.client_rate * a.client_rate_exchange);
    const vatAmt  = a.client_rate_vat ? rateKzt * 0.12 : 0;
    document.getElementById('fRate').textContent     = Starget.fmt.money(a.client_rate, a.client_rate_currency || 'KZT');
    document.getElementById('fCurrency').textContent = a.client_rate_currency || '—';
    document.getElementById('fExchange').textContent = a.client_rate_exchange ? `1 ${a.client_rate_currency || 'USD'} = ${a.client_rate_exchange} KZT` : '1';
    document.getElementById('fVat').textContent      = a.client_rate_vat ? '12%' : 'Без НДС';
    document.getElementById('fTotal').textContent    = Starget.fmt.money(rateKzt + vatAmt, 'KZT');

    // Status
    document.getElementById('dStatus').innerHTML = Starget.fmt.status(a.status);
    document.getElementById('dCreatedAt').textContent = Starget.fmt.datetime(a.created_at);
    document.getElementById('dUpdatedAt').textContent = Starget.fmt.datetime(a.updated_at);

    // Buttons
    if (['new', 'in_progress', 'open'].includes(a.status)) {
        document.getElementById('cancelBtn').style.display = '';
        document.getElementById('createTransBtn').style.display = '';
        document.getElementById('createTransBtn').href = `/transportations/create?application_id=${a.id}`;
    }

    // Stops
    if (a.stops && a.stops.length) {
        document.getElementById('stopsSection').style.display = '';
        document.getElementById('stopsTimeline').innerHTML = `<div class="route-timeline">` +
            a.stops.map((s, i) => `<div class="timeline-stop">
                <div class="timeline-dot ${i === 0 ? 'green' : i === a.stops.length - 1 ? 'red' : 'blue'}"></div>
                <div><div class="timeline-label">${s.stop_type || '—'}</div><div class="timeline-addr">${s.address}</div></div>
            </div>`).join('<div class="timeline-line"></div>') +
        `</div>`;
    }

    // Transportations
    if (a.transportations && a.transportations.length) {
        document.getElementById('transSection').style.display = '';
        document.getElementById('transList').innerHTML = a.transportations.map(t =>
            `<div class="trans-row">
                <span>${Starget.fmt.status(t.status)}</span>
                <span>${t.vehicle?.tractor_brand || '—'} ${t.vehicle?.tractor_plate || ''}</span>
                <span>${t.driver?.full_name || '—'}</span>
                <a href="/transportations#${t.id}" class="action-link">Открыть</a>
            </div>`
        ).join('');
    }
}

async function cancelApp() {
    if (!confirm('Отменить заявку?')) return;
    const res = await Starget.api('PUT', `/applications/${appId}`, { status: 'cancelled' });
    if (res && res.success) {
        Starget.toast('Заявка отменена', 'success');
        loadApp();
    }
}

loadApp();
</script>
@endpush
