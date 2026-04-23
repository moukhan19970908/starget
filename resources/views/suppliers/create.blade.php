@extends('layouts.app')
@section('title', 'Создать поставщика')

@section('header_left')
<div style="display:flex;align-items:center;gap:12px">
    <a href="/suppliers" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    </a>
    <div>
        <div class="page-title">Создание поставщика</div>
        <div class="page-subtitle">Заполните данные транспортной компании или ИП</div>
    </div>
</div>
@endsection

@section('content')
<div class="supplier-create-layout">
    {{-- LEFT: FORM --}}
    <div>
        {{-- ОСНОВНЫЕ ДАННЫЕ --}}
        <div class="form-section">
            <div class="form-section-title">Основные данные</div>
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label class="form-label">Название компании / ФИО <span class="required">*</span></label>
                    <input type="text" class="form-input" id="companyName" placeholder="ТОО «Ромашка»">
                </div>
                <div class="form-group">
                    <label class="form-label">БИН/ИИН <span class="required">*</span></label>
                    <input type="text" class="form-input" id="bin" placeholder="123456789012" maxlength="12">
                </div>
                <div class="form-group">
                    <label class="form-label">Тип организации <span class="required">*</span></label>
                    <select class="form-select" id="orgType">
                        <option value="legal">Юридическое лицо (ТОО, АО)</option>
                        <option value="individual">Физическое лицо / ИП</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Специализация</label>
                    <input type="text" class="form-input" id="specialization" placeholder="Грузоперевозки, рефрижераторы...">
                </div>
            </div>
        </div>

        {{-- КОНТАКТНЫЕ ДАННЫЕ --}}
        <div class="form-section">
            <div class="form-section-title">Контактные данные</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Контактное лицо</label>
                    <input type="text" class="form-input" id="contactPerson" placeholder="Иванов Иван Иванович">
                </div>
                <div class="form-group">
                    <label class="form-label">Телефон</label>
                    <input type="tel" class="form-input" id="phone" placeholder="+7 (777) 000-00-00">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-input" id="email" placeholder="company@example.kz">
                </div>
                <div class="form-group">
                    <label class="form-label">Адрес</label>
                    <input type="text" class="form-input" id="address" placeholder="г. Алматы, ул. Примерная, 1">
                </div>
            </div>
        </div>

        {{-- ТРАНСПОРТ --}}
        <div class="form-section">
            <div class="form-section-title">Транспортные средства</div>
            <div id="vehiclesList">
                <div class="empty-vehicles" style="color:var(--text-muted);font-size:13px;padding:12px 0">
                    Транспорт не добавлен
                </div>
            </div>
            <button class="btn btn-outline btn-sm" onclick="addVehicleRow()" style="margin-top:8px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
                Добавить транспорт
            </button>
        </div>

        {{-- ДОКУМЕНТЫ --}}
        <div class="form-section">
            <div class="form-section-title">Документы и скан-копии</div>
            <div class="form-group">
                <label class="form-label">Комментарий</label>
                <textarea class="form-input" id="notes" rows="3" placeholder="Дополнительная информация..."></textarea>
            </div>
            <div class="file-drop" id="fileDrop" onclick="document.getElementById('fileInput').click()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <div class="file-drop-text">Перетащите файлы или <span>нажмите для выбора</span></div>
                <div class="file-drop-hint">PDF, JPG, PNG до 10МБ</div>
                <input type="file" id="fileInput" multiple accept=".pdf,.jpg,.png" style="display:none" onchange="previewFiles()">
            </div>
            <div id="filePreview" style="margin-top:8px"></div>
        </div>
    </div>

    {{-- RIGHT: STATUS PANEL --}}
    <div>
        <div class="status-panel">
            <div class="status-panel-badge">
                <span class="badge badge-warning" style="font-size:11px;padding:6px 12px">НА ПРОВЕРКЕ</span>
            </div>
            <div class="status-panel-info">
                После сохранения поставщик будет отправлен на проверку. После одобрения статус изменится на «Активный».
            </div>
            <div class="finance-divider"></div>
            <button class="btn btn-primary btn-block" onclick="saveSupplier()">Сохранить поставщика</button>
            <a href="/suppliers" class="btn btn-outline btn-block" style="margin-top:8px;text-align:center;display:block">Отмена</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let vehicleCount = 0;

function addVehicleRow() {
    vehicleCount++;
    const empty = document.querySelector('.empty-vehicles');
    if (empty) empty.remove();
    const row = document.createElement('div');
    row.className = 'vehicle-row-form';
    row.id = `vrow_${vehicleCount}`;
    row.innerHTML = `<div style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:end;margin-bottom:8px">
        <div>
            <label class="form-label">Марка/Тип тягача</label>
            <input type="text" class="form-input" id="vbrand_${vehicleCount}" placeholder="Volvo FH">
        </div>
        <div>
            <label class="form-label">Гос. номер</label>
            <input type="text" class="form-input" id="vplate_${vehicleCount}" placeholder="123 АВС 02">
        </div>
        <div style="padding-bottom:1px">
            <button type="button" class="btn-icon-sm red" onclick="document.getElementById('vrow_${vehicleCount}').remove()">✕</button>
        </div>
    </div>`;
    document.getElementById('vehiclesList').appendChild(row);
}

function previewFiles() {
    const files = document.getElementById('fileInput').files;
    const preview = document.getElementById('filePreview');
    preview.innerHTML = Array.from(files).map(f =>
        `<div class="file-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M9 12h6m-6 4h3M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
            ${f.name} <span style="color:var(--text-muted)">(${(f.size/1024).toFixed(0)}КБ)</span>
        </div>`
    ).join('');
}

function collectVehicles() {
    const vehicles = [];
    for (let i = 1; i <= vehicleCount; i++) {
        const brand = document.getElementById(`vbrand_${i}`);
        const plate = document.getElementById(`vplate_${i}`);
        if (brand && plate && (brand.value || plate.value)) {
            vehicles.push({ brand: brand.value, plate: plate.value });
        }
    }
    return vehicles;
}

async function saveSupplier() {
    const payload = {
        company_name:   document.getElementById('companyName').value,
        bin:            document.getElementById('bin').value,
        type:           document.getElementById('orgType').value,
        specialization: document.getElementById('specialization').value,
        contact_person: document.getElementById('contactPerson').value,
        phone:          document.getElementById('phone').value,
        email:          document.getElementById('email').value,
        address:        document.getElementById('address').value,
        notes:          document.getElementById('notes').value,
        vehicles:       collectVehicles(),
    };

    if (!payload.company_name) { Starget.toast('Введите название компании', 'error'); return; }
    if (!payload.bin) { Starget.toast('Введите БИН/ИИН', 'error'); return; }

    const res = await Starget.api('POST', '/suppliers', payload);
    if (res && res.success) {
        Starget.toast('Поставщик создан', 'success');
        setTimeout(() => { location.href = '/suppliers'; }, 800);
    }
}

// Drag-and-drop on file drop zone
const drop = document.getElementById('fileDrop');
drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('drag-over'); });
drop.addEventListener('dragleave', () => drop.classList.remove('drag-over'));
drop.addEventListener('drop', e => {
    e.preventDefault();
    drop.classList.remove('drag-over');
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
    document.getElementById('fileInput').files = dt.files;
    previewFiles();
});
</script>
@endpush
