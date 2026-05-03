@extends('layouts.app')
@section('title', 'Добавить транспорт')

@section('header_left')
<div style="display:flex;align-items:center;gap:12px">
    <a href="/vehicles" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    </a>
    <div>
        <div class="page-title">Добавление нового транспорта</div>
        <div class="page-subtitle">Введите данные тягача, прицепа и водителя</div>
    </div>
</div>
@endsection

@section('header_actions')
<a href="/vehicles" class="header-btn">Отмена</a>
<button class="btn btn-primary" onclick="saveVehicle()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
    Сохранить транспорт
</button>
@endsection

@section('content')
<div class="vehicle-create-layout">
    {{-- LEFT COLUMN --}}
    <div>
        {{-- ВЛАДЕЛЕЦ --}}
        <div class="form-section">
            <div class="form-section-title">Владелец транспорта</div>
            <div class="form-group">
                <label class="form-label">Владелец <span class="required">*</span></label>
                <select class="form-select" id="ownerId">
                    <option value="">Выберите владельца...</option>
                </select>
            </div>
            <div style="margin-top:6px">
                <a href="/owners/create" class="link-sm" target="_blank">+ Создать нового владельца</a>
            </div>
        </div>

        {{-- ПОСТАВЩИК --}}
        <div class="form-section">
            <div class="form-section-title">Поставщик</div>
            <div class="form-group">
                <label class="form-label">Назначить поставщика</label>
                <select class="form-select" id="supplierId">
                    <option value="">Выберите поставщика...</option>
                </select>
            </div>
            <div style="margin-top:6px">
                <a href="/suppliers/create" class="link-sm" target="_blank">+ Создать нового поставщика</a>
            </div>
        </div>

        {{-- ВОДИТЕЛЬ --}}
        <div class="form-section">
            <div class="form-section-title">Водитель</div>
            <div class="form-group">
                <label class="form-label">Назначить водителя</label>
                <select class="form-select" id="driverId">
                    <option value="">Выберите водителя...</option>
                </select>
            </div>
            <div style="margin-top:6px">
                <a href="/drivers/create" class="link-sm" target="_blank">+ Создать нового водителя</a>
            </div>
            <div class="hint-text" style="margin-top:8px">
                Водитель может быть назначен позже при создании перевозки
            </div>
        </div>

        {{-- ОБЩИЕ ПАРАМЕТРЫ --}}
        <div class="form-section">
            <div class="form-section-title">Общие параметры</div>
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label class="form-label">Тип транспорта <span class="required">*</span></label>
                    <select class="form-select" id="vehicleTypeId" onchange="onTypeChange()">
                        <option value="">Выберите тип...</option>
                    </select>
                </div>
                <div class="form-group" id="tempRangeRow" style="display:none">
                    <label class="form-label">Температурный диапазон</label>
                    <div style="display:flex;gap:8px">
                        <input type="number" class="form-input" id="tempMin" placeholder="-25°C">
                        <input type="number" class="form-input" id="tempMax" placeholder="+5°C">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Тоннаж (т)</label>
                    <input type="number" class="form-input" id="tonnage" placeholder="20" min="0" step="0.1">
                </div>
                <div class="form-group">
                    <label class="form-label">Объём (м³)</label>
                    <input type="number" class="form-input" id="volume" placeholder="90" min="0">
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div>
        {{-- ТЯГАЧ --}}
        <div class="form-section">
            <div class="form-section-title">
                <div class="vehicle-icon-sm" style="display:inline-flex;vertical-align:middle;margin-right:6px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .5M13 16h2l3-4.5V9h-5v7z"/></svg>
                </div>
                Тягач
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Марка/Модель <span class="required">*</span></label>
                    <input type="text" class="form-input" id="tractorBrand" placeholder="Volvo FH, Scania R450...">
                </div>
                <div class="form-group">
                    <label class="form-label">Год выпуска</label>
                    <input type="number" class="form-input" id="tractorYear" placeholder="2020" min="1990" max="2030">
                </div>
                <div class="form-group form-group-full">
                    <label class="form-label">Государственный номер <span class="required">*</span></label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="text" class="form-input" id="tractorPlate" placeholder="123 АВС 01" style="text-transform:uppercase">
                        <span class="plate-suffix">KZ</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ПРИЦЕП --}}
        <div class="form-section">
            <div class="form-section-title">Прицеп</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Марка/Модель <span class="required">*</span></label>
                    <input type="text" class="form-input" id="trailerBrand" placeholder="Schmitz, Krone...">
                </div>
                <div class="form-group">
                    <label class="form-label">Год выпуска <span class="required">*</span></label>
                    <input type="number" class="form-input" id="trailerYear" placeholder="2021" min="1990" max="2030">
                </div>
                <div class="form-group form-group-full">
                    <label class="form-label">Государственный номер <span class="required">*</span></label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="text" class="form-input" id="trailerPlate" placeholder="123 АВС 01" style="text-transform:uppercase">
                        <span class="plate-suffix">KZ</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">Документы</div>
            <div class="form-group">
                <label class="form-label">Файлы на транспорт</label>
                <div class="file-drop" id="vehicleDocsDrop" onclick="document.getElementById('vehicleDocs').click()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <div class="file-drop-text">Перетащите или <span>нажмите для выбора</span></div>
                    <div class="file-drop-hint">PDF, JPG, PNG, WEBP до 10 МБ</div>
                    <input type="file" id="vehicleDocs" multiple accept=".pdf,.jpg,.jpeg,.png,.webp" style="display:none" onchange="previewVehicleDocs()">
                </div>
                <div id="vehicleDocsPreview" style="margin-top:6px"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const REFRIGERATOR_KEYWORDS = ['рефрижератор', 'реф', 'refrigerator', 'холод'];

function previewVehicleDocs() {
    const files = document.getElementById('vehicleDocs').files;
    const preview = document.getElementById('vehicleDocsPreview');

    if (!files.length) {
        preview.innerHTML = '';
        return;
    }

    preview.innerHTML = Array.from(files)
        .map(file => `<div class="file-item">${file.name}</div>`)
        .join('');
}

async function init() {
    const [vtypes, owners, drivers, suppliers] = await Promise.all([
        Starget.loadVehicleTypes(),
        Starget.api('GET', '/owners?limit=200'),
        Starget.api('GET', '/drivers?status=active&limit=200'),
        Starget.api('GET', '/suppliers?limit=200'),
    ]);

    Starget.fillSelect('vehicleTypeId', vtypes, 'id', 'name', 'Выберите тип...');
    if (owners && owners.data) {
        Starget.fillSelect('ownerId', owners.data, 'id', o => o.full_name, 'Выберите владельца...');
    }
    if (drivers && drivers.data) {
        Starget.fillSelect('driverId', drivers.data, 'id', d => d.full_name || d.name, 'Выберите водителя...');
    }
    if (suppliers && suppliers.data) {
        Starget.fillSelect('supplierId', suppliers.data, 'id', s => s.name, 'Выберите поставщика...');
    }
}

function onTypeChange() {
    const sel = document.getElementById('vehicleTypeId');
    const selectedText = sel.options[sel.selectedIndex]?.text?.toLowerCase() || '';
    const isRefrig = REFRIGERATOR_KEYWORDS.some(k => selectedText.includes(k));
    document.getElementById('tempRangeRow').style.display = isRefrig ? '' : 'none';
}

async function saveVehicle() {
    const payload = {
        owner_id:         document.getElementById('ownerId').value || null,
        driver_id:        document.getElementById('driverId').value || null,
        supplier_id:      document.getElementById('supplierId').value || null,
        vehicle_type_id:  document.getElementById('vehicleTypeId').value || null,
        tonnage:          document.getElementById('tonnage').value || null,
        volume:           document.getElementById('volume').value || null,
        tractor_brand:    document.getElementById('tractorBrand').value.trim(),
        tractor_plate:    document.getElementById('tractorPlate').value.toUpperCase().trim(),
        tractor_year:     document.getElementById('tractorYear').value || null,
        trailer_brand:    document.getElementById('trailerBrand').value.trim(),
        trailer_plate:    document.getElementById('trailerPlate').value.toUpperCase().trim(),
        trailer_year:     document.getElementById('trailerYear').value || null,
        temperature_min:  document.getElementById('tempMin').value || null,
        temperature_max:  document.getElementById('tempMax').value || null,
    };

    if (!payload.tractor_brand)  { Starget.toast('Введите марку тягача', 'error'); return; }
    if (!payload.tractor_plate)  { Starget.toast('Введите номер тягача', 'error'); return; }
    if (!payload.trailer_brand)  { Starget.toast('Введите марку прицепа', 'error'); return; }
    if (!payload.trailer_plate)  { Starget.toast('Введите номер прицепа', 'error'); return; }

    const fd = new FormData();
    Object.entries(payload).forEach(([key, value]) => {
        if (value !== null && value !== '') fd.append(key, value);
    });

    Array.from(document.getElementById('vehicleDocs').files).forEach(file => {
        fd.append('documents[]', file);
    });

    const res = await Starget.api('POST', '/vehicles', fd, true);
    if (res && res.success) {
        Starget.toast('Транспорт добавлен', 'success');
        setTimeout(() => { location.href = '/vehicles'; }, 800);
    }
}

const vehicleDocsDrop = document.getElementById('vehicleDocsDrop');
vehicleDocsDrop.addEventListener('dragover', e => { e.preventDefault(); vehicleDocsDrop.classList.add('drag-over'); });
vehicleDocsDrop.addEventListener('dragleave', () => vehicleDocsDrop.classList.remove('drag-over'));
vehicleDocsDrop.addEventListener('drop', e => {
    e.preventDefault();
    vehicleDocsDrop.classList.remove('drag-over');
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(file => dt.items.add(file));
    document.getElementById('vehicleDocs').files = dt.files;
    previewVehicleDocs();
});

init();
</script>
@endpush
