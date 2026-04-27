@extends('layouts.app')
@section('title', 'Водитель')

@section('header_left')
<div style="display:flex;align-items:center;gap:12px">
    <a href="/drivers" class="back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    </a>
    <div>
        <div class="page-title" id="driverTitle">Водитель</div>
        <div class="page-subtitle" id="driverSubtitle">Личная карточка</div>
    </div>
</div>
@endsection

@section('header_actions')
<button class="header-btn" id="editStatusBtn" onclick="changeStatus()" style="display:none">Изменить статус</button>
@endsection

@section('content')

<div id="loadingState" style="padding:60px;text-align:center;color:var(--text-muted)">Загрузка...</div>

<div id="driverLayout" class="driver-create-layout" style="display:none">

    {{-- LEFT --}}
    <div>
        {{-- ЛИЧНЫЕ ДАННЫЕ --}}
        <div class="form-section">
            <div class="form-section-title">Личные данные</div>
            <div class="detail-grid">
                <div class="detail-item form-group-full">
                    <div class="detail-label">ФИО</div>
                    <div class="detail-value" id="dFullName">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">ИИН</div>
                    <div class="detail-value text-mono" id="dIin">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Тип</div>
                    <div class="detail-value" id="dType">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Телефон</div>
                    <div class="detail-value" id="dPhone">—</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Категории прав</div>
                    <div class="detail-value" id="dLicenses">—</div>
                </div>
            </div>
        </div>

        {{-- ДОКУМЕНТЫ --}}
        <div class="form-section">
            <div class="form-section-title">Документы</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Тип</th>
                        <th>Номер</th>
                        <th>Дата выдачи</th>
                        <th>Кем выдан</th>
                        <th>Действует до</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="docsTable">
                    <tr><td colspan="6" class="table-empty">Загрузка...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- RIGHT --}}
    <div>
        <div class="side-info-card">
            <div class="side-info-title">Статус</div>
            <div id="dStatus" style="margin:10px 0">—</div>
            <div class="side-info-row">
                <span>Добавлен</span>
                <span id="dCreatedAt">—</span>
            </div>
        </div>

        <div class="side-info-card" style="margin-top:12px" id="vehicleCard" style="display:none">
            <div class="side-info-title">Закреплённый транспорт</div>
            <div id="vehiclesList" style="margin-top:8px">—</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const driverId = location.pathname.split('/').pop();

async function load() {
    const res = await Starget.api('GET', `/drivers/${driverId}`);
    if (!res || !res.success) {
        document.getElementById('loadingState').textContent = 'Ошибка загрузки данных';
        return;
    }
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('driverLayout').style.display = '';
    document.getElementById('editStatusBtn').style.display = '';

    const d = res.data;

    document.getElementById('driverTitle').textContent   = d.full_name || 'Водитель';
    document.getElementById('driverSubtitle').textContent = 'ИИН: ' + (d.iin || '—');

    document.getElementById('dFullName').textContent = d.full_name || '—';
    document.getElementById('dIin').textContent      = d.iin       || '—';
    document.getElementById('dPhone').textContent    = d.phone     || '—';
    document.getElementById('dType').textContent     = d.type === 'legal' ? 'Юридическое лицо (ИП)' : 'Физическое лицо';

    const licenses = d.license_classes
        ? (Array.isArray(d.license_classes) ? d.license_classes : d.license_classes.split(','))
        : [];
    document.getElementById('dLicenses').innerHTML = licenses.length
        ? licenses.map(l => `<span class="badge badge-secondary" style="font-size:11px">${l.trim()}</span>`).join(' ')
        : '—';

    document.getElementById('dStatus').innerHTML    = Starget.fmt.status(d.status || 'idle');
    document.getElementById('dCreatedAt').textContent = Starget.fmt.date(d.created_at);

    // Documents
    const docs = d.documents || [];
    const tbody = document.getElementById('docsTable');
    if (!docs.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="table-empty">Документы не добавлены</td></tr>';
    } else {
        tbody.innerHTML = docs.map(doc => {
            const today = new Date();
            const expires = doc.expires_at ? new Date(doc.expires_at) : null;
            const isExpiring = expires && expires <= new Date(today.getTime() + 30 * 86400000);
            const isExpired  = expires && expires < today;
            const expiresHtml = expires
                ? `<span class="${isExpired ? 'text-danger' : isExpiring ? 'text-warning' : ''}">${Starget.fmt.date(doc.expires_at)}</span>`
                : '—';
            const fileHtml = doc.file_path
                ? `<a href="/storage/${doc.file_path}" target="_blank" class="action-link">Скачать</a>`
                : '—';
            return `<tr>
                <td>${doc.document_type || '—'}</td>
                <td class="text-mono">${doc.number || '—'}</td>
                <td>${Starget.fmt.date(doc.issued_date)}</td>
                <td>${doc.issued_by || '—'}</td>
                <td>${expiresHtml}</td>
                <td>${fileHtml}</td>
            </tr>`;
        }).join('');
    }

    // Vehicles
    if (d.vehicles && d.vehicles.length) {
        document.getElementById('vehicleCard').style.display = '';
        document.getElementById('vehiclesList').innerHTML = d.vehicles.map(v =>
            `<div style="display:flex;align-items:center;gap:6px;padding:4px 0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .5M13 16h2l3-4.5V9h-5v7z"/></svg>
                <span>${v.tractor_brand || ''}</span>
                <span class="plate-badge">${v.tractor_plate || ''}</span>
            </div>`
        ).join('');
    }
}

async function changeStatus() {
    const statuses = ['idle', 'on_trip', 'reserve', 'sick'];
    const labels   = { idle: 'Свободен', on_trip: 'На рейсе', reserve: 'Резерв', sick: 'Больничный' };
    const current  = document.getElementById('dStatus').querySelector('.badge')?.textContent || '';
    const newStatus = statuses.find(s => labels[s] !== current) || 'idle';

    const choice = prompt('Введите статус:\nidle — Свободен\non_trip — На рейсе\nreserve — Резерв\nsick — Больничный');
    if (!choice || !statuses.includes(choice.trim())) { Starget.toast('Неверный статус', 'error'); return; }

    const res = await Starget.api('PUT', `/drivers/${driverId}`, { status: choice.trim() });
    if (res && res.success) {
        Starget.toast('Статус обновлён', 'success');
        load();
    }
}

load();
</script>
@endpush
