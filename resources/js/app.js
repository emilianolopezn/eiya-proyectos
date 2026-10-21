document.querySelector('[data-password-toggle]')?.addEventListener('click', (event) => {
    const button = event.currentTarget;
    const input = document.getElementById('password');
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    button.setAttribute('aria-pressed', String(visible));
    button.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
});

document.querySelector('.login-form')?.addEventListener('submit', (event) => {
    const button = event.currentTarget.querySelector('[type="submit"]');
    button.disabled = true;
    button.querySelector('span').textContent = 'Iniciando sesión…';
});

window.addEventListener('pageshow', () => {
    const button = document.querySelector('.login-submit');
    if (button) {
        button.disabled = false;
        button.querySelector('span').textContent = 'Iniciar sesión';
    }
});

let activeFilter = 'all';
const projectSearch = document.getElementById('project-search');
const normalize = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es');
const filterProjects = () => {
    const term = normalize(projectSearch?.value.trim() ?? '');
    let visibleCount = 0;
    document.querySelectorAll('[data-project-row]').forEach((row) => {
        const visible = (activeFilter === 'all' || row.dataset.state === activeFilter)
            && normalize(row.dataset.search).includes(term);
        row.hidden = !visible;
        if (visible) visibleCount++;
    });
    const empty = document.querySelector('.empty-projects');
    if (empty) empty.hidden = visibleCount > 0;
};

document.querySelectorAll('[data-filter]').forEach((button) => {
    button.addEventListener('click', () => {
        activeFilter = button.dataset.filter;
        document.querySelectorAll('[data-filter]').forEach((tab) => {
            const selected = tab === button;
            tab.classList.toggle('active', selected);
            tab.setAttribute('aria-pressed', String(selected));
        });
        filterProjects();
    });
});
projectSearch?.addEventListener('input', filterProjects);

document.querySelectorAll('[data-dialog-open]').forEach((button) => {
    button.addEventListener('click', () => {
        closeSidebar();
        document.getElementById(button.dataset.dialogOpen)?.showModal();
    });
});
document.querySelectorAll('[data-dialog-close]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog').close());
});
document.querySelectorAll('dialog').forEach((dialog) => {
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const bounds = dialog.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) {
            dialog.close();
        }
    });
});

const sidebarToggle = document.querySelector('[data-sidebar-open]');
const sidebar = document.getElementById('sidebar');
const closeSidebar = () => {
    document.body.classList.remove('sidebar-open');
    sidebarToggle?.setAttribute('aria-expanded', 'false');
    if (sidebar) sidebar.inert = window.innerWidth <= 760;
};
const syncSidebar = () => {
    if (window.innerWidth > 760) closeSidebar();
    if (sidebar) sidebar.inert = window.innerWidth <= 760 && !document.body.classList.contains('sidebar-open');
};
sidebarToggle?.addEventListener('click', () => {
    if (sidebar) sidebar.inert = false;
    document.body.classList.add('sidebar-open');
    sidebarToggle.setAttribute('aria-expanded', 'true');
    sidebar?.querySelector('a')?.focus();
});
document.querySelector('[data-sidebar-close]')?.addEventListener('click', closeSidebar);
document.querySelectorAll('.main-nav a').forEach((link) => link.addEventListener('click', closeSidebar));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
        closeSidebar();
        sidebarToggle?.focus();
    }
    if (event.key === 'Tab' && document.body.classList.contains('sidebar-open')) {
        const focusable = [...sidebar.querySelectorAll('a, button')];
        const first = focusable[0];
        const last = focusable.at(-1);
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }
});
window.addEventListener('resize', syncSidebar);
syncSidebar();

// Keep uploads local until the person chooses to save the form.
document.querySelectorAll('[data-image-upload]').forEach((upload) => {
    const input = upload.querySelector('[data-image-input]');
    const preview = upload.querySelector('[data-image-preview]');
    const originalPreview = preview.innerHTML;
    const message = upload.querySelector('[data-upload-message]');
    const originalMessage = message.textContent;
    const permission = upload.closest('form').querySelector('[data-photo-permission]');
    let previewUrl;
    input.addEventListener('change', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        input.setCustomValidity('');
        preview.innerHTML = originalPreview;
        message.textContent = originalMessage;
        const file = input.files[0];
        if (permission) {
            permission.hidden = !file;
            const checkbox = permission.querySelector('input');
            checkbox.required = Boolean(file);
            checkbox.checked = false;
        }
        if (!file) return;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 3 * 1024 * 1024) {
            const error = 'Elige una imagen JPG, PNG o WebP de hasta 3 MB.';
            input.setCustomValidity(error);
            message.textContent = error;
            input.reportValidity();
            return;
        }
        previewUrl = URL.createObjectURL(file);
        const image = document.createElement('img');
        image.src = previewUrl;
        image.alt = 'Vista previa de la imagen elegida';
        preview.replaceChildren(image);
        message.textContent = 'Así se verá al guardar.';
    });
});
document.querySelector('[data-form-errors]')?.focus();
