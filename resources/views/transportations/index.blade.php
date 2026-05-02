@extends('layouts.app')
@section('title', 'Перевозки')

@section('header_left')
<div>
    <div class="page-title">Перевозки</div>
    <div class="page-subtitle">Управление активными и завершёнными перевозками</div>
</div>
@endsection



@section('content')
<div class="split-layout">
    {{-- LEFT: LIST --}}
    <div class="split-left">
        <div class="split-header">
            <div>
                <div class="split-title">В ПЕРЕВОЗКЕ</div>
                <div class="split-count badge badge-primary" id="transCount">0</div>
            </div>
            <div class="tabs tabs-sm" id="statusTabs">
                <button class="tab active" data-status="in_transit">Активные</button>
                <button class="tab" data-status="completed">Завершённые</button>
                <button class="tab" data-status="cancelled">Отказано</button>
            </div>
        </div>

        <div class="search-box" style="margin:12px 0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" id="searchInput" placeholder="Поиск по маршруту, транспорту..." oninput="debounceLoad()">
        </div>

        <div class="trans-list" id="transList">
            <div style="padding:30px;text-align:center;color:var(--text-muted);font-size:13px">Загрузка...</div>
        </div>
    </div>

    {{-- RIGHT: DETAIL --}}
    <div class="split-right" id="detailPanel">
        <div class="detail-empty" id="detailEmpty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;color:var(--text-muted)"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .5M13 16h2l3-4.5V9h-5v7z"/></svg>
            <div style="margin-top:12px;color:var(--text-muted);font-size:14px">Выберите перевозку</div>
        </div>

        <div id="detailContent" style="display:none">
            <div class="detail-panel-header">
                <div>
                    <div class="detail-panel-title" id="dpTitle">—</div>
                    <div id="dpStatus"></div>
                </div>
                <div class="detail-panel-actions">
                    <button class="btn btn-outline btn-sm" onclick="uploadPhotoClick()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        Фото
                    </button>
                    <button class="btn btn-primary btn-sm" id="completeBtn" onclick="completeTransportation()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                        Завершить
                    </button>
                </div>
            </div>

            <div class="detail-sections">
                <div class="detail-section">
                    <div class="detail-section-label">ИНФОРМАЦИЯ О ТРАНСПОРТЕ</div>
                    <div class="vehicle-info-row">
                        <div class="vehicle-type-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .5M13 16h2l3-4.5V9h-5v7z"/></svg>
                        </div>
                        <div>
                            <div class="vehicle-name" id="dpTractor">—</div>
                            <div class="vehicle-plate" id="dpTractorPlate">—</div>
                        </div>
                    </div>
                    <div class="vehicle-info-row trailer">
                        <div class="vehicle-type-icon small">Пр</div>
                        <div>
                            <div class="vehicle-name" id="dpTrailer">—</div>
                            <div class="vehicle-plate" id="dpTrailerPlate">—</div>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-section-label">ПЕРСОНАЛ И СТАВКИ</div>
                    <div class="staff-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m8-10a4 4 0 100-8 4 4 0 000 8z"/></svg>
                        <div>
                            <div class="staff-name" id="dpDriver">—</div>
                            <div class="staff-role">Водитель</div>
                        </div>
                        <div class="staff-badge" id="dpDriverDocs"></div>
                    </div>
                    <div class="staff-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0H5m14 0v-2H5v2"/></svg>
                        <div>
                            <div class="staff-name" id="dpSupplier">—</div>
                            <div class="staff-role">Поставщик</div>
                        </div>
                    </div>
                    <div class="finance-row" style="margin-top:8px">
                        <span>Ставка поставщика</span>
                        <strong id="dpSupRate">—</strong>
                    </div>
                    <div class="finance-row">
                        <span>Ставка клиента</span>
                        <strong id="dpClientRate">—</strong>
                    </div>
                    <div class="finance-row total">
                        <span>Маржа</span>
                        <strong id="dpMargin">—</strong>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-section-label">МАРШРУТ</div>
                    <div id="dpRoute" class="route-mini">—</div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="file" id="photoInput" accept="image/*" style="display:none" onchange="uploadPhoto()">
@endsection

@push('scripts')
<script>
let selectedId = null;
let currentStatus = 'in_transit';
let debounce;

function debounceLoad() {
    clearTimeout(debounce);
    debounce = setTimeout(loadList, 350);
}

async function loadList() {
    const q = document.getElementById('searchInput').value;
    let url = `/transportations?status=${currentStatus}`;
    if (q) url += `&search=${encodeURIComponent(q)}`;
    const res = await Starget.api('GET', url);
    if (!res) return;

    const items = res.data || [];
    document.getElementById('transCount').textContent = res.meta?.total || items.length;

    const listEl = document.getElementById('transList');
    if (!items.length) {
        listEl.innerHTML = '<div style="padding:30px;text-align:center;color:var(--text-muted);font-size:13px">Нет перевозок</div>';
        return;
    }

    listEl.innerHTML = items.map(t => {
        const route = [t.route?.from, t.route?.to].filter(Boolean).join(' → ') || '—';
        const isSelected = t.id === selectedId;
        return `<div class="trans-card ${isSelected ? 'active' : ''}" onclick="selectTrans(${t.id})">
            <div class="trans-card-top">
                <div class="trans-card-route">${route}</div>
                ${Starget.fmt.status(t.status)}
            </div>
            <div class="trans-card-info">
                <span class="trans-vehicle">${t.vehicle?.tractor_brand || '—'} ${t.vehicle?.tractor_plate || ''}</span>
                <span class="trans-driver">${t.driver?.full_name || '—'}</span>
            </div>
            <div class="trans-card-date">${Starget.fmt.date(t.created_at)}</div>
        </div>`;
    }).join('');
}

async function selectTrans(id) {
    selectedId = id;
    document.querySelectorAll('.trans-card').forEach(c => c.classList.remove('active'));
    document.querySelector(`.trans-card[onclick="selectTrans(${id})"]`)?.classList.add('active');

    document.getElementById('detailEmpty').style.display = 'none';
    document.getElementById('detailContent').style.display = 'block';

    const res = await Starget.api('GET', `/transportations/${id}`);
    if (!res || !res.success) return;
    const t = res.data;

    Starget.dom.set('dpTitle', `Перевозка #${t.id}`);
    document.getElementById('dpStatus').innerHTML = Starget.fmt.status(t.status);
    Starget.dom.set('dpTractor',      t.vehicle?.tractor_brand || '—');
    Starget.dom.set('dpTractorPlate', t.vehicle?.tractor_plate || '—');
    Starget.dom.set('dpTrailer',      t.vehicle?.trailer_brand || 'Прицеп не указан');
    Starget.dom.set('dpTrailerPlate', t.vehicle?.trailer_plate || '—');
    Starget.dom.set('dpDriver',   t.driver?.full_name || '—');
    Starget.dom.set('dpSupplier', t.supplier?.company_name || t.supplier?.name || '—');
    Starget.dom.set('dpSupRate',    Starget.fmt.money(t.supplier_rate, t.supplier_currency || 'KZT'));
    Starget.dom.set('dpClientRate', Starget.fmt.money(t.application?.client_rate, t.application?.currency));

    const margin = (t.application?.total_cost || 0) - (t.supplier_rate_kzt || 0);
    Starget.dom.set('dpMargin', Starget.fmt.money(margin, 'KZT'));

    const route = [t.application?.from_city?.name, t.application?.to_city?.name].filter(Boolean).join(' → ') || '—';
    Starget.dom.set('dpRoute', route);

    const docs = t.driver?.documents || [];
    document.getElementById('dpDriverDocs').innerHTML = Starget.fmt.docStatus(docs);

    const isActive = t.status === 'in_transit';
    document.getElementById('completeBtn').style.display = isActive ? '' : 'none';
}

async function completeTransportation() {
    if (!selectedId || !confirm('Завершить перевозку?')) return;
    const res = await Starget.api('POST', `/transportations/${selectedId}/complete`);
    if (res && res.success) {
        Starget.toast('Перевозка завершена', 'success');
        selectedId = null;
        document.getElementById('detailEmpty').style.display = '';
        document.getElementById('detailContent').style.display = 'none';
        loadList();
    }
}

function uploadPhotoClick() {
    document.getElementById('photoInput').click();
}

async function uploadPhoto() {
    if (!selectedId) return;
    const file = document.getElementById('photoInput').files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('photo', file);
    const res = await Starget.api('POST', `/transportations/${selectedId}/call-photo`, fd, true);
    if (res && res.success) Starget.toast('Фото загружено', 'success');
    document.getElementById('photoInput').value = '';
}

// Tab switching
document.getElementById('statusTabs').addEventListener('click', e => {
    const tab = e.target.closest('.tab');
    if (!tab) return;
    document.querySelectorAll('#statusTabs .tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    currentStatus = tab.dataset.status;
    selectedId = null;
    document.getElementById('detailEmpty').style.display = '';
    document.getElementById('detailContent').style.display = 'none';
    loadList();
});

// Auto-select from hash
if (location.hash) {
    const id = parseInt(location.hash.slice(1));
    if (id) setTimeout(() => selectTrans(id), 500);
}

loadList();
</script>
@endpush
