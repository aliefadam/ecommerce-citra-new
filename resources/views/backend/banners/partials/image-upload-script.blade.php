<script>
    (function() {
        const form = document.getElementById('bannerForm');
        const typeSelect = document.querySelector('select[name="type"]');
        const hintText = document.getElementById('bannerSizeHintText');
        const mobileHeroGuide = document.getElementById('bannerMobileHeroGuide');
        const targetInput = document.querySelector('input[name="target_url"]');
        const placements = {
            carousel: { size: [1600, 700], hint: '1600 x 700 px (rasio 16:7)', aspect: 'aspect-[16/7]', target: 'https://example.com' },
            side: { size: [800, 350], hint: '800 x 350 px (rasio 16:7)', aspect: 'aspect-[16/7]', target: 'https://example.com' },
            mobile_hero: { size: [864, 1080], hint: '864 x 1080 px (rasio 4:5)', aspect: 'aspect-[4/5]', target: '/app/search?sort=newest' },
        };
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

        function placement() {
            return placements[typeSelect.value] || placements.carousel;
        }

        function outputSize() {
            return placement().size;
        }

        function syncPlacement() {
            const current = placement();
            if (hintText) hintText.textContent = current.hint;
            mobileHeroGuide?.classList.toggle('hidden', typeSelect.value !== 'mobile_hero');
            if (targetInput) targetInput.placeholder = current.target;
            preview.classList.remove('aspect-[16/7]', 'aspect-[4/5]');
            preview.classList.add(current.aspect);
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

        // Hero Mobile is shown bottom-anchored, so never cut the sides or the bottom:
        // a wider source keeps its full width at the bottom and the top is filled with a blurred copy;
        // a taller source is trimmed from the top only.
        function drawMobileHero(context, image, width, height) {
            const fitScale = width / image.naturalWidth;
            const fitHeight = image.naturalHeight * fitScale;
            if (fitHeight >= height) {
                context.drawImage(image, 0, height - fitHeight, width, fitHeight);
                return;
            }

            const coverScale = height / image.naturalHeight;
            const coverWidth = image.naturalWidth * coverScale;
            context.save();
            context.filter = 'blur(28px)';
            context.drawImage(image, (width - coverWidth) / 2, 0, coverWidth, height);
            context.restore();
            context.drawImage(image, 0, height - fitHeight, width, fitHeight);
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
                if (typeSelect.value === 'mobile_hero') {
                    drawMobileHero(context, image, width, height);
                } else {
                    context.drawImage(
                        image,
                        (width - drawWidth) / 2,
                        (height - drawHeight) / 2,
                        drawWidth,
                        drawHeight
                    );
                }

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
            syncPlacement();
            if (originalFile) processImage(originalFile);
        });

        syncPlacement();
    })();
</script>
