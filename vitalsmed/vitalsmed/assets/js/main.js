// ============================================================
// VitalsMed — Main JavaScript
// ============================================================

// ── Modal helpers ─────────────────────────────────────────
function openModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.add('open');
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.remove('open');
}

// Close modal when clicking backdrop
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.classList.remove('open');
    }
});

// Escape key closes modals
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop.open').forEach(m => m.classList.remove('open'));
    }
});

// ── Live table search ─────────────────────────────────────
const searchInput = document.getElementById('tableSearch');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        const q   = this.value.toLowerCase();
        const tbl = document.querySelector('.searchable-table tbody');
        if (!tbl) return;
        tbl.querySelectorAll('tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

// ── Client-side vital sign validation ─────────────────────
const vitalsForm = document.getElementById('vitalsForm');
if (vitalsForm) {
    vitalsForm.addEventListener('submit', function (e) {
        let valid = true;

        const systolic  = parseFloat(document.getElementById('systolic_bp')?.value);
        const diastolic = parseFloat(document.getElementById('diastolic_bp')?.value);
        const hr        = parseFloat(document.getElementById('heart_rate')?.value);
        const temp      = parseFloat(document.getElementById('temperature')?.value);
        const o2        = parseFloat(document.getElementById('oxygen_sat')?.value);

        clearVitalErrors();

        if (systolic < 60 || systolic > 250) {
            showFieldError('systolic_bp', 'Systolic BP must be between 60–250 mmHg');
            valid = false;
        }
        if (diastolic < 40 || diastolic > 150) {
            showFieldError('diastolic_bp', 'Diastolic BP must be between 40–150 mmHg');
            valid = false;
        }
        if (!isNaN(systolic) && !isNaN(diastolic) && systolic <= diastolic) {
            showFieldError('diastolic_bp', 'Diastolic must be lower than Systolic');
            valid = false;
        }
        if (hr < 30 || hr > 250) {
            showFieldError('heart_rate', 'Heart Rate must be between 30–250 bpm');
            valid = false;
        }
        if (temp < 32 || temp > 45) {
            showFieldError('temperature', 'Temperature must be between 32–45 °C');
            valid = false;
        }
        const o2Input = document.getElementById('oxygen_sat')?.value;
        if (o2Input !== '' && (o2 < 50 || o2 > 100)) {
            showFieldError('oxygen_sat', 'O₂ Saturation must be between 50–100 %');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.add('is-invalid');
    let fb = field.nextElementSibling;
    if (!fb || !fb.classList.contains('invalid-feedback')) {
        fb = document.createElement('div');
        fb.className = 'invalid-feedback';
        field.parentNode.insertBefore(fb, field.nextSibling);
    }
    fb.textContent = message;
}

function clearVitalErrors() {
    document.querySelectorAll('.form-control.is-invalid').forEach(f => f.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach(f => f.remove());
}

// ── Auto-dismiss flash messages ───────────────────────────
const flash = document.querySelector('.flash-msg');
if (flash) {
    setTimeout(() => {
        flash.style.transition = 'opacity .5s';
        flash.style.opacity    = '0';
        setTimeout(() => flash.remove(), 500);
    }, 4000);
}

// ── Confirm delete ────────────────────────────────────────
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
        if (!confirm(this.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
});

// ── Active nav link ───────────────────────────────────────
document.querySelectorAll('.nav-link').forEach(link => {
    if (link.href === window.location.href) link.classList.add('active');
});