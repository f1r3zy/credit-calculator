<?php
include __DIR__ . '/layout.php';
use App\Core\Auth;
if (!Auth::check()) {
    header('Location: /login');
    exit;
}
?>
<div class="container mt-4">
    <h2>Simularile mele salvate</h2>
    <table class="table" id="simulationsTable">
        <thead>
            <tr>
                <th>Tip credit</th>
                <th>Data</th>
                <th>Actiuni</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="3">Se incarca...</td></tr>
        </tbody>
    </table>
</div>

<div id="customModal" class="custom-modal-overlay" style="display:none;">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5>Detalii simulare</h5>
            <button type="button" class="custom-modal-close" id="closeModalBtn">&times;</button>
        </div>
        <div class="custom-modal-body" id="simulationDetail">
            Incarcare...
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const navbar = document.querySelector('.navbar');
    const footer = document.querySelector('.site-footer');

    const originalFooterMaxHeight = footer ? footer.style.maxHeight : null;
    const originalFooterOpacity = footer ? footer.style.opacity : null;
    const originalNavbarOpacity = navbar ? navbar.style.opacity : null;
    function openModal() {
        document.getElementById('customModal').style.display = 'flex';
        document.body.classList.add('modal-open-custom');
        if (navbar) {
            navbar.style.transition = 'opacity 0.5s ease';
            navbar.style.opacity = '0.25';
        }
        if (footer) {
            const currentHeight = footer.scrollHeight;
            footer.style.transition = 'max-height 0.5s ease, opacity 0.5s ease, transform 0.5s ease';
            footer.style.maxHeight = currentHeight + 'px'; 
            footer.offsetHeight; 
            footer.style.maxHeight = '0';
            footer.style.opacity = '0';
            footer.style.transform = 'translateY(20px)'; 
            footer.style.overflow = 'hidden';
        }
    }
    function closeModal() {
        document.getElementById('customModal').style.display = 'none';
        document.body.classList.remove('modal-open-custom');
        if (navbar) {
            navbar.style.opacity = originalNavbarOpacity || '1';
        }
        if (footer) {
            footer.style.transition = 'max-height 0.5s ease, opacity 0.5s ease, transform 0.5s ease';
            footer.style.maxHeight = originalFooterMaxHeight || '1000px'; 
            footer.style.transform = 'translateY(0)';
            footer.style.overflow = '';
            setTimeout(() => {
                footer.style.maxHeight = '';
            }, 500);
        }
    }

    try {
        const resp = await fetch('/api/simulations');
        const data = await resp.json();
        if (data.error) { alert(data.error); return; }
        const tbody = document.querySelector('#simulationsTable tbody');
        tbody.innerHTML = '';
        if (data.simulations.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3">Nicio simulare salvata.</td></tr>';
            return;
        }
        data.simulations.forEach(sim => {
            const row = document.createElement('tr');
            row.innerHTML = `<td>${escapeHtml(sim.type)}</td>
                             <td>${escapeHtml(sim.created_at)}</td>
                             <td><button class="btn btn-sm btn-info view-sim" data-id="${sim.id}">Vezi</button></td>`;
            tbody.appendChild(row);
        });
        document.querySelectorAll('.view-sim').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.dataset.id;
                const resp = await fetch(`/api/simulations/${id}`);
                const d = await resp.json();
                if (d.error) { alert(d.error); return; }
                const sim = d.simulation;
                const params = JSON.parse(sim.params || '{}');
                const results = JSON.parse(sim.results || '{}');
                delete params.csrf_token;
                delete results.csrf_token;

                const modalBody = document.getElementById('simulationDetail');
                modalBody.innerHTML = buildDetailHtml(sim, params, results);

                openModal();
            });
        });

    } catch (e) {
        console.error(e);
    }

    document.getElementById('closeModalBtn').addEventListener('click', closeModal);
    document.getElementById('customModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
});

function buildDetailHtml(sim, params, results) {
    let html = `<span class="badge bg-primary mb-3">Tip: ${escapeHtml(sim.type)}</span>`;

    html += '<h6 class="fw-bold mt-3">Parametrii</h6>';
    if (Object.keys(params).length > 0) {
        html += '<div class="table-responsive"><table class="table table-sm table-bordered"><tbody>';
        for (const [key, value] of Object.entries(params)) {
            html += `<tr><td class="fw-bold" style="width:35%">${escapeHtml(key)}</td><td>${escapeHtml(String(value))}</td></tr>`;
        }
        html += '</tbody></table></div>';
    } else {
        html += '<p class="text-muted">Nu exista parametri salvati.</p>';
    }
    html += '<h6 class="fw-bold mt-3">Rezultate</h6>';
    if (Object.keys(results).length > 0) {
        html += '<div class="table-responsive"><table class="table table-sm table-bordered"><tbody>';
        for (const [key, value] of Object.entries(results)) {
            if (typeof value === 'object') continue;
            html += `<tr><td class="fw-bold" style="width:35%">${escapeHtml(key)}</td><td>${escapeHtml(String(value))}</td></tr>`;
        }
        html += '</tbody></table></div>';
    } else {
        html += '<p class="text-muted">Nu exista rezultate salvate.</p>';
    }

    return html;
}
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
<?php include __DIR__ . '/footer.php'; ?>