(() => {
    const editor = document.querySelector('[data-rich-editor]');
    if (!editor) return;

    const form = editor.closest('form');
    const toolbar = editor.closest('.rich-editor-frame')?.querySelector('.rich-toolbar');
    const hiddenValue = form?.querySelector('[data-rich-value]');
    const imagePicker = form?.querySelector('[data-rich-image-picker]');
    const csrf = form?.querySelector('[name="csrf_token"]')?.value || '';
    let savedRange = null;

    if (!form || !toolbar || !hiddenValue || !imagePicker) return;

    const syncValue = () => {
        hiddenValue.value = editor.innerHTML;
    };

    const rememberSelection = () => {
        const selection = window.getSelection();
        if (!selection || !selection.rangeCount) return;
        const range = selection.getRangeAt(0);
        if (editor.contains(range.commonAncestorContainer)) {
            savedRange = range.cloneRange();
        }
    };

    const restoreSelection = () => {
        editor.focus();
        const selection = window.getSelection();
        if (!selection) return;
        if (savedRange && editor.contains(savedRange.commonAncestorContainer)) {
            selection.removeAllRanges();
            selection.addRange(savedRange);
        } else {
            const range = document.createRange();
            range.selectNodeContents(editor);
            range.collapse(false);
            selection.removeAllRanges();
            selection.addRange(range);
        }
    };

    document.addEventListener('selectionchange', rememberSelection);
    editor.addEventListener('input', syncValue);

    toolbar.addEventListener('mousedown', event => {
        if (event.target.closest('button')) event.preventDefault();
    });

    toolbar.querySelectorAll('[data-rich-command]').forEach(button => {
        button.addEventListener('click', () => {
            restoreSelection();
            document.execCommand(button.dataset.richCommand, false, null);
            rememberSelection();
            syncValue();
        });
    });

    toolbar.querySelector('[data-rich-link]')?.addEventListener('click', () => {
        restoreSelection();
        const selection = window.getSelection();
        if (!selection || !selection.toString().trim()) {
            window.alert('Sorot teks yang akan dijadikan tautan terlebih dahulu.');
            return;
        }
        const href = window.prompt('Alamat tautan (https://...)');
        if (!href) return;
        const target = href.trim();
        const allowed = /^(?:https?:\/\/|mailto:|tel:)/i.test(target)
            || target.startsWith('#')
            || (target.startsWith('/') && !target.startsWith('//'));
        if (!allowed || /[\x00-\x20]/.test(target)) {
            window.alert('Gunakan tautan https://, mailto:, tel:, alamat lokal, atau penanda #.');
            return;
        }
        // Prompt steals focus and may clear the browser selection. Restore the
        // saved range before applying the link to the text the user selected.
        restoreSelection();
        document.execCommand('createLink', false, target);
        rememberSelection();
        syncValue();
    });

    toolbar.querySelector('[data-rich-image]')?.addEventListener('click', () => {
        rememberSelection();
        imagePicker.click();
    });

    imagePicker.addEventListener('change', async () => {
        const file = imagePicker.files?.[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            window.alert('Ukuran gambar harus kurang dari 5 MB.');
            imagePicker.value = '';
            return;
        }

        const payload = new FormData();
        payload.append('action', 'unggah_gambar');
        payload.append('csrf_token', csrf);
        payload.append('gambar', file);
        const imageAlt = window.prompt('Teks alternatif gambar', file.name.replace(/\.[^.]+$/, '')) || '';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: payload,
                credentials: 'same-origin',
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();
            if (!response.ok || !result.ok || typeof result.url !== 'string') {
                throw new Error(result.message || 'Gambar belum dapat diunggah.');
            }

            restoreSelection();
            const image = document.createElement('img');
            image.src = result.url;
            image.alt = imageAlt;
            image.loading = 'lazy';
            const selection = window.getSelection();
            if (selection?.rangeCount) {
                const range = selection.getRangeAt(0);
                range.deleteContents();
                range.insertNode(image);
                range.setStartAfter(image);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
                savedRange = range.cloneRange();
            } else {
                editor.appendChild(image);
            }
            syncValue();
        } catch (error) {
            window.alert(error instanceof Error ? error.message : 'Gambar belum dapat diunggah.');
        } finally {
            imagePicker.value = '';
        }
    });

    form.addEventListener('submit', event => {
        syncValue();
        if (editor.innerHTML.length > 30000) {
            event.preventDefault();
            window.alert('Detail terlalu panjang. Batasnya 30.000 karakter HTML.');
            editor.focus();
            return;
        }
        if (!editor.textContent.trim() && !editor.querySelector('img')) {
            event.preventDefault();
            window.alert('Detail wajib diisi.');
            editor.focus();
        }
    });
})();
