import './bootstrap';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import 'flowbite';

window.Alpine = Alpine;
window.Sortable = Sortable;

Alpine.start();

// Kanban drag and drop (only when kanban columns exist)
document.addEventListener('DOMContentLoaded', () => {
  const columns = document.querySelectorAll('.kanban-column-cards');
  if (!columns.length) return;

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  columns.forEach((el) => {
    new Sortable(el, {
      group: 'todos',
      animation: 150,
      handle: '.kanban-drag-handle',
      filter: '.empty-placeholder',
      ghostClass: 'opacity-50',
      chosenClass: 'ring-2 ring-blue-500',
      dragClass: 'cursor-grabbing',
      onEnd(evt) {
        const todoId = evt.item.dataset.todoId;
        const newStatus = evt.to.dataset.status;
        if (!todoId || !newStatus) return;

        const url = evt.item.dataset.updateUrl;
        if (!url) return;

        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('_method', 'PATCH');
        formData.append('status', newStatus);
        formData.append('view', 'kanban');

        fetch(url, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
        })
          .then((res) => {
            if (!res.ok) throw new Error('Update failed');
            return res.json().catch(() => ({}));
          })
          .catch(() => {
            evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
          });
      },
    });
  });
});
