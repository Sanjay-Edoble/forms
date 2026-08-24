/**
 * Edoble Forms — Core JavaScript
 * Toast notifications, AJAX helpers, theme toggle, utilities
 */
const Edoble = {
    /** CSRF token for AJAX requests */
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',

    /** ─── Toast Notifications ───────────────────────────── */
    toast(message, type = 'info', duration = 4000) {
        let container = document.querySelector('.edf-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'edf-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `edf-toast ${type}`;
        const icons = { success: 'bi-check-circle-fill', error: 'bi-exclamation-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
        toast.innerHTML = `
            <i class="bi ${icons[type] || icons.info}"></i>
            <span>${message}</span>
            <button class="edf-toast-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
        `;
        container.appendChild(toast);
        setTimeout(() => { toast.style.animation = 'toastSlideOut 0.3s forwards'; setTimeout(() => toast.remove(), 300); }, duration);
    },

    /** ─── AJAX Helper ────────────────────────────────────── */
    async fetch(url, options = {}) {
        const defaults = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': this.csrfToken,
            },
        };

        if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
            options.body = JSON.stringify(options.body);
        }

        if (options.body instanceof FormData) {
            delete defaults.headers['Content-Type'];
        }

        const config = { ...defaults, ...options, headers: { ...defaults.headers, ...(options.headers || {}) } };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok && !data.success) {
                throw new Error(data.message || `Request failed (${response.status})`);
            }
            return data;
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Edoble.fetch error:', error);
            }
            throw error;
        }
    },

    /** ─── Debounce ──────────────────────────────────────── */
    debounce(fn, ms = 300) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), ms);
        };
    },

    /** ─── Theme Toggle ──────────────────────────────────── */
    initThemeToggle() {
        const stored = localStorage.getItem('edf-theme');
        if (stored) document.documentElement.setAttribute('data-theme', stored);

        document.querySelectorAll('[data-toggle-theme]').forEach(btn => {
            btn.addEventListener('click', () => {
                const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', current);
                localStorage.setItem('edf-theme', current);
            });
        });
    },

    /** ─── Dropdown Toggle ───────────────────────────────── */
    initDropdowns() {
        document.addEventListener('click', (e) => {
            // Close all open dropdowns
            document.querySelectorAll('.edf-dropdown.open').forEach(d => {
                if (!d.contains(e.target)) d.classList.remove('open');
            });

            // Toggle clicked dropdown
            const trigger = e.target.closest('[data-dropdown]');
            if (trigger) {
                e.preventDefault();
                const dropdown = trigger.closest('.edf-dropdown');
                dropdown.classList.toggle('open');
            }
        });
    },

    /** ─── Modal ─────────────────────────────────────────── */
    openModal(id) {
        const overlay = document.getElementById(id);
        if (overlay) { overlay.classList.add('open'); document.body.style.overflow = 'hidden'; }
    },
    closeModal(id) {
        const overlay = document.getElementById(id);
        if (overlay) { overlay.classList.remove('open'); document.body.style.overflow = ''; }
    },
    initModals() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('edf-modal-overlay')) {
                e.target.classList.remove('open');
                document.body.style.overflow = '';
            }
            const closer = e.target.closest('[data-close-modal]');
            if (closer) {
                const modal = closer.closest('.edf-modal-overlay');
                if (modal) { modal.classList.remove('open'); document.body.style.overflow = ''; }
            }
        });
    },

    /** ─── Mobile Sidebar ────────────────────────────────── */
    initMobileSidebar() {
        document.querySelectorAll('[data-toggle-sidebar]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelector('.edf-sidebar')?.classList.toggle('open');
            });
        });
    },

    /** ─── Confirm Dialog ────────────────────────────────── */
    async confirm(message = 'Are you sure?') {
        return new Promise(resolve => {
            const id = 'confirm-' + Date.now();
            const html = `
                <div class="edf-modal-overlay open" id="${id}">
                    <div class="edf-modal" style="max-width:380px;">
                        <div class="edf-modal-body" style="text-align:center;padding:32px;">
                            <i class="bi bi-exclamation-triangle" style="font-size:36px;color:var(--edf-warning);margin-bottom:16px;display:block;"></i>
                            <p style="font-size:15px;margin:0 0 24px;color:var(--edf-text);">${message}</p>
                            <div style="display:flex;gap:8px;justify-content:center;">
                                <button class="edf-btn edf-btn-secondary" data-confirm="no">Cancel</button>
                                <button class="edf-btn edf-btn-danger" data-confirm="yes">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>`;
            document.body.insertAdjacentHTML('beforeend', html);
            const overlay = document.getElementById(id);
            overlay.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-confirm]');
                if (btn || e.target === overlay) {
                    overlay.remove();
                    document.body.style.overflow = '';
                    resolve(btn?.dataset.confirm === 'yes');
                }
            });
        });
    },

    /** ─── Initialize ────────────────────────────────────── */
    init() {
        this.initThemeToggle();
        this.initDropdowns();
        this.initModals();
        this.initMobileSidebar();
    }
};

// Auto-init on DOM ready
document.addEventListener('DOMContentLoaded', () => Edoble.init());
