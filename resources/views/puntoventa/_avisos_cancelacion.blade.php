{{-- Avisos al cajero cuando el gerente aprueba/rechaza una CANCELACIÓN de venta.
     Incluir con: @include('puntoventa._avisos_cancelacion')  (POS e Historial) --}}
<script>
(function () {
    const CLAVE = 'pos_cancel_watch_{{ auth()->id() }}';
    const URL_ESTADO = '{{ route("puntoventa.cancelaciones.estado") }}';
    const enHistorial = !!document.getElementById('modalReimpresionTicket');

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

    function mostrar(v) {
        const cont = contenedorAvisos();
        const aprobada = v.estado === 'aprobada';
        const el = document.createElement('div');
        el.className = 'toast border-0 shadow-lg ' + (aprobada ? 'text-bg-success' : 'text-bg-danger');
        el.setAttribute('role', 'alert');
        el.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <div class="fw-bold"><i class="bi ${aprobada ? 'bi-check-circle-fill' : 'bi-x-circle-fill'} me-1"></i>Cancelación ${aprobada ? 'APROBADA' : 'RECHAZADA'}</div>
                    <div class="small">Venta <strong>${esc(v.folio)}</strong> · ${esc(v.cliente)} · $${(v.total || 0).toFixed(2)}</div>
                    ${v.autorizador ? '<div class="small">' + (aprobada ? 'Autorizó: ' : 'Resolvió: ') + esc(v.autorizador) + '</div>' : ''}
                    <div class="small mt-1">${aprobada ? 'La venta fue cancelada y el stock regresó al inventario.' : 'La venta sigue vigente.'}</div>
                    ${enHistorial ? '<button type="button" class="btn btn-sm btn-light fw-bold mt-2" data-recargar>Actualizar lista</button>' : ''}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>`;
        cont.appendChild(el);
        el.querySelector('[data-recargar]')?.addEventListener('click', () => location.reload());
        el.addEventListener('hidden.bs.toast', () => el.remove());
        new bootstrap.Toast(el, { autohide: false }).show();
        beep();
    }

    function consultar() {
        fetch(URL_ESTADO + '?ids=' + observadas.join(','), { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data || !Array.isArray(data.ventas)) return;
                data.ventas.forEach(v => {
                    if (v.estado === 'pendiente') {
                        if (!observadas.includes(v.id)) observadas.push(v.id);
                        return;
                    }
                    if (observadas.includes(v.id)) {
                        observadas = observadas.filter(i => i !== v.id);
                        mostrar(v);
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