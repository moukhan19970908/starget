@extends('layouts.app')
@section('title', 'Создать перевозку')

@section('header_left')
<div style="display:flex;align-items:center;gap:12px">
    <a href="/transportations" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    </a>
    <div>
        <div class="page-title">Создать перевозку</div>
        <div class="page-subtitle" id="transSubtitle">Назначение транспорта и водителя</div>
    </div>
</div>
@endsection

@section('header_actions')
<button class="btn btn-primary" onclick="submitTransportation()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
    Создать перевозку
</button>
@endsection

@section('content')
<div class="transport-create-layout">
    {{-- LEFT: FORM --}}
    <div>
        {{-- APPLICATION INFO --}}
        <div class="form-section info-block" id="appInfoBlock" style="display:none">
            <div class="form-section-title">Заявка</div>
            <div class="detail-grid" id="appInfoGrid"></div>
        </div>

        {{-- ОСНОВНЫЕ ДАННЫЕ --}}
        <div class="form-section">
            <div class="form-section-title">Основные данные</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Организация (перевозчик)</label>
                    <input type="text" class="form-input" id="org" placeholder="ТОО Starget Logistics">
                </div>
                <div class="form-group">
                    <label class="form-label">Клиентский менеджер</label>
                    <select class="form-select" id="clientManagerId">
                        <option value="">Выберите менеджера...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Логист</label>
                    <select class="form-select" id="logisticManagerId">
                        <option value="">Выберите логиста...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Контракт клиента</label>
                    <select class="form-select" id="clientContractId">
                        <option value="">Выберите контракт...</option>
                    </select>
                </div>
                <div class="toggle-row form-group">
                    <span>НДС перевозки</span>
                    <label class="toggle">
                        <input type="checkbox" id="vatEnabled">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- ТРАНСПОРТ --}}
        <div class="form-section">
            <div class="form-section-title">Транспорт</div>
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label class="form-label">Тип транспорта <span class="required">*</span></label>
                    <select class="form-select" id="vehicleTypeId">
                        <option value="">Выберите тип...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Тягач <span class="required">*</span></label>
                    <div class="autocomplete-wrap">
                        <input type="text" class="form-input" id="tractorSearch" placeholder="Поиск по номеру или марке..." oninput="searchVehicles()">
                        <input type="hidden" id="vehicleId">
                        <div class="autocomplete-dropdown" id="tractorDropdown"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Прицеп</label>
                    <input type="text" class="form-input" id="trailerInfo" placeholder="Заполняется автоматически" readonly>
                </div>
            </div>
        </div>

        {{-- ВОДИТЕЛЬ --}}
        <div class="form-section">
            <div class="form-section-title">Водитель</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Поиск по ИИН</label>
                    <div class="autocomplete-wrap">
                        <input type="text" class="form-input" id="driverIin" placeholder="ИИН водителя" oninput="searchDriverByIin()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Поиск по телефону</label>
                    <div class="autocomplete-wrap">
                        <input type="text" class="form-input" id="driverPhone" placeholder="+7..." oninput="searchDriverByPhone()">
                    </div>
                </div>
            </div>
            <div class="driver-found-card" id="driverFound" style="display:none">
                <div class="driver-avatar-sm" id="driverAvatar"></div>
                <div class="driver-info">
                    <div class="driver-found-name" id="driverName">—</div>
                    <div class="driver-found-iin" id="driverIinDisplay">—</div>
                    <div id="driverDocStatus"></div>
                </div>
                <input type="hidden" id="driverId">
                <button type="button" class="btn-icon-sm" onclick="clearDriver()">✕</button>
            </div>
        </div>

        {{-- ПОСТАВЩИК И СТАВКА --}}
        <div class="form-section">
            <div class="form-section-title">Поставщик и ставка</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Поставщик <span class="required">*</span></label>
                    <select class="form-select" id="supplierId" onchange="onSupplierChange()">
                        <option value="">Выберите поставщика...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Контракт поставщика</label>
                    <select class="form-select" id="supplierContractId" disabled>
                        <option value="">Сначала выберите поставщика</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ставка поставщика <span class="required">*</span></label>
                    <div class="input-group">
                        <input type="number" class="form-input" id="supplierRate" placeholder="0" min="0">
                        <select class="form-select input-group-append" id="supplierCurrency">
                            <option value="KZT">₸</option>
                            <option value="USD">$</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Отсрочка платежа (дни)</label>
                    <input type="number" class="form-input" id="paymentDelay" placeholder="30" min="0">
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: DOCS + INFO --}}
    <div>
        <div class="side-doc-card">
            <div class="side-doc-title">Документы перевозки</div>
            <div class="doc-upload-item">
                <div class="doc-upload-label">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h3M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                    ТТН / CMR накладная
                </div>
                <label class="btn btn-outline btn-sm doc-upload-btn">
                    <input type="file" accept=".pdf,.jpg,.png" onchange="previewDoc(this, 'ttn')">
                    Загрузить
                </label>
                <div class="doc-preview" id="ttnPreview"></div>
            </div>
            <div class="doc-upload-item">
                <div class="doc-upload-label">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h3M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                    Договор-заявка поставщика
                </div>
                <label class="btn btn-outline btn-sm doc-upload-btn">
                    <input type="file" accept=".pdf,.jpg,.png" onchange="previewDoc(this, 'contract')">
                    Загрузить
                </label>
                <div class="doc-preview" id="contractPreview"></div>
            </div>
        </div>

        <div class="side-info-card" id="appSummary" style="display:none;margin-top:16px">
            <div class="side-info-title">Данные рейса</div>
            <div class="side-info-row"><span>Маршрут</span><span id="sumRoute">—</span></div>
            <div class="side-info-row"><span>Груз</span><span id="sumCargo">—</span></div>
            <div class="side-info-row"><span>Ставка клиента</span><span id="sumClientRate">—</span></div>
            <div class="side-info-row"><span>Дата загрузки</span><span id="sumLoadDate">—</span></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const appId = new URLSearchParams(location.search).get('application_id');
let vehicleSearchTimer;

async function init() {
    const [vtypes, suppliers] = await Promise.all([
        Starget.loadVehicleTypes(),
        Starget.api('GET', '/suppliers?status=active&limit=200'),
    ]);
    Starget.fillSelect('vehicleTypeId', vtypes, 'id', 'name', 'Выберите тип...');
    if (suppliers && suppliers.data) {
        Starget.fillSelect('supplierId', suppliers.data, 'id', s => s.company_name || s.name, 'Выберите поставщика...');
    }

    // Load managers
    const users = await Starget.api('GET', '/auth/users?role=client_manager,logistic_manager&limit=100');
    if (users && users.data) {
        const cms = users.data.filter(u => u.role === 'client_manager');
        const lms = users.data.filter(u => u.role === 'logistic_manager');
        Starget.fillSelect('clientManagerId', cms, 'id', 'name', 'Выберите...');
        Starget.fillSelect('logisticManagerId', lms, 'id', 'name', 'Выберите...');
    }

    if (appId) loadApplicationInfo();
}

async function loadApplicationInfo() {
    const res = await Starget.api('GET', `/applications/${appId}`);
    if (!res || !res.success) return;
    const a = res.data;

    document.getElementById('transSubtitle').textContent = `Заявка №${a.id}`;
    document.getElementById('appInfoBlock').style.display = 'block';
    document.getElementById('appInfoGrid').innerHTML = `
        <div class="detail-item"><div class="detail-label">Клиент</div><div class="detail-value">${a.client?.company_name || a.client?.name || '—'}</div></div>
        <div class="detail-item"><div class="detail-label">Маршрут</div><div class="detail-value">${[a.from_city?.name, a.to_city?.name].filter(Boolean).join(' → ') || '—'}</div></div>
    `;

    document.getElementById('appSummary').style.display = 'block';
    Starget.dom.set('sumRoute', [a.from_city?.name, a.to_city?.name].filter(Boolean).join(' → ') || '—');
    Starget.dom.set('sumCargo', a.cargo_name || '—');
    Starget.dom.set('sumClientRate', Starget.fmt.money(a.client_rate, a.currency));
    Starget.dom.set('sumLoadDate', Starget.fmt.date(a.loading_date) || '—');

    if (a.contract_id) {
        document.getElementById('clientContractId').innerHTML = `<option value="${a.contract_id}">${a.contract?.number || 'Контракт ' + a.contract_id}</option>`;
    }
}

function searchVehicles() {
    clearTimeout(vehicleSearchTimer);
    vehicleSearchTimer = setTimeout(async () => {
        const q = document.getElementById('tractorSearch').value;
        if (q.length < 2) { document.getElementById('tractorDropdown').innerHTML = ''; return; }
        const res = await Starget.api('GET', `/vehicles/search?q=${encodeURIComponent(q)}`);
        const items = res?.data || [];
        const dd = document.getElementById('tractorDropdown');
        if (!items.length) { dd.innerHTML = '<div class="ac-item ac-empty">Ничего не найдено</div>'; return; }
        dd.innerHTML = items.map(v => `<div class="ac-item" onclick="selectVehicle(${v.id}, '${v.tractor_brand} ${v.tractor_plate}', '${v.trailer_brand || ''} ${v.trailer_plate || ''}')">
            <strong>${v.tractor_brand}</strong> <span class="plate">${v.tractor_plate}</span>
            ${v.trailer_plate ? `<span style="color:var(--text-muted);font-size:11px;margin-left:4px">+ ${v.trailer_plate}</span>` : ''}
        </div>`).join('');
    }, 300);
}

function selectVehicle(id, tractor, trailer) {
    document.getElementById('vehicleId').value = id;
    document.getElementById('tractorSearch').value = tractor;
    document.getElementById('trailerInfo').value = trailer.trim() || '—';
    document.getElementById('tractorDropdown').innerHTML = '';
}

async function searchDriverByIin() {
    const iin = document.getElementById('driverIin').value;
    if (iin.length < 3) return;
    const res = await Starget.api('GET', `/drivers/search?iin=${encodeURIComponent(iin)}`);
    if (res && res.data) fillDriver(res.data);
}

async function searchDriverByPhone() {
    const phone = document.getElementById('driverPhone').value;
    if (phone.length < 5) return;
    const res = await Starget.api('GET', `/drivers/search?phone=${encodeURIComponent(phone)}`);
    if (res && res.data) fillDriver(res.data);
}

function fillDriver(d) {
    document.getElementById('driverId').value = d.id;
    document.getElementById('driverFound').style.display = 'flex';
    document.getElementById('driverAvatar').textContent = Starget.fmt.initials(d.full_name || d.name || '?');
    Starget.dom.set('driverName', d.full_name || d.name || '—');
    Starget.dom.set('driverIinDisplay', d.iin || '—');
    document.getElementById('driverDocStatus').innerHTML = Starget.fmt.docStatus(d.documents || []);
}

function clearDriver() {
    document.getElementById('driverId').value = '';
    document.getElementById('driverFound').style.display = 'none';
    document.getElementById('driverIin').value = '';
    document.getElementById('driverPhone').value = '';
}

async function onSupplierChange() {
    const supId = document.getElementById('supplierId').value;
    const sel = document.getElementById('supplierContractId');
    sel.disabled = true;
    if (!supId) return;
    const res = await Starget.api('GET', `/contracts?supplier_id=${supId}&status=active&limit=100`);
    sel.disabled = false;
    if (res && res.data && res.data.length) {
        Starget.fillSelect('supplierContractId', res.data, 'id', c => `${c.number}`, 'Выберите контракт...');
    } else {
        sel.innerHTML = '<option value="">Нет контрактов</option>';
    }
}

function previewDoc(input, type) {
    const file = input.files[0];
    if (!file) return;
    const el = document.getElementById(type + 'Preview');
    el.textContent = `✓ ${file.name}`;
    el.style.color = 'var(--success)';
}

async function submitTransportation() {
    const payload = {
        application_id:     appId || null,
        vehicle_id:         document.getElementById('vehicleId').value || null,
        driver_id:          document.getElementById('driverId').value || null,
        supplier_id:        document.getElementById('supplierId').value || null,
        supplier_contract_id: document.getElementById('supplierContractId').value || null,
        client_contract_id: document.getElementById('clientContractId').value || null,
        client_manager_id:  document.getElementById('clientManagerId').value || null,
        logistic_manager_id:document.getElementById('logisticManagerId').value || null,
        vehicle_type_id:    document.getElementById('vehicleTypeId').value || null,
        supplier_rate:      document.getElementById('supplierRate').value || null,
        supplier_currency:  document.getElementById('supplierCurrency').value,
        payment_delay:      document.getElementById('paymentDelay').value || null,
        vat_enabled:        document.getElementById('vatEnabled').checked,
        organization:       document.getElementById('org').value,
    };

    if (!payload.supplier_id) { Starget.toast('Выберите поставщика', 'error'); return; }

    const url = appId ? `/applications/${appId}/transportations` : '/transportations';
    const res = await Starget.api('POST', url, payload);
    if (res && res.success) {
        Starget.toast('Перевозка создана', 'success');
        setTimeout(() => { location.href = '/transportations'; }, 800);
    }
}

init();
</script>
@endpush
