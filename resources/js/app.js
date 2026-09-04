import Alpine from 'alpinejs';
import TomSelect from 'tom-select';

window.Alpine = Alpine;
window.TomSelect = TomSelect;

Alpine.start();

/** Quita acentos, diéresis y normaliza a minúsculas para comparación. */
window.normalize = function normalize(str) {
    return String(str ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '');
};

/**
 * Inicializa Tom Select en todos los <select data-searchable>.
 * Se llama al cargar la página y puede llamarse de nuevo si el DOM cambia.
 */
function initSearchableSelects() {
    document.querySelectorAll('select[data-searchable]:not(.tomselected)').forEach(el => {
        const ts = new TomSelect(el, {
            maxOptions: null,
            allowEmptyOption: true,
            selectOnTab: true,
            dropdownParent: 'body',
            placeholder: el.dataset.placeholder ?? '',
            plugins: [],
            score: function (search) {
                const q = normalize(search);
                return function (item) {
                    return normalize(item.text).includes(q) ? 1 : 0;
                };
            },
        });

        // Cuando Tom Select cambia el valor, notificar al select subyacente
        // para que cualquier listener externo (formularios, etc.) lo detecte.
        ts.on('change', function (value) {
            el.value = value;
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
}

document.addEventListener('DOMContentLoaded', initSearchableSelects);
