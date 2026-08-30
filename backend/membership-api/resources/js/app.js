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
            modal.querySelector('[data-room-title]').textContent = 'Edit Outlet';
            ['room_name','description','access_price','capacity','status'].forEach(key => { if (form.elements[key]) form.elements[key].value = room[key] ?? ''; });
        }
        modal?.classList.add('open'); backdrop?.classList.add('open');
    }));
    document.querySelectorAll('[data-modal-close]').forEach(button => button.addEventListener('click', closeModals));
    backdrop?.addEventListener('click', closeModals);

    const notificationToggle = document.querySelector('[data-notification-toggle]');
    const notificationDropdown = document.querySelector('[data-notification-dropdown]');
    notificationToggle?.addEventListener('click', (event) => { event.stopPropagation(); notificationDropdown?.classList.toggle('open'); });
    document.addEventListener('click', (event) => { if (!event.target.closest('.notification-menu')) notificationDropdown?.classList.remove('open'); });
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const markRead = async (url) => fetch(url, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
    document.querySelector('[data-read-all]')?.addEventListener('click', async (event) => {
        await markRead(event.currentTarget.dataset.url);
        document.querySelectorAll('.notification-item.unread').forEach(item => item.classList.remove('unread'));
        document.querySelector('[data-unread-count]')?.remove(); event.currentTarget.remove();
    });
    document.querySelectorAll('[data-notification-id]').forEach(item => item.addEventListener('click', async (event) => {
        if (!item.classList.contains('unread')) return;
        event.preventDefault(); await markRead(item.dataset.readUrl); window.location.href = item.href;
    }));

    document.querySelectorAll('[data-outlet]').forEach(button => button.addEventListener('click', () => {
        const modal = document.querySelector('#outletModal'); if (!modal) return;
        const outlet = JSON.parse(button.dataset.outlet); const form = modal.querySelector('[data-outlet-form]');
        form.action = `/outlets/${outlet.id}`; modal.querySelector('[data-outlet-method]').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        modal.querySelector('[data-outlet-title]').textContent = 'Edit Outlet';
        ['outlet_code','outlet_name','description','status'].forEach(key => { if (form.elements[key]) form.elements[key].value = outlet[key] ?? ''; });
    }));

    const memberSelect = document.querySelector('[data-member-select]');
    const updateMemberSummary = () => {
        if (!memberSelect) return; const option = memberSelect.selectedOptions[0]; const hasMember = Boolean(option?.value);
        document.querySelector('[data-member-empty]')?.toggleAttribute('hidden', hasMember);
        document.querySelector('[data-member-content]')?.toggleAttribute('hidden', !hasMember);
        if (!hasMember) return;
        document.querySelector('[data-member-name]').textContent = option.dataset.name;
        document.querySelector('[data-member-code]').textContent = option.dataset.code;
        document.querySelector('[data-member-initial]').textContent = option.dataset.name.charAt(0).toUpperCase();
        document.querySelector('[data-member-balance]').textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(option.dataset.balance);
    };
    memberSelect?.addEventListener('change', updateMemberSummary); updateMemberSummary();
});
