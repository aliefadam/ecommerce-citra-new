<style>
    .file-dropzone {
        position: relative;
        display: flex;
        min-width: 0;
        min-height: 7rem;
        width: 100%;
        align-items: center;
        justify-content: center;
        gap: 0.875rem;
        padding: 1rem;
        overflow: hidden;
        border: 2px dashed #cbd5e1 !important;
        border-radius: 0.875rem !important;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%) !important;
        color: #475569;
        cursor: pointer;
        transition: border-color 160ms ease, background-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
    }

    .file-dropzone:hover,
    .file-dropzone:focus-within,
    .file-dropzone[data-dragging] {
        border-color: #3b82f6 !important;
        background: #eff6ff !important;
        box-shadow: 0 0 0 4px rgb(59 130 246 / 0.10);
    }

    .file-dropzone[data-dragging] {
        transform: translateY(-1px);
    }

    .file-dropzone[data-has-files] {
        border-color: #34d399 !important;
        background: #ecfdf5 !important;
    }

    .dark .file-dropzone {
        border-color: #475569 !important;
        background: linear-gradient(135deg, rgb(30 41 59 / 0.82) 0%, rgb(15 23 42 / 0.58) 100%) !important;
        color: #cbd5e1;
    }

    .dark .file-dropzone:hover,
    .dark .file-dropzone:focus-within,
    .dark .file-dropzone[data-dragging] {
        border-color: #60a5fa !important;
        background: rgb(30 58 138 / 0.20) !important;
    }

    .dark .file-dropzone[data-has-files] {
        border-color: #34d399 !important;
        background: rgb(6 78 59 / 0.22) !important;
    }

    .file-dropzone > input[type="file"] {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        opacity: 0 !important;
        overflow: hidden !important;
        border: 0 !important;
        display: block !important;
        pointer-events: none;
    }

    .file-dropzone__content {
        display: flex;
        min-width: 0;
        align-items: center;
        justify-content: center;
        gap: 0.875rem;
        text-align: left;
    }

    .file-dropzone__icon {
        display: inline-flex;
        width: 2.75rem;
        height: 2.75rem;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: #dbeafe;
        color: #2563eb;
    }

    .file-dropzone[data-has-files] .file-dropzone__icon {
        background: #d1fae5;
        color: #059669;
    }

    .dark .file-dropzone__icon {
        background: rgb(37 99 235 / 0.22);
        color: #93c5fd;
    }

    .file-dropzone__copy {
        min-width: 0;
    }

    .file-dropzone__title,
    .file-dropzone__meta {
        display: block;
        overflow-wrap: anywhere;
    }

    .file-dropzone__title {
        color: #334155;
        font-size: 0.8125rem;
        font-weight: 700;
        line-height: 1.25rem;
    }

    .dark .file-dropzone__title {
        color: #e2e8f0;
    }

    .file-dropzone__meta {
        margin-top: 0.125rem;
        color: #94a3b8;
        font-size: 0.6875rem;
        line-height: 1rem;
    }

    .file-dropzone__previews {
        display: none;
        flex: 0 0 auto;
        gap: 0.375rem;
    }

    .file-dropzone[data-has-image] .file-dropzone__previews {
        display: flex;
    }

    .file-dropzone__preview {
        width: 2.75rem;
        height: 2.75rem;
        border: 1px solid #dbeafe;
        border-radius: 0.625rem;
        background: #ffffff;
        object-fit: cover;
    }

    .file-dropzone__clear {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        display: none;
        width: 1.75rem;
        height: 1.75rem;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 9999px;
        background: rgb(255 255 255 / 0.92);
        color: #64748b;
        box-shadow: 0 1px 3px rgb(15 23 42 / 0.14);
        cursor: pointer;
    }

    .file-dropzone[data-has-files] .file-dropzone__clear {
        display: inline-flex;
    }

    .dark .file-dropzone__clear {
        background: #1e293b;
        color: #cbd5e1;
    }

    @media (max-width: 480px) {
        .file-dropzone {
            min-height: 6.5rem;
            padding: 0.875rem;
        }

        .file-dropzone__content {
            flex-direction: column;
            gap: 0.5rem;
            text-align: center;
        }
    }
</style>

<script>
    (function() {
        const selector = 'input[type="file"]:not([data-file-dropzone-ready])';
        const objectUrls = new WeakMap();

        function formatBytes(bytes) {
            if (!Number.isFinite(bytes) || bytes <= 0) return '0 KB';
            if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
            return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
        }

        function defaultTitle(input) {
            const accept = (input.accept || '').toLowerCase();
            if (accept.includes('pdf')) return 'Pilih dokumen PDF';
            if (accept.includes('xlsx') || accept.includes('xls')) return 'Pilih file Excel';
            if (accept.includes('image')) return input.multiple ? 'Pilih gambar' : 'Pilih gambar';
            return input.multiple ? 'Pilih beberapa file' : 'Pilih file';
        }

        function makeStandardContent(input, oldTitle) {
            const content = document.createElement('div');
            content.className = 'file-dropzone__content';
            content.setAttribute('data-file-dropzone-content', '');
            content.innerHTML = `
                <span class="file-dropzone__icon" aria-hidden="true">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                </span>
                <span class="file-dropzone__previews" data-file-dropzone-previews></span>
                <span class="file-dropzone__copy">
                    <span class="file-dropzone__title" data-file-dropzone-title></span>
                    <span class="file-dropzone__meta" data-file-dropzone-meta>Klik untuk memilih atau tarik file ke area ini</span>
                </span>
                <button class="file-dropzone__clear" type="button" data-file-dropzone-clear aria-label="Hapus file terpilih">&times;</button>
            `;

            const title = content.querySelector('[data-file-dropzone-title]');
            title.textContent = oldTitle?.textContent?.trim() || defaultTitle(input);
            if (oldTitle?.id) title.id = oldTitle.id;
            return content;
        }

        function clearObjectUrls(input) {
            (objectUrls.get(input) || []).forEach((url) => URL.revokeObjectURL(url));
            objectUrls.set(input, []);
        }

        function syncDropzone(input, zone) {
            if (zone.dataset.fileDropzonePreserve === 'true') return;

            const files = Array.from(input.files || []);
            const title = zone.querySelector('[data-file-dropzone-title]');
            const meta = zone.querySelector('[data-file-dropzone-meta]');
            const previews = zone.querySelector('[data-file-dropzone-previews]');
            zone.toggleAttribute('data-has-files', files.length > 0);
            zone.removeAttribute('data-has-image');
            clearObjectUrls(input);
            if (previews) previews.replaceChildren();

            if (!files.length) {
                if (title) title.textContent = defaultTitle(input);
                if (meta) meta.textContent = 'Klik untuk memilih atau tarik file ke area ini';
                return;
            }

            if (title) {
                title.textContent = files.length === 1
                    ? files[0].name
                    : `${files.length} file dipilih`;
            }
            if (meta) meta.textContent = files.map((file) => formatBytes(file.size)).join(' · ');

            const imageFiles = files.filter((file) => file.type.startsWith('image/')).slice(0, 4);
            if (previews && imageFiles.length) {
                const urls = imageFiles.map((file) => URL.createObjectURL(file));
                objectUrls.set(input, urls);
                urls.forEach((url, index) => {
                    const image = document.createElement('img');
                    image.src = url;
                    image.alt = `Preview ${imageFiles[index].name}`;
                    image.className = 'file-dropzone__preview';
                    previews.appendChild(image);
                });
                zone.setAttribute('data-has-image', '');
            }
        }

        function assignDroppedFiles(input, files) {
            const transfer = new DataTransfer();
            Array.from(files || []).slice(0, input.multiple ? undefined : 1).forEach((file) => transfer.items.add(file));
            input.files = transfer.files;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function attachInteractions(input, zone) {
            if (zone.dataset.fileDropzoneBound === 'true') return;
            zone.dataset.fileDropzoneBound = 'true';

            if (zone.tagName !== 'LABEL') {
                zone.addEventListener('click', (event) => {
                    if (!event.target.closest('[data-file-dropzone-clear]')) input.click();
                });
            }
            zone.addEventListener('keydown', (event) => {
                if ((event.key === 'Enter' || event.key === ' ') && !event.target.closest('[data-file-dropzone-clear]')) {
                    event.preventDefault();
                    input.click();
                }
            });
            ['dragenter', 'dragover'].forEach((name) => zone.addEventListener(name, (event) => {
                event.preventDefault();
                zone.setAttribute('data-dragging', '');
            }));
            ['dragleave', 'drop'].forEach((name) => zone.addEventListener(name, (event) => {
                event.preventDefault();
                zone.removeAttribute('data-dragging');
            }));
            zone.addEventListener('drop', (event) => assignDroppedFiles(input, event.dataTransfer?.files));
            zone.querySelector('[data-file-dropzone-clear]')?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
            input.addEventListener('change', () => syncDropzone(input, zone));
        }

        function enhanceInput(input) {
            input.dataset.fileDropzoneReady = 'true';
            const anchorSelector = input.dataset.fileDropzoneAnchor;
            const anchor = anchorSelector ? document.querySelector(anchorSelector) : null;
            const associatedLabel = input.id ? document.querySelector(`label[for="${CSS.escape(input.id)}"]`) : null;
            let zone = input.closest('[data-file-dropzone]') || input.closest('label') || associatedLabel;
            const preserve = input.dataset.dropzonePreserve === 'true';

            if (!zone) {
                zone = document.createElement('div');
                (anchor || input).before(zone);
                if (anchor) anchor.remove();
            }

            zone.setAttribute('data-file-dropzone', '');
            zone.classList.add('file-dropzone');
            zone.tabIndex = 0;
            zone.setAttribute('role', 'button');
            zone.setAttribute('aria-label', defaultTitle(input));

            if (preserve) {
                zone.dataset.fileDropzonePreserve = 'true';
                if (!zone.contains(input)) zone.prepend(input);
            } else {
                const oldTitle = zone.querySelector('[id$="PreviewLabel"], [data-file-dropzone-title]');
                Array.from(zone.children).forEach((child) => {
                    if (child !== input) child.remove();
                });
                if (!zone.contains(input)) zone.prepend(input);
                zone.appendChild(makeStandardContent(input, oldTitle));
            }

            attachInteractions(input, zone);
            syncDropzone(input, zone);
        }

        function initFileDropzones(root = document) {
            root.querySelectorAll(selector).forEach(enhanceInput);
        }

        window.initFileDropzones = initFileDropzones;
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => initFileDropzones());
        } else {
            initFileDropzones();
        }

        new MutationObserver((mutations) => {
            mutations.forEach((mutation) => mutation.addedNodes.forEach((node) => {
                if (!(node instanceof Element)) return;
                if (node.matches?.(selector)) enhanceInput(node);
                initFileDropzones(node);
            }));
        }).observe(document.documentElement, { childList: true, subtree: true });
    })();
</script>
