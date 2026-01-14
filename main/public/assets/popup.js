class Popup {
    static init() {
        if (!document.getElementById('popup-container')) {
            const container = document.createElement('div');
            container.id = 'popup-container';
            document.body.appendChild(container);
        }

        if (!document.getElementById('modal-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'modal-overlay';
            overlay.className = 'modal-overlay hidden';

            overlay.innerHTML = `
                <div class="modal-content">
                    <h3 id="modal-title">Confirm Action</h3>
                    <p id="modal-message">Are you sure?</p>
                    <div class="modal-actions">
                        <button id="modal-cancel" class="btn-cancel">Cancel</button>
                        <button id="modal-confirm" class="btn-confirm">Confirm</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            // Event delegation for closing
            // overlay.addEventListener('click', (e) => {
            //    if (e.target === overlay) Popup.closeModal();
            // });

            document.getElementById('modal-cancel').addEventListener('click', () => Popup.closeModal());
        }
    }

    static toast(message, type = 'success') {
        this.init();
        const container = document.getElementById('popup-container');

        const toast = document.createElement('div');
        toast.className = `popup-toast ${type}`;

        const icon = type === 'success' ? '✅' : '❌';

        toast.innerHTML = `
            <span class="toast-icon">${icon}</span>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;

        container.appendChild(toast);

        // Trigger reflow for animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    static confirm(message, onConfirm, title = 'Are you sure?') {
        this.init();
        const overlay = document.getElementById('modal-overlay');
        const titleEl = document.getElementById('modal-title');
        const msgEl = document.getElementById('modal-message');
        const confirmBtn = document.getElementById('modal-confirm');

        titleEl.textContent = title;
        msgEl.textContent = message;

        // Cloning button to remove old event listeners
        const newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);

        newBtn.addEventListener('click', () => {
            onConfirm();
            Popup.closeModal();
        });

        overlay.classList.remove('hidden');
        setTimeout(() => overlay.classList.add('show'), 10);
    }

    static closeModal() {
        const overlay = document.getElementById('modal-overlay');
        overlay.classList.remove('show');
        setTimeout(() => overlay.classList.add('hidden'), 300);
    }
}

// Auto-init on load
document.addEventListener('DOMContentLoaded', () => Popup.init());
