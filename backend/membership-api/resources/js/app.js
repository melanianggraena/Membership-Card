import { createIcons, icons } from 'lucide';

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
    const sidebar = document.querySelector('#sidebar');
    document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => sidebar?.classList.toggle('open'));
    document.querySelector('[data-toggle-password]')?.addEventListener('click', () => {
        const input = document.querySelector('#password');
        if (input) input.type = input.type === 'password' ? 'text' : 'password';
    });
    const backdrop = document.querySelector('#modalBackdrop');
    const closeModals = () => { document.querySelectorAll('.modal.open').forEach(m => m.classList.remove('open')); backdrop?.classList.remove('open'); };
    document.querySelectorAll('[data-modal-open]').forEach(button => button.addEventListener('click', () => {
        const modal = document.querySelector(`#${button.dataset.modalOpen}`);
        if (button.dataset.room && modal) {
            const room = JSON.parse(button.dataset.room);
            const form = modal.querySelector('[data-room-form]');
            form.action = `/rooms/${room.id}`;
            modal.querySelector('[data-method]').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            modal.querySelector('[data-room-title]').textContent = 'Edit Ruangan';
            ['room_name','description','access_price','capacity','status'].forEach(key => { if (form.elements[key]) form.elements[key].value = room[key] ?? ''; });
        }
        modal?.classList.add('open'); backdrop?.classList.add('open');
    }));
    document.querySelectorAll('[data-modal-close]').forEach(button => button.addEventListener('click', closeModals));
    backdrop?.addEventListener('click', closeModals);
});
