/**
 * Form Submission Handler to prevent multiple submissions
 * Disables the submit button on form submit event.
 */
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            // Find submit button(s)
            const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');

            // If valid (HTML5 validation passes), disable button
            if (form.checkValidity()) {
                buttons.forEach(btn => {
                    // Store original text/width if needed, but for now just disable
                    // Optionally show loading state if it's a button element
                    if (btn.tagName === 'BUTTON') {
                        const originalText = btn.innerHTML;
                        btn.dataset.originalText = originalText;
                        btn.innerHTML = 'Processing...';
                        btn.style.opacity = '0.7';
                        btn.style.cursor = 'not-allowed';
                    }

                    btn.disabled = true;
                });

                // Safety timeout: Re-enable after 10 seconds in case of network error/no page reload
                setTimeout(() => {
                    buttons.forEach(btn => {
                        btn.disabled = false;
                        if (btn.tagName === 'BUTTON' && btn.dataset.originalText) {
                            btn.innerHTML = btn.dataset.originalText;
                            btn.style.opacity = '1';
                            btn.style.cursor = 'pointer';
                        }
                    });
                }, 10000);
            }
        });
    });
});
