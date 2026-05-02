@extends('layouts.app')
@section('title', 'Транспорт')

@section('header_left')
<div style="display:flex;align-items:center;gap:12px">
    <a href="/vehicles" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    </a>
    <div>
        <div class="page-title" id="vTitle">Транспорт</div>
        <div class="page-subtitle" id="vSubtitle">Карточка транспортного средства</div>
    </div>
</div>
@endsection

@section('content')

<div id="loadingState" style="padding:60px;text-align:center;color:var(--text-muted)">Загрузка...</div>

<div id="vehicleLayout" class="driver-create-layout" style="display:none">

    {{-- LEFT --}}
    <div>
        {{-- ТЯГАЧ --}}
        <div class="form-section">
            <div class="form-section-title">Тягач</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Марка / Модель</div>
                    <div class="detail-value" id="vTractorBrand">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Гос. номер</div>
                    <div class="detail-value text-mono" id="vTractorPlate">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Год выпуска</div>
                    <div class="detail-value" id="vTractorYear">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Тип кузова</div>
                    <div class="detail-value" id="vType">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Грузоподъёмность, т</div>
                    <div class="detail-value" id="vTonnage">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Объём, м³</div>
                    <div class="detail-value" id="vVolume">—</div>
                </div>
                <div id="tempRow" class="detail-item form-group-full" style="display:none">
                    <div class="detail-label">Температурный режим</div>
                    <div class="detail-value" id="vTemp">—</div>
                </div>
            </div>
        </div>

        {{-- ПРИЦЕП --}}
        <div class="form-section">
            <div class="form-section-title">Прицеп</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Марка / Модель</div>
                    <div class="detail-value" id="vTrailerBrand">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Гос. номер</div>
                    <div class="detail-value text-mono" id="vTrailerPlate">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Год выпуска</div>
                    <div class="detail-value" id="vTrailerYear">—</div>
                </div>
            </div>
        </div>

        {{-- ВОДИТЕЛИ --}}
        <div class="form-section">
            <div class="form-section-title">Водители</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>ИИН</th>
                    </tr>
                </thead>
                <tbody id="driversTable">
                    <tr><td colspan="2" class="table-empty">Загрузка...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- RIGHT --}}
    <div>
        <div class="side-info-card">
            <div class="side-info-title">Статус</div>
            <div id="vStatus" style="margin:10px 0">—</div>
            <div class="side-info-row">
                <span>Добавлен</span>
                <span id="vCreatedAt">—</span>
            </div>
        </div>

        <div class="side-info-card" style="margin-top:12px">
            <div class="side-info-title">Владелец</div>
            <div style="margin-top:8px">
                <div class="detail-value" id="vOwner">—</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:4px" id="vOwnerType"></div>
            </div>
        </div>

        <div class="side-info-card" id="suppliersCard" style="margin-top:12px;display:none">
            <div class="side-info-title">Поставщики</div>
            <div id="vSuppliers" style="margin-top:8px"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const vehicleId = location.pathname.split('/').pop();

const ownerTypeLabels = { legal: 'Юридическое лицо', individual: 'Физическое лицо' };
const statusLabels    = { active: 'Активен', inactive: 'Неактивен' };

async function load() {
    const res = await Starget.api('GET', `/vehicles/${vehicleId}`);
    if (!res || !res.success) {
        document.getElementById('loadingState').textContent = 'Ошибка загрузки данных';
        return;
    }
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('vehicleLayout').style.display = '';

    const v = res.data;

    document.getElementById('vTitle').textContent    = (v.tractor_brand || '') + ' ' + (v.tractor_plate || '');
    document.getElementById('vSubtitle').textContent = v.vehicle_type?.name || 'Транспортное средство';

    // Тягач
    document.getElementById('vTractorBrand').textContent = v.tractor_brand || '—';
    document.getElementById('vTractorPlate').textContent = v.tractor_plate || '—';
    document.getElementById('vTractorYear').textContent  = v.tractor_year  || '—';
    document.getElementById('vType').textContent         = v.vehicle_type?.name || '—';
    document.getElementById('vTonnage').textContent      = v.tonnage ? v.tonnage + ' т' : '—';
    document.getElementById('vVolume').textContent       = v.volume  ? v.volume  + ' м³' : '—';

    if (v.temperature_min != null && v.temperature_max != null) {
        document.getElementById('tempRow').style.display = '';
        document.getElementById('vTemp').textContent = `от ${v.temperature_min}°C до ${v.temperature_max}°C`;
    }

    // Прицеп
    document.getElementById('vTrailerBrand').textContent = v.trailer_brand || '—';
    document.getElementById('vTrailerPlate').textContent = v.trailer_plate || '—';
    document.getElementById('vTrailerYear').textContent  = v.trailer_year  || '—';

    // Статус
    document.getElementById('vStatus').innerHTML     = Starget.fmt.status(v.status || 'active');
    document.getElementById('vCreatedAt').textContent = Starget.fmt.date(v.created_at);

    // Владелец
    document.getElementById('vOwner').textContent     = v.owner?.full_name || '—';
    document.getElementById('vOwnerType').textContent = ownerTypeLabels[v.owner?.type] || '';

    // Водители
    const tbody = document.getElementById('driversTable');
    if (v.drivers && v.drivers.length) {
        tbody.innerHTML = v.drivers.map(d =>
            `<tr>
                <td>${d.full_name || '—'}</td>
                <td class="text-mono">${d.iin || '—'}</td>
            </tr>`
        ).join('');
    } else {
        tbody.innerHTML = '<tr><td colspan="2" class="table-empty">Водители не назначены</td></tr>';
    }

    // Поставщики
    if (v.suppliers && v.suppliers.length) {
        document.getElementById('suppliersCard').style.display = '';
        document.getElementById('vSuppliers').innerHTML = v.suppliers.map(s =>
            `<div class="side-info-row"><span>${s.name}</span></div>`
        ).join('');
    }
}

load();
</script>
@endpush
