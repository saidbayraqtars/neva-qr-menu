/**
 * Sürükle-bırak sıralama (kategoriler + ürünler).
 *
 * Kütüphane KULLANILMAZ: liste HTML5 drag&drop ile taşınır, dokunmatik
 * cihazlarda ise her satırdaki ▲ / ▼ düğmeleri aynı işi görür (mobil
 * tarayıcılar HTML5 sürüklemeyi desteklemez).
 *
 * Kullanım (Blade):
 *   <ul data-sortable data-sort-url="{{ route(...) }}" data-sort-extra='{"category_id":3}'>
 *       <li data-id="12" draggable="true"> … <button data-sort-up> … </li>
 */
function initList(list) {
    const url = list.dataset.sortUrl;
    if (!url) return;

    let extra = {};
    try {
        extra = list.dataset.sortExtra ? JSON.parse(list.dataset.sortExtra) : {};
    } catch (e) { /* geçersiz JSON: ek alan gönderilmez */ }

    let dragged = null;
    let saveTimer = null;

    const items = () => Array.from(list.querySelectorAll(':scope > [data-id]'));

    function persist() {
        clearTimeout(saveTimer);
        // Ard arda yapılan taşımalarda tek istek atılsın.
        saveTimer = setTimeout(() => {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ ...extra, order: items().map((el) => el.dataset.id) }),
            })
                .then((r) => flash(r.ok))
                .catch(() => flash(false));
        }, 350);
    }

    function flash(ok) {
        list.classList.toggle('sort-saved', ok);
        list.classList.toggle('sort-failed', !ok);
        setTimeout(() => list.classList.remove('sort-saved', 'sort-failed'), 1200);
    }

    function move(row, delta) {
        const all = items();
        const i = all.indexOf(row);
        const j = i + delta;
        if (i < 0 || j < 0 || j >= all.length) return;

        delta < 0 ? list.insertBefore(row, all[j]) : list.insertBefore(all[j], row);
        row.focus?.();
        persist();
    }

    list.addEventListener('dragstart', (e) => {
        const row = e.target.closest('[data-id]');
        if (!row || !list.contains(row)) return;
        dragged = row;
        row.classList.add('opacity-50');
        e.dataTransfer.effectAllowed = 'move';
        // Firefox sürüklemeyi başlatmak için veri bekler.
        e.dataTransfer.setData('text/plain', row.dataset.id);
    });

    list.addEventListener('dragend', () => {
        dragged?.classList.remove('opacity-50');
        dragged = null;
    });

    list.addEventListener('dragover', (e) => {
        if (!dragged) return;
        e.preventDefault();

        const over = e.target.closest('[data-id]');
        if (!over || over === dragged || !list.contains(over)) return;

        const rect = over.getBoundingClientRect();
        const after = e.clientY > rect.top + rect.height / 2;
        list.insertBefore(dragged, after ? over.nextSibling : over);
    });

    list.addEventListener('drop', (e) => {
        if (!dragged) return;
        e.preventDefault();
        persist();
    });

    list.addEventListener('click', (e) => {
        const up = e.target.closest('[data-sort-up]');
        const down = e.target.closest('[data-sort-down]');
        if (!up && !down) return;

        e.preventDefault();
        move(e.target.closest('[data-id]'), up ? -1 : 1);
    });
}

export default function initSortableLists(root = document) {
    root.querySelectorAll('[data-sortable]').forEach(initList);
}
