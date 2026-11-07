// Lightweight rich text editor for the admin blog forms.
// Markup expected:  .rte > .rte-toolbar + .rte-area[contenteditable] + textarea[hidden]
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.rte').forEach(function (rte) {
        const area = rte.querySelector('.rte-area');
        const field = rte.querySelector('textarea');
        const form = rte.closest('form');
        const buttons = rte.querySelectorAll('[data-cmd]');

        if (!area || !field) return;

        // Use <p> for new lines instead of <div>
        try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) {}

        // Load the stored content into the editor
        area.innerHTML = field.value;

        function runCommand(cmd, value) {
            area.focus();
            if (cmd === 'link') {
                const url = window.prompt('Paste the link (https://...)');
                if (!url) return;
                if (!/^(https?:\/\/|mailto:)/i.test(url)) {
                    alert('Links must start with http://, https:// or mailto:');
                    return;
                }
                document.execCommand('createLink', false, url);
            } else if (cmd === 'formatBlock') {
                document.execCommand('formatBlock', false, value);
            } else {
                document.execCommand(cmd, false, null);
            }
            refreshState();
        }

        buttons.forEach(function (btn) {
            // mousedown (not click) so the text selection is not lost
            btn.addEventListener('mousedown', function (e) {
                e.preventDefault();
                runCommand(btn.dataset.cmd, btn.dataset.value);
            });
        });

        function refreshState() {
            buttons.forEach(function (btn) {
                const cmd = btn.dataset.cmd;
                let on = false;
                try {
                    if (['bold', 'italic', 'underline', 'strikeThrough', 'insertUnorderedList', 'insertOrderedList'].indexOf(cmd) !== -1) {
                        on = document.queryCommandState(cmd);
                    } else if (cmd === 'formatBlock') {
                        on = (document.queryCommandValue('formatBlock') || '').toLowerCase() === btn.dataset.value;
                    }
                } catch (e) {}
                btn.classList.toggle('active', on);
            });
        }

        ['keyup', 'mouseup', 'focus'].forEach(function (ev) {
            area.addEventListener(ev, refreshState);
        });

        // Paste as plain text so no stray styles/fonts come in from Word or websites
        area.addEventListener('paste', function (e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
        });

        if (form) {
            form.addEventListener('submit', function (e) {
                field.value = area.innerHTML;
                if (area.textContent.trim() === '') {
                    e.preventDefault();
                    rte.classList.add('rte-error');
                    area.focus();
                }
            });
        }

        area.addEventListener('input', function () {
            rte.classList.remove('rte-error');
        });
    });

});
