@extends('layouts.app')
@section('title', 'Создать водителя')

@section('header_left')
<div style="display:flex;align-items:center;gap:12px">
    <a href="/drivers" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    </a>
    <div>
        <div class="page-title">Создание нового водителя</div>
        <div class="page-subtitle">Введите личные данные и документы</div>
    </div>
</div>
@endsection

@section('header_actions')
<a href="/drivers" class="header-btn">Отмена</a>
<button class="btn btn-primary" onclick="saveDriver()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
    Сохранить водителя
</button>
@endsection

@section('content')
<div class="driver-create-layout">
    {{-- LEFT: FORM --}}
    <div>
        {{-- ЛИЧНЫЕ ДАННЫЕ --}}
        <div class="form-section">
            <div class="form-section-title">Личные данные</div>
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label class="form-label">ФИО <span class="required">*</span></label>
                    <input type="text" class="form-input" id="fullName" placeholder="Иванов Иван Иванович">
                </div>
                <div class="form-group">
                    <label class="form-label">ИИН <span class="required">*</span></label>
                    <input type="text" class="form-input" id="iin" placeholder="123456789012" maxlength="12">
                </div>
                <div class="form-group">
                    <label class="form-label">Тип <span class="required">*</span></label>
                    <select class="form-select" id="personType">
                        <option value="individual">Физическое лицо</option>
                        <option value="legal">Юридическое лицо (ИП)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Телефон <span class="required">*</span></label>
                    <input type="tel" class="form-input" id="phone" placeholder="+7 747 777 74 74" oninput="formatDriverPhone(this)" maxlength="16">
                </div>
                <div class="form-group">
                    <label class="form-label">Категории прав <span class="required">*</span></label>
                    <div class="checkbox-row">
                        <label><input type="checkbox" value="B" id="licB"> B</label>
                        <label><input type="checkbox" value="C" id="licC"> C</label>
                        <label><input type="checkbox" value="CE" id="licCE"> CE</label>
                        <label><input type="checkbox" value="D" id="licD"> D</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ДОКУМЕНТЫ --}}
        <div class="form-section">
            <div class="form-section-title">Документы <span class="required">*</span></div>

            <table class="data-table" style="margin-bottom:8px" id="docsTable">
                <thead>
                    <tr>
                        <th>Тип документа</th>
                        <th>Номер</th>
                        <th>Дата выдачи</th>
                        <th>Дата истечения</th>
                        <th>Кем выдан</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="docsTableBody">
                    <tr id="docsEmptyRow"><td colspan="6" class="table-empty">Документы не добавлены</td></tr>
                </tbody>
            </table>

            <button class="btn btn-outline btn-sm" onclick="addDocRow()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
                Добавить документ
            </button>

            <div style="margin-top:16px">
                <label class="form-label">Скан-копии документов <span class="required">*</span></label>
                <div class="file-drop" onclick="document.getElementById('docFiles').click()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <div class="file-drop-text">Перетащите или <span>нажмите для выбора</span></div>
                    <input type="file" id="docFiles" multiple accept=".pdf,.jpg,.png" style="display:none" onchange="previewDocFiles()">
                </div>
                <div id="docFilesPreview" style="margin-top:6px"></div>
            </div>
        </div>
    </div>

    {{-- RIGHT: ROLE CARD --}}
    <div>
        <div class="role-card">
            <div class="role-card-title">Роль в системе</div>
            <div class="role-card-subtitle">Выберите роли для данного пользователя</div>

            <div class="role-option selected" id="roleDriver" onclick="toggleRole('driver')">
                <div class="role-option-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m8-10a4 4 0 100-8 4 4 0 000 8z"/></svg>
                </div>
                <div class="role-option-info">
                    <div class="role-option-name">Водитель</div>
                    <div class="role-option-desc">Доступ к мобильному приложению</div>
                </div>
                <div class="role-option-check" id="roleDriverCheck">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                </div>
                <input type="checkbox" id="isDriver" style="display:none" checked>
            </div>

            <div class="role-option" id="roleOwner" onclick="toggleRole('owner')">
                <div class="role-option-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
                </div>
                <div class="role-option-info">
                    <div class="role-option-name">Владелец</div>
                    <div class="role-option-desc">Управление финансами и автопарком</div>
                </div>
                <div class="role-option-check" id="roleOwnerCheck" style="display:none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                </div>
                <input type="checkbox" id="isOwner" style="display:none">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let docCount = 0;
let docTypes = [];

async function init() {
    docTypes = await Starget.loadDocTypes();
}

function toggleRole(role) {
    const check = document.getElementById('role' + (role === 'driver' ? 'Driver' : 'Owner') + 'Check');
    const cb    = document.getElementById('is' + (role === 'driver' ? 'Driver' : 'Owner'));
    const card  = document.getElementById('role' + (role === 'driver' ? 'Driver' : 'Owner'));
    cb.checked = !cb.checked;
    check.style.display = cb.checked ? '' : 'none';
    card.classList.toggle('selected', cb.checked);
}

function addDocRow() {
    docCount++;
    const empty = document.getElementById('docsEmptyRow');
    if (empty) empty.remove();
    const tr = document.createElement('tr');
    tr.id = `docrow_${docCount}`;
    const typeOptions = docTypes.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
    tr.innerHTML = `
        <td><select class="form-select form-select-sm" id="dtype_${docCount}">${typeOptions}</select></td>
        <td><input type="text" class="form-input form-input-sm" id="dnum_${docCount}" placeholder="AA123456"></td>
        <td><input type="date" class="form-input form-input-sm" id="dissue_${docCount}"></td>
        <td><input type="date" class="form-input form-input-sm" id="dexpiry_${docCount}"></td>
        <td><input type="text" class="form-input form-input-sm" id="dissuer_${docCount}" placeholder="МВД РК"></td>
        <td><button type="button" class="btn-icon-sm red" onclick="document.getElementById('docrow_${docCount}').remove()">✕</button></td>
    `;
    document.getElementById('docsTableBody').appendChild(tr);
}

function previewDocFiles() {
    const files = document.getElementById('docFiles').files;
    const preview = document.getElementById('docFilesPreview');
    preview.innerHTML = Array.from(files).map(f =>
        `<div class="file-item">✓ ${f.name}</div>`
    ).join('');
}

function collectDocs() {
    const docs = [];
    for (let i = 1; i <= docCount; i++) {
        const t = document.getElementById(`dtype_${i}`);
        const n = document.getElementById(`dnum_${i}`);
        if (!t || !n) continue;
        docs.push({
            document_type_id: t.value,
            number:           n.value,
            issued_date:      document.getElementById(`dissue_${i}`)?.value || null,
            expires_at:       document.getElementById(`dexpiry_${i}`)?.value || null,
            issued_by:        document.getElementById(`dissuer_${i}`)?.value || null,
        });
    }
    return docs;
}

function getLicenses() {
    return ['B','C','CE','D'].filter(l => document.getElementById('lic' + l)?.checked);
}

async function saveDriver() {
    const payload = {
        full_name:       document.getElementById('fullName').value,
        iin:             document.getElementById('iin').value,
        type:            document.getElementById('personType').value,
        phone:           document.getElementById('phone').value,
        license_classes: getLicenses().join(','),
        is_driver:       document.getElementById('isDriver').checked,
        is_owner:        document.getElementById('isOwner').checked,
        documents:       collectDocs(),
    };

    if (!payload.full_name)         { Starget.toast('Введите ФИО', 'error'); return; }
    if (!payload.iin)                { Starget.toast('Введите ИИН', 'error'); return; }
    if (!payload.phone)              { Starget.toast('Введите телефон', 'error'); return; }
    if (!payload.license_classes)    { Starget.toast('Выберите хотя бы одну категорию прав', 'error'); return; }
    if (!payload.documents.length)   { Starget.toast('Добавьте хотя бы один документ', 'error'); return; }
    const docFilesInput = document.getElementById('docFiles');
    if (!docFilesInput.files || !docFilesInput.files.length) { Starget.toast('Загрузите скан-копии документов', 'error'); return; }

    const res = await Starget.api('POST', '/drivers', payload);
    if (res && res.success) {
        Starget.toast('Водитель создан', 'success');
        setTimeout(() => { location.href = '/drivers'; }, 800);
    }
}

function formatDriverPhone(input) {
    let digits = input.value.replace(/\D/g, '');
    if (digits.startsWith('8')) digits = '7' + digits.slice(1);
    if (!digits.startsWith('7') && digits.length > 0) digits = '7' + digits;
    digits = digits.slice(0, 11);
    let result = '';
    if (digits.length > 0)  result = '+7';
    if (digits.length > 1)  result += ' ' + digits.slice(1, 4);
    if (digits.length > 4)  result += ' ' + digits.slice(4, 7);
    if (digits.length > 7)  result += ' ' + digits.slice(7, 9);
    if (digits.length > 9)  result += ' ' + digits.slice(9, 11);
    input.value = result;
}

init();
</script>
@endpush
