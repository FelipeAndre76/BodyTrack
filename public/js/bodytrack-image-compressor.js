(function () {
    const presets = {
        socialPost: {
            mode: 'cover',
            maxBytes: 700 * 1024,
            quality: 0.86,
            minQuality: 0.62,
            square: { width: 1080, height: 1080 },
            portrait: { width: 1080, height: 1920 },
        },
        socialStatus: {
            mode: 'cover',
            maxBytes: 650 * 1024,
            quality: 0.86,
            minQuality: 0.62,
            width: 1080,
            height: 1920,
        },
        nutritionLabel: {
            mode: 'contain',
            maxBytes: 900 * 1024,
            quality: 0.9,
            minQuality: 0.72,
            width: 1400,
            height: 1800,
        },
        checkIn: {
            mode: 'contain',
            maxBytes: 650 * 1024,
            quality: 0.86,
            minQuality: 0.64,
            width: 1280,
            height: 1280,
        },
        avatar: {
            mode: 'cover',
            maxBytes: 260 * 1024,
            quality: 0.86,
            minQuality: 0.64,
            width: 512,
            height: 512,
        },
    };

    function selectedSocialPostSize(input, preset) {
        const modal = input.closest('#newPostModal') || document;
        const portraitChecked = modal.querySelector('input[value="portrait"]:checked');

        return portraitChecked ? preset.portrait : preset.square;
    }

    function getPreset(input) {
        const preset = presets[input.dataset.imagePreset];

        if (!preset) {
            return null;
        }

        if (input.dataset.imagePreset === 'socialPost') {
            return { ...preset, ...selectedSocialPostSize(input, preset) };
        }

        return preset;
    }

    function canvasToBlob(canvas, quality) {
        return new Promise((resolve) => {
            canvas.toBlob(resolve, 'image/jpeg', quality);
        });
    }

    function readImage(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const url = URL.createObjectURL(file);

            img.onload = function () {
                URL.revokeObjectURL(url);
                resolve(img);
            };

            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('Nao foi possivel carregar a imagem.'));
            };

            img.src = url;
        });
    }

    function drawImage(img, preset) {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d', { alpha: false });
        const targetRatio = preset.width / preset.height;
        const sourceRatio = img.naturalWidth / img.naturalHeight;
        let width = preset.width;
        let height = preset.height;
        let sourceX = 0;
        let sourceY = 0;
        let sourceWidth = img.naturalWidth;
        let sourceHeight = img.naturalHeight;

        if (preset.mode === 'contain') {
            const scale = Math.min(1, preset.width / img.naturalWidth, preset.height / img.naturalHeight);
            width = Math.max(1, Math.round(img.naturalWidth * scale));
            height = Math.max(1, Math.round(img.naturalHeight * scale));
        } else if (sourceRatio > targetRatio) {
            sourceWidth = Math.round(img.naturalHeight * targetRatio);
            sourceX = Math.round((img.naturalWidth - sourceWidth) / 2);
        } else {
            sourceHeight = Math.round(img.naturalWidth / targetRatio);
            sourceY = Math.round((img.naturalHeight - sourceHeight) / 2);
        }

        canvas.width = width;
        canvas.height = height;
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, width, height);
        context.drawImage(img, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, width, height);

        return canvas;
    }

    async function compressImage(file, preset) {
        if (!file || !file.type.startsWith('image/')) {
            return file;
        }

        const img = await readImage(file);
        const canvas = drawImage(img, preset);
        let quality = preset.quality;
        let blob = await canvasToBlob(canvas, quality);

        while (blob && blob.size > preset.maxBytes && quality > preset.minQuality) {
            quality = Math.max(preset.minQuality, quality - 0.08);
            blob = await canvasToBlob(canvas, quality);
        }

        if (!blob) {
            return file;
        }

        return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', {
            type: 'image/jpeg',
            lastModified: Date.now(),
        });
    }

    function setInputFile(input, file) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;
    }

    function setStatus(input, message) {
        const targetId = input.dataset.imageStatus;
        const target = targetId ? document.getElementById(targetId) : null;

        if (target) {
            target.textContent = message;
        }
    }

    document.addEventListener('change', async function (event) {
        const input = event.target;

        if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.dataset.imagePreset) {
            return;
        }

        if (input.dataset.compressed === '1') {
            input.dataset.compressed = '0';
            return;
        }

        const file = input.files?.[0];
        const preset = getPreset(input);

        if (!file || !preset) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        const originalSize = file.size;
        setStatus(input, 'Otimizando imagem...');

        try {
            const compressed = await compressImage(file, preset);
            setInputFile(input, compressed);
            input.dataset.compressed = '1';

            const originalKb = Math.round(originalSize / 1024);
            const compressedKb = Math.round(compressed.size / 1024);
            setStatus(input, `Imagem otimizada: ${originalKb}KB -> ${compressedKb}KB`);

            input.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (error) {
            setStatus(input, 'Nao foi possivel otimizar. A imagem original sera enviada.');
            input.dataset.compressed = '1';
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }, true);
})();
