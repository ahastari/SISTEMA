{{-- Avisos al cajero cuando el gerente AUTORIZA o RECHAZA una solicitud de CRÉDITO.
     Incluir con: @include('puntoventa._avisos_credito')  (POS e Historial) --}}
<script>
(function () {
    const CLAVE = 'pos_credit_watch_{{ auth()->id() }}';
    const URL_ESTADO = '{{ route("puntoventa.creditos.estado") }}';

    function leer() {
        try { return JSON.parse(localStorage.getItem(CLAVE) || '[]'); } catch (e) { return []; }
    }
    function guardar() {
        try { localStorage.setItem(CLAVE, JSON.stringify(observadas)); } catch (e) {}
    }
    function esc(t) {
        return String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }
    function beep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const o = ctx.createOscillator(), g = ctx.createGain();
            o.connect(g); g.connect(ctx.destination);
            o.frequency.value = 880; g.gain.value = 0.08;
            o.start(); o.stop(ctx.currentTime + 0.25);
        } catch (e) {}
    }

    let observadas = leer();

    // Contenedor único ARRIBA a la derecha (reutiliza el del POS si existe) para que los avisos no se encimen
    function contenedorAvisos() {
        let c = document.getElementById('toastDescuentos') || document.getElementById('toastAvisosTop');
        if (!c) {
            c = document.createElement('div');
            c.id = 'toastAvisosTop';
            c.className = 'toast-container position-fixed top-0 end-0 p-3';
            c.style.zIndex = 2100;
            document.body.appendChild(c);
        }
        return c;
    }

    function mostrar(c) {
        const cont = contenedorAvisos();
        const aprobada = c.estado === 'aprobada';
        const el = document.createElement('div');
        el.className = 'toast border-0 shadow-lg ' + (aprobada ? 'text-bg-success' : 'text-bg-danger');
        el.setAttribute('role', 'alert');
        el.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <div class="fw-bold"><i class="bi ${aprobada ? 'bi-check-circle-fill' : 'bi-x-circle-fill'} me-1"></i>Crédito ${aprobada ? 'AUTORIZADO' : 'RECHAZADO'}</div>
                    <div class="small">Cliente: <strong>${esc(c.cliente)}</strong></div>
                    <div class="small">Monto: <strong>$${(c.monto || 0).toFixed(2)}</strong>${c.dias ? ' · Plazo: <strong>' + c.dias + ' días</strong>' : ''}</div>
                    <div class="small">Sucursal: <strong>${esc(c.sucursal)}</strong></div>
                    ${c.autorizador ? '<div class="small">' + (aprobada ? 'Autorizó: ' : 'Resolvió: ') + esc(c.autorizador) + '</div>' : ''}
                    <div class="small mt-1">${aprobada ? 'Ya puedes registrar la venta a crédito.' : 'Cambia la forma de pago para continuar con la venta.'}</div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>`;
        cont.appendChild(el);
        el.addEventListener('hidden.bs.toast', () => el.remove());
        new bootstrap.Toast(el, { autohide: false }).show();
        beep();
    }

    function consultar() {
        fetch(URL_ESTADO + '?ids=' + observadas.join(','), { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data || !Array.isArray(data.creditos)) return;
                data.creditos.forEach(c => {
                    if (c.estado === 'pendiente') {
                        if (!observadas.includes(c.id)) observadas.push(c.id);
                        return;
                    }
                    if (observadas.includes(c.id)) {
                        observadas = observadas.filter(i => i !== c.id);
                        if (c.estado !== 'cancelada') mostrar(c); // 'cancelada' = el cajero la reemplazó
                    }
                });
                guardar();
            })
            .catch(() => {});
    }

    consultar();
    setInterval(consultar, 6000);
})();
</script>