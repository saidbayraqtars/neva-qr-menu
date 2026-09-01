import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import collapse from '@alpinejs/collapse';
import designStudio from './preview';
import initSortableLists from './sortable';

window.Alpine = Alpine;

Alpine.plugin(intersect);
Alpine.plugin(collapse);
Alpine.data('designStudio', designStudio);

Alpine.start();

// Panel listelerinde sürükle-bırak sıralama (kategoriler / ürünler).
initSortableLists();
