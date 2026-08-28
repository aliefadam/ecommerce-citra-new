<script>
    (function() {
        const form = document.getElementById('bannerForm');
        const typeSelect = document.querySelector('select[name="type"]');
        const hintText = document.getElementById('bannerSizeHintText');
        const input = document.getElementById('bannerImageFile');
        const preview = document.getElementById('bannerImagePreview');
        const wrap = document.getElementById('bannerImagePreviewWrap');
        const label = document.getElementById('bannerImagePreviewLabel');
        const submit = form?.querySelector('button[type="submit"]');
        const emptyLabel = @js($emptyLabel ?? 'Pilih gambar...');
        let originalFile = null;
        let previewUrl = null;
        let processVersion = 0;

        if (!form || !typeSelect || !input || !preview || !label || !submit) return;

        function outputSize() {
            return typeSelect.value === 'side' ? [800, 350] : [1600, 700];
        }

        function formatBytes(bytes) {
            if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        }

        function setProcessing(processing) {
            submit.disabled = processing;
            submit.classList.toggle('opacity-60', processing);
            submit.classList.toggle('cursor-wait', processing);
        }

        function loadImage(file) {
            return new Promise((resolve, reject) => {
                const url = URL.createObjectURL(file);
                const image = new Image();
                image.onload = () => {
                    URL.revokeObjectURL(url);
                    resolve(image);
                };
                image.onerror = () => {
                    URL.revokeObjectURL(url);
                    reject(new Error('File gambar tidak dapat dibaca.'));
                };
                image.src = url;
            });
        }

        function canvasToWebp(canvas) {
            return new Promise((resolve, reject) => {
                canvas.toBlob(
                    blob => blob && blob.type === 'image/webp'
                        ? resolve(blob)
                        : reject(new Error('Browser tidak mendukung konversi WebP.')),
                    'image/webp',
                    0.82
                );
            });
        }

        async function processImage(file) {
            const version = ++processVersion;
            setProcessing(true);
            label.classList.remove('text-red-500');
            label.textContent = 'Menyiapkan WebP...';

            try {
                const image = await loadImage(file);
                const [width, height] = outputSize();
                const scale = Math.max(width / image.naturalWidth, height / image.naturalHeight);
                const drawWidth = image.naturalWidth * scale;
                const drawHeight = image.naturalHeight * scale;
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const context = canvas.getContext('2d', { alpha: false });
                context.imageSmoothingEnabled = true;
                context.imageSmoothingQuality = 'high';
                context.drawImage(
                    image,
                    (width - drawWidth) / 2,
                    (height - drawHeight) / 2,
                    drawWidth,
                    drawHeight
                );

                const blob = await canvasToWebp(canvas);
                if (version !== processVersion) return;

                const baseName = file.name.replace(/\.[^.]+$/, '') || 'banner';
                const converted = new File([blob], `${baseName}.webp`, {
                    type: 'image/webp',
                    lastModified: Date.now(),
                });
                const transfer = new DataTransfer();
                transfer.items.add(converted);
                input.files = transfer.files;

                if (previewUrl) URL.revokeObjectURL(previewUrl);
                previewUrl = URL.createObjectURL(converted);
                preview.src = previewUrl;
                wrap?.classList.remove('hidden');
                label.textContent = `${file.name} • ${formatBytes(file.size)} → ${formatBytes(converted.size)} WebP`;
            } catch (error) {
                if (version !== processVersion) return;
                input.value = '';
                label.classList.add('text-red-500');
                label.textContent = error instanceof Error ? error.message : 'Gambar gagal diproses.';
            } finally {
                if (version === processVersion) setProcessing(false);
            }
        }

        input.addEventListener('change', function(event) {
            const file = event.target.files?.[0] || null;
            if (!file) {
                originalFile = null;
                label.textContent = emptyLabel;
                return;
            }

            originalFile = file;
            processImage(file);
        });

        typeSelect.addEventListener('change', function() {
            if (hintText) hintText.textContent = '1600 x 700 px (rasio 16:7)';
            if (originalFile) processImage(originalFile);
        });
    })();
</script>
