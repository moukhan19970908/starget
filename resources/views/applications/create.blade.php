@extends('layouts.app')
@section('title', 'Создать заявку')

@section('header_left')
<div style="display:flex;align-items:center;gap:12px">
    <a href="/applications" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    </a>
    <div>
        <div class="page-title">Новая заявка</div>
        <div class="page-subtitle">Заполните данные транспортной заявки</div>
    </div>
</div>
@endsection

@section('header_actions')
<button class="header-btn" onclick="saveDraft()">Сохранить как черновик</button>
@endsection

@section('content')
<div class="create-layout">
    {{-- LEFT: FORM --}}
    <div>
        {{-- ОСНОВНЫЕ РЕКВИЗИТЫ --}}
        <div class="form-section">
            <div class="form-section-title">Основные реквизиты</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Клиент <span class="required">*</span></label>
                    <select class="form-select" id="clientId" onchange="onClientChange()">
                        <option value="">Выберите клиента...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Контракт <span class="required">*</span></label>
                    <select class="form-select" id="contractId" disabled>
                        <option value="">Сначала выберите клиента</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Город отправки <span class="required">*</span></label>
                    <select class="form-select" id="fromCityId">
                        <option value="">Выберите город...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Город назначения <span class="required">*</span></label>
                    <select class="form-select" id="toCityId">
                        <option value="">Выберите город...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Грузоотправитель <span class="required">*</span></label>
                    <input type="text" class="form-input" id="shipperName" placeholder="Название компании">
                </div>
                <div class="form-group">
                    <label class="form-label">Грузополучатель <span class="required">*</span></label>
                    <input type="text" class="form-input" id="consigneeName" placeholder="Название компании">
                </div>
                <div class="form-group">
                    <label class="form-label">Контакт отправителя <span class="required">*</span></label>
                    <input type="text" class="form-input" id="shipperContact" placeholder="+7(000)000-00-00" maxlength="16">
                </div>
                <div class="form-group">
                    <label class="form-label">Контакт получателя <span class="required">*</span></label>
                    <input type="text" class="form-input" id="consigneeContact" placeholder="+7(000)000-00-00" maxlength="16">
                </div>
                <div class="form-group">
                    <label class="form-label">Дата загрузки <span class="required">*</span></label>
                    <input type="date" class="form-input" id="loadingDate">
                </div>
                <div class="form-group">
                    <label class="form-label">Дата выгрузки <span class="required">*</span></label>
                    <input type="date" class="form-input" id="unloadingDate">
                </div>
                <div class="form-group form-group-full">
                    <label class="form-label">Полный адрес загрузки <span class="required">*</span></label>
                    <input type="text" class="form-input" id="loadingAddress" placeholder="ул. Примерная, д. 1">
                </div>
                <div class="form-group form-group-full">
                    <label class="form-label">Полный адрес выгрузки <span class="required">*</span></label>
                    <input type="text" class="form-input" id="unloadingAddress" placeholder="ул. Примерная, д. 1">
                </div>
            </div>
        </div>

        {{-- ПАРАМЕТРЫ ГРУЗА --}}
        <div class="form-section">
            <div class="form-section-title">Параметры груза</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Наименование груза <span class="required">*</span></label>
                    <input type="text" class="form-input" id="cargoName" placeholder="Металлопрокат, зерно...">
                </div>
                <div class="form-group">
                    <label class="form-label">Тип загрузки <span class="required">*</span></label>
                    <select class="form-select" id="loadingTypeId">
                        <option value="">Выберите тип...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Вес груза (кг) <span class="required">*</span></label>
                    <input type="number" class="form-input" id="cargoWeight" placeholder="20000" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Объём груза (м³) <span class="required">*</span></label>
                    <input type="number" class="form-input" id="cargoVolume" placeholder="82" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Стоимость груза <span class="required">*</span></label>
                    <input type="number" class="form-input" id="cargoValue" placeholder="0" min="0">
                </div>
                <div class="form-group form-group-full">
                    <label class="form-label">Особые условия перевозки</label>
                    <input type="text" class="form-input" id="specialConditions" placeholder="Температурный режим, хрупкий груз...">
                </div>
            </div>
        </div>

        {{-- МАРШРУТ --}}
        <div class="form-section">
            <div class="form-section-title">Маршрут и точки остановки</div>
            <div id="stopsList"></div>
            <button class="btn btn-outline btn-sm" onclick="addStop()" style="margin-top:8px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
                Добавить точку
            </button>
        </div>

        {{-- КОММЕНТАРИЙ --}}
        <div class="form-section">
            <div class="form-section-title">Комментарий к заявке</div>
            <textarea class="form-input" id="notes" rows="4" placeholder="Дополнительные сведения, особые требования..."></textarea>
        </div>
    </div>

    {{-- RIGHT: FINANCE PANEL --}}
    <div>
        <div class="finance-panel">
            <div class="finance-panel-title">Финансовые условия</div>

            <div class="form-group">
                <label class="form-label">Ставка клиента <span class="required">*</span></label>
                <div class="input-group">
                    <input type="number" class="form-input" id="clientRate" placeholder="0" oninput="recalc()" min="0">
                    <select class="form-select input-group-append" id="currency" onchange="recalc()"></select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Курс валюты</label>
                <input type="number" class="form-input" id="exchangeRate" placeholder="1" oninput="recalc()" min="0">
            </div>

            <div class="toggle-row">
                <span>НДС (12%)</span>
                <label class="toggle">
                    <input type="checkbox" id="vatEnabled" onchange="recalc()">
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="finance-divider"></div>

            <div class="finance-row">
                <span>Ставка (KZT)</span>
                <strong id="calcRateKzt">—</strong>
            </div>
            <div class="finance-row">
                <span>НДС</span>
                <strong id="calcVat">—</strong>
            </div>
            <div class="finance-row total">
                <span>Итого к оплате</span>
                <strong id="calcTotal">—</strong>
            </div>

            <div class="finance-divider"></div>

            <button class="btn btn-primary btn-block" onclick="submitApplication()">
                Создать заявку
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let stopCount = 0;
let stopTypes = [];

async function init() {
    const [cities, loadTypes, stopTypesData, currenciesData] = await Promise.all([
        Starget.loadCities(),
        Starget.loadLoadingTypes(),
        Starget.loadStopTypes(),
        Starget.api('GET', '/dict/currencies'),
    ]);

    stopTypes = stopTypesData || [];

    Starget.fillSelect('fromCityId', cities, 'id', 'name', 'Выберите город...');
    Starget.fillSelect('toCityId', cities, 'id', 'name', 'Выберите город...');
    Starget.fillSelect('loadingTypeId', loadTypes, 'id', 'name', 'Выберите тип...');

    if (currenciesData && currenciesData.data) {
        Starget.fillSelect('currency', currenciesData.data, 'id', 'code', '');
        // Auto-select KZT by default
        const kzt = currenciesData.data.find(c => c.code === 'KZT');
        if (kzt) document.getElementById('currency').value = kzt.id;
        recalc();
    }

    // Clients
    const cr = await Starget.api('GET', '/clients?limit=200');
    if (cr && cr.data) {
        Starget.fillSelect('clientId', cr.data, 'id', item => item.company_name || item.name, 'Выберите клиента...');
    }
}

async function onClientChange() {
    const clientId = document.getElementById('clientId').value;
    const sel = document.getElementById('contractId');
    sel.innerHTML = '<option value="">Загрузка...</option>';
    sel.disabled = true;
    if (!clientId) { sel.innerHTML = '<option value="">Сначала выберите клиента</option>'; return; }
    const res = await Starget.api('GET', `/contracts?client_id=${clientId}&status=active&limit=100`);
    sel.disabled = false;
    if (res && res.data && res.data.length) {
        Starget.fillSelect('contractId', res.data, 'id', item => `${item.number} (до ${Starget.fmt.date(item.end_date)})`, 'Выберите контракт...');
        if (res.data.length === 1) sel.value = res.data[0].id;
    } else {
        sel.innerHTML = '<option value="">Нет активных контрактов</option>';
    }
}

function addStop() {
    stopCount++;
    const div = document.createElement('div');
    div.className = 'stop-row';
    div.id = `stop_${stopCount}`;
    const typeOptions = stopTypes.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
    div.innerHTML = `<div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
        <div class="stop-num">${stopCount}</div>
        <select class="form-select" style="flex:1" id="stopType_${stopCount}">${typeOptions}</select>
        <input type="text" class="form-input" style="flex:2" id="stopAddr_${stopCount}" placeholder="Адрес остановки">
        <button type="button" class="btn-icon-sm red" onclick="this.closest('.stop-row').remove()">✕</button>
    </div>`;
    document.getElementById('stopsList').appendChild(div);
}

function recalc() {
    const rate  = parseFloat(document.getElementById('clientRate').value) || 0;
    const exch  = parseFloat(document.getElementById('exchangeRate').value) || 1;
    const currSel = document.getElementById('currency');
    const currCode = currSel.options[currSel.selectedIndex]?.text || '';
    const vat   = document.getElementById('vatEnabled').checked;

    const inKzt = currCode === 'KZT' ? rate : rate * exch;
    const vatAmt = vat ? inKzt * 0.12 : 0;
    const total  = inKzt + vatAmt;

    Starget.dom.set('calcRateKzt', Starget.fmt.money(inKzt, 'KZT'));
    Starget.dom.set('calcVat',     vat ? Starget.fmt.money(vatAmt, 'KZT') : '—');
    Starget.dom.set('calcTotal',   Starget.fmt.money(total, 'KZT'));
}

function collectStops() {
    const stops = [];
    for (let i = 1; i <= stopCount; i++) {
        const t = document.getElementById(`stopType_${i}`);
        const a = document.getElementById(`stopAddr_${i}`);
        if (t && a && a.value) {
            stops.push({ stop_type_id: t.value, address: a.value, sort_order: stops.length + 1 });
        }
    }
    return stops;
}

async function saveDraft() {
    await doSubmit('draft');
}

async function submitApplication() {
    await doSubmit('new');
}

async function doSubmit(status) {
    const payload = {
        client_id:                document.getElementById('clientId').value,
        contract_id:              document.getElementById('contractId').value || null,
        departure_city_id:        document.getElementById('fromCityId').value,
        destination_city_id:      document.getElementById('toCityId').value,
        shipper:                  document.getElementById('shipperName').value,
        consignee:                document.getElementById('consigneeName').value,
        contact_loading:          document.getElementById('shipperContact').value,
        contact_unloading:        document.getElementById('consigneeContact').value,
        departure_date:           document.getElementById('loadingDate').value,
        arrival_date:             document.getElementById('unloadingDate').value,
        loading_address:          document.getElementById('loadingAddress').value,
        unloading_address:        document.getElementById('unloadingAddress').value,
        cargo_name:               document.getElementById('cargoName').value,
        loading_type_id:          document.getElementById('loadingTypeId').value || null,
        weight:                   document.getElementById('cargoWeight').value || null,
        volume:                   document.getElementById('cargoVolume').value || null,
        cargo_cost:               document.getElementById('cargoValue').value || null,
        special_conditions:       document.getElementById('specialConditions').value,
        client_rate:              document.getElementById('clientRate').value,
        client_rate_currency_id:  document.getElementById('currency').value || null,
        client_rate_exchange:     document.getElementById('exchangeRate').value || 1,
        client_rate_vat:          document.getElementById('vatEnabled').checked,
        comment:                  document.getElementById('notes').value,
        status:                   status,
        stops:                    collectStops(),
    };

    if (!payload.client_id)              { Starget.toast('Выберите клиента', 'error'); return; }
    if (!payload.contract_id)             { Starget.toast('Выберите контракт', 'error'); return; }
    if (!payload.departure_city_id)       { Starget.toast('Укажите город отправки', 'error'); return; }
    if (!payload.destination_city_id)     { Starget.toast('Укажите город назначения', 'error'); return; }
    if (!payload.shipper)                 { Starget.toast('Укажите грузоотправителя', 'error'); return; }
    if (!payload.consignee)               { Starget.toast('Укажите грузополучателя', 'error'); return; }
    if (!payload.contact_loading)         { Starget.toast('Укажите контакт отправителя', 'error'); return; }
    if (!payload.contact_unloading)       { Starget.toast('Укажите контакт получателя', 'error'); return; }
    if (!payload.departure_date)          { Starget.toast('Укажите дату загрузки', 'error'); return; }
    if (!payload.arrival_date)            { Starget.toast('Укажите дату выгрузки', 'error'); return; }
    if (!payload.loading_address)         { Starget.toast('Укажите адрес загрузки', 'error'); return; }
    if (!payload.unloading_address)       { Starget.toast('Укажите адрес выгрузки', 'error'); return; }
    if (!payload.cargo_name)              { Starget.toast('Укажите наименование груза', 'error'); return; }
    if (!payload.loading_type_id)         { Starget.toast('Выберите тип загрузки', 'error'); return; }
    if (!payload.weight)                  { Starget.toast('Укажите вес груза', 'error'); return; }
    if (!payload.volume)                  { Starget.toast('Укажите объём груза', 'error'); return; }
    if (!payload.cargo_cost)              { Starget.toast('Укажите стоимость груза', 'error'); return; }
    if (!payload.client_rate)             { Starget.toast('Укажите ставку клиента', 'error'); return; }

    const res = await Starget.api('POST', '/applications', payload);
    if (res && res.success) {
        Starget.toast('Заявка создана', 'success');
        setTimeout(() => { location.href = '/applications/' + res.data.id; }, 800);
    }
}

function applyPhoneMask(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', function (e) {
        let digits = this.value.replace(/\D/g, '');
        if (digits.startsWith('8')) digits = '7' + digits.slice(1);
        if (!digits.startsWith('7')) digits = '7' + digits;
        digits = digits.slice(0, 11);
        let result = '+7';
        if (digits.length > 1) result += '(' + digits.slice(1, 4);
        if (digits.length >= 4) result += ')';
        if (digits.length > 4)  result += digits.slice(4, 7);
        if (digits.length > 7)  result += '-' + digits.slice(7, 9);
        if (digits.length > 9)  result += '-' + digits.slice(9, 11);
        this.value = result;
    });
    el.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && (this.value === '+7(' || this.value === '+7')) {
            this.value = '';
            e.preventDefault();
        }
    });
    el.addEventListener('focus', function () {
        if (!this.value) this.value = '+7(';
    });
    el.addEventListener('blur', function () {
        if (this.value === '+7(') this.value = '';
    });
}

applyPhoneMask('shipperContact');
applyPhoneMask('consigneeContact');
init();
recalc();
</script>
@endpush
