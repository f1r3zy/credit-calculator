document.addEventListener('DOMContentLoaded', () => {
    const syncSlider = (sliderId, inputId) => {
        const slider = document.getElementById(sliderId);
        const input = document.getElementById(inputId);
        if (!slider || !input) return;
        slider.addEventListener('input', () => { input.value = slider.value; });
        input.addEventListener('input', () => { slider.value = input.value; });
    };
    syncSlider('amountSlider', 'amount');
    syncSlider('monthsSlider', 'months');

    document.querySelectorAll('.internal-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            document.body.classList.add('fade-out');
            setTimeout(() => { window.location.href = href; }, 250);
        });
    });

    const interestType = document.getElementById('interestType');
    const fixedDiv = document.getElementById('fixedRateDiv');
    const variableDiv = document.getElementById('variableDiv');
    const mixedDiv = document.getElementById('mixedDiv');
    if (interestType && fixedDiv && variableDiv && mixedDiv) {
        interestType.addEventListener('change', () => {
            const val = interestType.value;
            fixedDiv.style.display = val === 'fixed' || val === 'mixed' ? 'block' : 'none';
            variableDiv.style.display = val === 'variable' || val === 'mixed' ? 'block' : 'none';
            mixedDiv.style.display = val === 'mixed' ? 'block' : 'none';
        });
        interestType.dispatchEvent(new Event('change'));
    }

    const graceCheck = document.getElementById('grace');
    const graceOptions = document.getElementById('graceOptions');
    if (graceCheck && graceOptions) {
        graceCheck.addEventListener('change', () => {
            graceOptions.style.display = graceCheck.checked ? 'block' : 'none';
        });
    }

    const calcBtn = document.getElementById('calculateBtn');
    if (calcBtn) calcBtn.addEventListener('click', calculate);

    const earlyBtn = document.getElementById('earlyRepayBtn');
    if (earlyBtn) earlyBtn.addEventListener('click', earlyRepayment);

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('/api/logout', { method: 'POST' });
            window.location.href = '/';
        });
    }

    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            const payload = Object.fromEntries(formData);
            try {
                const resp = await fetch('/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await resp.json();
                if (data.error) { alert(data.error); }
                else { window.location.href = '/calculator'; }
            } catch (err) { alert('Eroare: ' + err.message); }
        });
    }

    // Register form
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);
            const payload = Object.fromEntries(formData);
            try {
                const resp = await fetch('/api/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await resp.json();
                if (data.error) { alert(data.error); }
                else { window.location.href = '/calculator'; }
            } catch (err) { alert('Eroare: ' + err.message); }
        });
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function calculate() {
    const form = document.getElementById('calc-form');
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());
    payload.grace = document.getElementById('grace').checked;
    payload.advance_percent = parseFloat(payload.advance_percent) || 0;
    payload.rate = parseFloat(payload.rate) || 0;
    payload.margin = parseFloat(payload.margin) || 0;
    payload.amount = parseFloat(payload.amount);
    payload.months = parseInt(payload.months);
    payload.base_rate = parseFloat(document.getElementById('baseRate')?.value) || null;
    payload.fixed_months = parseInt(document.getElementById('fixedMonths')?.value) || 60;

    try {
        const resp = await fetch('/api/calculate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await resp.json();
        if (data.error) { alert(data.error); return; }
        if (data.errors) { alert(Object.values(data.errors).join('\n')); return; }
        window._lastResult = data.result;
        displayResults(data.result);
        document.getElementById('earlyRepaymentSection').style.display = 'block';
    } catch (e) { alert('Eroare: ' + e.message); }
}

function displayResults(result) {
    document.getElementById('results').style.display = 'block';
    document.getElementById('results').innerHTML = `
        <div class="card shadow-sm">
            <div class="card-body">
                <h3>Rezultate</h3>
                <div class="row">
                    <div class="col-md-3 mb-3"><div class="border rounded p-3 text-center"><h6>Rata lunara</h6><h4>${escapeHtml(result.monthly_payment_first)} MDL</h4></div></div>
                    <div class="col-md-3 mb-3"><div class="border rounded p-3 text-center"><h6>Total rambursat</h6><h4>${escapeHtml(result.total_payments)} MDL</h4></div></div>
                    <div class="col-md-3 mb-3"><div class="border rounded p-3 text-center"><h6>Total dobanda</h6><h4>${escapeHtml(result.total_interest)} MDL</h4></div></div>
                    <div class="col-md-3 mb-3"><div class="border rounded p-3 text-center"><h6>DAE</h6><h4>${escapeHtml(result.dae)} %</h4></div></div>
                </div>
                <h4>Grafic de amortizare</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="scheduleTable">
                        <thead class="table-dark">
                            <tr><th>Luna</th><th>Data</th><th>Rata totală</th><th>Principal</th><th>Dobândă</th><th>Sold rămas</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button id="exportCSV" class="btn btn-secondary" type="button">Export CSV</button>
                    <button id="saveSim" class="btn btn-success" type="button">Salvează simulare</button>
                </div>
            </div>
        </div>
    `;
    const tbody = document.querySelector('#scheduleTable tbody');
    result.schedule.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${escapeHtml(row.month)}</td><td>${escapeHtml(row.due_date)}</td><td>${escapeHtml(row.total_payment)}</td>
                        <td>${escapeHtml(row.principal)}</td><td>${escapeHtml(row.interest)}</td><td>${escapeHtml(row.remaining_balance)}</td>`;
        tbody.appendChild(tr);
    });
    document.getElementById('exportCSV').addEventListener('click', exportCSV);
    document.getElementById('saveSim').addEventListener('click', saveSimulation);
}

async function exportCSV() {
    const result = window._lastResult;
    if (!result || !result.schedule) return alert('Mai întâi calculează.');
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
    if (!csrfToken) { alert('Token CSRF lipsă. Reîncarcă pagina.'); return; }
    try {
        const resp = await fetch('/api/export-csv', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ schedule: result.schedule, csrf_token: csrfToken })
        });
        if (!resp.ok) {
            const err = await resp.json();
            alert(err.error || 'Eroare la export.');
            return;
        }
        const blob = await resp.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'amortizare.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    } catch (e) { alert('Eroare: ' + e.message); }
}

async function saveSimulation() {
    const result = window._lastResult;
    if (!result) return alert('Calculează mai întâi.');
    const type = document.querySelector('select[name="type"]')?.value;
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
    if (!csrfToken) { alert('Token CSRF lipsă. Reîncarcă pagina.'); return; }
    try {
        const resp = await fetch('/api/simulations', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                type: type,
                params: Object.fromEntries(new FormData(document.getElementById('calc-form'))),
                results: result,
                csrf_token: csrfToken
            })
        });
        const data = await resp.json();
        if (data.error) { alert(data.error); return; }
        alert('Simulare salvată!');
    } catch (e) { alert('Eroare la salvare: ' + e.message); }
}

async function earlyRepayment() {
    const month = parseInt(document.getElementById('earlyMonth').value);
    const amount = parseFloat(document.getElementById('earlyAmount').value);
    const option = document.getElementById('earlyOption').value;
    if (!month || !amount) return alert('Completează luna și suma.');
    const schedule = window._lastResult?.schedule;
    if (!schedule) return alert('Nu există un grafic de amortizare.');
    const annualRate = parseFloat(document.getElementById('rate')?.value) || 7.5;
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
    const payload = { schedule, month, extra_amount: amount, option, annual_rate: annualRate, csrf_token: csrfToken };
    try {
        const resp = await fetch('/api/early-repayment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await resp.json();
        if (data.error) { alert(data.error); return; }
        const res = data.result;
        document.getElementById('earlyResult').innerHTML = `
            <div class="alert alert-success">
                Economie dobândă: <strong>${res.savings} MDL</strong><br>
                Dobânda veche: ${res.old_total_interest} MDL, Dobânda nouă: ${res.new_total_interest} MDL
            </div>
        `;
        window._lastResult.schedule = res.schedule;
        displayResults({...window._lastResult, schedule: res.schedule});
    } catch (e) { alert('Eroare: ' + e.message); }
}