import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import { TableKit } from '@tiptap/extension-table';

(() => {
    const editorElement = document.querySelector('[data-tiptap-editor]');
    if (!editorElement) return;

    const form = editorElement.closest('form');
    const toolbar = form?.querySelector('[data-tiptap-toolbar]');
    const hiddenValue = form?.querySelector('[data-tiptap-value]');
    const imagePicker = form?.querySelector('[data-tiptap-image-picker]');
    const csrf = form?.querySelector('[name="csrf_token"]')?.value || '';
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!form || !toolbar || !hiddenValue || !imagePicker) return;

    let editor = null;
    let uploading = false;
    let pendingRange = null;

    const getEditorHtml = () => {
        const output = document.createElement('div');
        output.innerHTML = editor.getHTML();
        output.querySelectorAll('table').forEach(table => {
            if (table.parentElement?.classList.contains('rich-table-scroll')) return;
            const wrapper = document.createElement('div');
            wrapper.className = 'rich-table-scroll';
            table.parentNode?.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        });
        return output.innerHTML;
    };

    const syncValue = () => {
        if (!editor) return;
        hiddenValue.value = getEditorHtml();
        refreshToolbar();
    };

    const activeCommands = {
        bold: () => editor.isActive('bold'),
        italic: () => editor.isActive('italic'),
        underline: () => editor.isActive('underline'),
        h2: () => editor.isActive('heading', { level: 2 }),
        h3: () => editor.isActive('heading', { level: 3 }),
        bulletList: () => editor.isActive('bulletList'),
        orderedList: () => editor.isActive('orderedList'),
        blockquote: () => editor.isActive('blockquote'),
        link: () => editor.isActive('link'),
    };

    function refreshToolbar() {
        if (!editor) return;
        toolbar.querySelectorAll('[data-tiptap-command]').forEach(button => {
            const command = button.dataset.tiptapCommand;
            const isActive = activeCommands[command]?.() === true;
            if (button.hasAttribute('aria-pressed')) {
                button.setAttribute('aria-pressed', String(isActive));
            }
            button.classList.toggle('is-active', isActive);
            if (command === 'undo') button.disabled = uploading || !editor.can().undo();
            else if (command === 'redo') button.disabled = uploading || !editor.can().redo();
            else button.disabled = uploading;
        });
    }

    const safeLink = value => {
        const trimmed = value.trim();
        if (!trimmed) return null;
        try {
            const parsed = new URL(trimmed, window.location.href);
            return ['http:', 'https:', 'mailto:', 'tel:'].includes(parsed.protocol) ? parsed.href : null;
        } catch {
            return null;
        }
    };

    const insertLink = () => {
        if (editor.isActive('link')) {
            editor.chain().focus().unsetLink().run();
            return;
        }
        const value = window.prompt('Masukkan alamat tautan:');
        if (value === null) return;
        const href = safeLink(value);
        if (!href) {
            window.alert('Alamat tautan harus menggunakan http, https, mailto, atau tel.');
            return;
        }
        if (editor.state.selection.empty) {
            editor.chain().focus().insertContent({
                type: 'text',
                text: href,
                marks: [{ type: 'link', attrs: { href } }],
            }).run();
        } else {
            editor.chain().focus().setLink({ href }).run();
        }
    };

    const openImagePicker = () => {
        const selection = editor.state.selection;
        pendingRange = { from: selection.from, to: selection.to };
        imagePicker.click();
    };

    const uploadImage = async file => {
        if (!allowedTypes.includes(file.type)) {
            throw new Error('Pilih gambar JPG, PNG, atau WebP.');
        }
        if (file.size < 8 || file.size > 5 * 1024 * 1024) {
            throw new Error('Ukuran gambar harus kurang dari 5 MB.');
        }

        const payload = new FormData();
        payload.append('action', 'unggah_gambar');
        payload.append('csrf_token', csrf);
        payload.append('gambar', file);
        const uploadUrl = new URL(form.getAttribute('action') || window.location.href, window.location.href);
        uploadUrl.searchParams.set('ajax', 'gambar');
        const response = await fetch(uploadUrl, {
            method: 'POST',
            body: payload,
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.toLowerCase().includes('application/json')) {
            throw new Error(response.status === 413
                ? 'Server menolak gambar karena ukurannya melewati batas unggah.'
                : `Server mengembalikan halaman HTML (HTTP ${response.status}), bukan jawaban unggah. Muat ulang halaman; jika masih terjadi, periksa sesi login dan batas unggah PHP.`);
        }
        let result;
        try {
            result = await response.json();
        } catch {
            throw new Error('Jawaban server untuk unggah gambar tidak valid. Coba muat ulang halaman.');
        }
        if (!response.ok || !result.ok || typeof result.url !== 'string') {
            throw new Error(result.message || 'Gambar belum dapat diunggah.');
        }
        return result.url;
    };

    const addImage = async file => {
        uploading = true;
        editor.setEditable(false);
        refreshToolbar();
        try {
            const src = await uploadImage(file);
            const alt = file.name.replace(/\.[^.]+$/, '').trim().slice(0, 255);
            editor.setEditable(true);
            editor.chain().focus().insertContentAt(pendingRange ?? editor.state.selection.from, {
                type: 'image',
                attrs: { src, alt },
            }).run();
        } catch (error) {
            editor.setEditable(true);
            window.alert(error instanceof Error ? error.message : 'Gambar belum dapat diunggah.');
        } finally {
            uploading = false;
            pendingRange = null;
            imagePicker.value = '';
            refreshToolbar();
        }
    };

    const initialContent = editorElement.innerHTML;
    editorElement.innerHTML = '';
    editor = new Editor({
        element: editorElement,
        content: initialContent,
        extensions: [
            StarterKit.configure({
                code: false,
                codeBlock: false,
                horizontalRule: false,
                strike: false,
                heading: { levels: [2, 3] },
                link: {
                    openOnClick: false,
                    autolink: true,
                    HTMLAttributes: { target: '_blank', rel: 'noopener noreferrer' },
                },
            }),
            Image.configure({
                inline: false,
                allowBase64: false,
                resize: {
                    enabled: true,
                    directions: ['top-left', 'top-right', 'bottom-left', 'bottom-right'],
                    minWidth: 100,
                    minHeight: 60,
                    alwaysPreserveAspectRatio: true,
                },
            }),
            TableKit.configure({ table: { resizable: false, renderWrapper: false } }),
        ],
        editorProps: {
            attributes: {
                class: 'tiptap-content',
                role: 'textbox',
                'aria-label': 'Narasi berita detail',
                'aria-multiline': 'true',
            },
        },
        onUpdate: syncValue,
        onSelectionUpdate: refreshToolbar,
    });

    toolbar.addEventListener('mousedown', event => {
        if (event.target.closest('button')) event.preventDefault();
    });
    toolbar.addEventListener('click', event => {
        const button = event.target.closest('[data-tiptap-command]');
        if (!button || button.disabled) return;
        switch (button.dataset.tiptapCommand) {
            case 'bold': editor.chain().focus().toggleBold().run(); break;
            case 'italic': editor.chain().focus().toggleItalic().run(); break;
            case 'underline': editor.chain().focus().toggleUnderline().run(); break;
            case 'h2': editor.chain().focus().toggleHeading({ level: 2 }).run(); break;
            case 'h3': editor.chain().focus().toggleHeading({ level: 3 }).run(); break;
            case 'bulletList': editor.chain().focus().toggleBulletList().run(); break;
            case 'orderedList': editor.chain().focus().toggleOrderedList().run(); break;
            case 'blockquote': editor.chain().focus().toggleBlockquote().run(); break;
            case 'link': insertLink(); break;
            case 'image': openImagePicker(); break;
            case 'table': {
                const rows = Number(window.prompt('Jumlah baris tabel (1–10):', '3'));
                if (!Number.isInteger(rows) || rows < 1 || rows > 10) return;
                const columns = Number(window.prompt('Jumlah kolom tabel (1–8):', '3'));
                if (!Number.isInteger(columns) || columns < 1 || columns > 8) return;
                editor.chain().focus().insertTable({ rows, cols: columns, withHeaderRow: true }).run();
                break;
            }
            case 'undo': editor.chain().focus().undo().run(); break;
            case 'redo': editor.chain().focus().redo().run(); break;
        }
        refreshToolbar();
    });

    imagePicker.addEventListener('change', () => {
        const file = imagePicker.files?.[0];
        if (file) void addImage(file);
        else pendingRange = null;
    });

    form.addEventListener('submit', event => {
        if (uploading) {
            event.preventDefault();
            window.alert('Tunggu sampai unggahan gambar selesai.');
            return;
        }
        syncValue();
        if (hiddenValue.value.length > 30000) {
            event.preventDefault();
            window.alert('Detail terlalu panjang. Batasnya 30.000 karakter HTML.');
            editor.commands.focus();
            return;
        }
        if (!editor.getText().trim() && !editor.view.dom.querySelector('img, table')) {
            event.preventDefault();
            window.alert('Detail wajib diisi.');
            editor.commands.focus();
        }
    });

    syncValue();
})();
