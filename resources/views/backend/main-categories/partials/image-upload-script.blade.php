<script>
    (function() {
        window.initFileDropzones?.();

        const form = document.getElementById('mainCategoryForm');
        const input = document.getElementById('mainCategoryImageFile');
        const preview = document.getElementById('mainCategoryImagePreview');
        const wrap = document.getElementById('mainCategoryImagePreviewWrap');
        const label = document.getElementById('mainCategoryImagePreviewLabel');
        const submit = form?.querySelector('button[type="submit"]');
        const emptyLabel = @js($emptyLabel ?? 'Pilih gambar...');
        let previewUrl = null;
        let processVersion = 0;
        let isProcessing = false;

        if (!form || !input || !preview || !wrap || !label || !submit) return;

        function formatBytes(bytes) {
            if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
            return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
        }

        function setProcessing(processing) {
            isProcessing = processing;
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
            label.textContent = 'Mengoptimalkan gambar...';

            try {
                const image = await loadImage(file);
                const scale = Math.min(600 / image.naturalWidth, 600 / image.naturalHeight, 1);
                const width = Math.max(1, Math.round(image.naturalWidth * scale));
                const height = Math.max(1, Math.round(image.naturalHeight * scale));
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const context = canvas.getContext('2d', { alpha: true });
                if (!context) throw new Error('Gambar gagal diproses oleh browser.');
                context.imageSmoothingEnabled = true;
                context.imageSmoothingQuality = 'high';
                context.drawImage(image, 0, 0, width, height);

                const blob = await canvasToWebp(canvas);
                if (version !== processVersion) return;

                const baseName = file.name.replace(/\.[^.]+$/, '') || 'kategori';
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
                wrap.classList.remove('hidden');
                label.textContent = `${file.name} · ${formatBytes(file.size)} → ${formatBytes(converted.size)} WebP`;
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
                label.textContent = emptyLabel;
                return;
            }

            processImage(file);
        });

        form.addEventListener('submit', function(event) {
            if (!isProcessing) return;
            event.preventDefault();
            label.textContent = 'Tunggu hingga gambar selesai dioptimalkan.';
        });
    })();
</script>
