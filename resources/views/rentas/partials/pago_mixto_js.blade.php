<script>
window.mixtoCallbacks = window.mixtoCallbacks || {};

function mixtoSuma(id) {
    const box = document.getElementById('mixto_' + id);
    if (!box) return 0;
    let s = 0;
    box.querySelectorAll('.mixto-monto').forEach(i => s += parseFloat(i.value) || 0);
    return Math.round(s * 100) / 100;
}

function mixtoCambio(id) {
    const box = document.getElementById('mixto_' + id);
    if (!box) return;
    box.querySelectorAll('.mixto-parte').forEach(p => {
        const necesitaRef = p.querySelector('.mixto-metodo').value !== 'efectivo';
        const wrap = p.querySelector('.mixto-ref-wrap');
        const ref = wrap.querySelector('input');
        wrap.style.display = necesitaRef ? 'block' : 'none';
        if (necesitaRef && !ref.disabled) ref.setAttribute('required', 'required');
        else { ref.removeAttribute('required'); if (!necesitaRef) ref.value = ''; }
    });
    const suma = mixtoSuma(id);
    document.getElementById('mixto_suma_' + id).textContent = '$' + suma.toFixed(2);
    if (typeof window.mixtoCallbacks[id] === 'function') window.mixtoCallbacks[id](suma);
}

// metodo = valor del select principal ('' si no aplica). Los campos deshabilitados no se envían.
function mixtoToggle(id, metodo) {
    const box = document.getElementById('mixto_' + id);
    if (!box) return;
    const activo = metodo === 'mixto';
    box.style.display = activo ? 'block' : 'none';
    box.querySelectorAll('input, select').forEach(el => el.disabled = !activo);
    mixtoCambio(id);
}

function mixtoValido(id, monto) {
    const box = document.getElementById('mixto_' + id);
    if (!box) return true;
    const metodos = [...box.querySelectorAll('.mixto-metodo')].map(s => s.value);
    const montos = [...box.querySelectorAll('.mixto-monto')].map(i => parseFloat(i.value) || 0);
    if (montos.some(v => v <= 0)) { alert('Pago mixto: captura el monto de ambos métodos.'); return false; }
    if (metodos[0] === metodos[1]) { alert('Pago mixto: elige dos métodos distintos.'); return false; }
    if (Math.abs(mixtoSuma(id) - monto) >= 0.01) {
        alert('Pago mixto: la suma de las dos partes ($' + mixtoSuma(id).toFixed(2) + ') debe ser igual al monto a registrar ($' + monto.toFixed(2) + ').');
        return false;
    }
    return true;
}
</script>