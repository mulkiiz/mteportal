(function () {
    'use strict';

    var dialog = document.querySelector('[data-gallery-dialog]');
    if (!dialog) return;

    var thumbs = Array.prototype.slice.call(document.querySelectorAll('[data-gallery-open]'));
    var image = dialog.querySelector('[data-gallery-image]');
    var caption = dialog.querySelector('[data-gallery-caption]');
    var count = dialog.querySelector('[data-gallery-count]');
    var activeIndex = 0;

    function showImage(index) {
        activeIndex = (index + thumbs.length) % thumbs.length;
        var thumb = thumbs[activeIndex];
        var preview = thumb.querySelector('img');
        image.src = preview.src;
        image.alt = preview.alt;
        caption.textContent = thumb.querySelector('span').textContent;
        count.textContent = (activeIndex + 1) + ' / ' + thumbs.length;
    }

    thumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            showImage(Number(thumb.getAttribute('data-gallery-index')) || 0);
            dialog.showModal();
            dialog.querySelector('[data-gallery-close]').focus();
        });
    });

    dialog.querySelector('[data-gallery-prev]').addEventListener('click', function () {
        showImage(activeIndex - 1);
    });
    dialog.querySelector('[data-gallery-next]').addEventListener('click', function () {
        showImage(activeIndex + 1);
    });
    dialog.querySelector('[data-gallery-close]').addEventListener('click', function () {
        dialog.close();
    });
    dialog.addEventListener('click', function (event) {
        if (event.target === dialog) dialog.close();
    });
    document.addEventListener('keydown', function (event) {
        if (!dialog.open) return;
        if (event.key === 'ArrowLeft') showImage(activeIndex - 1);
        if (event.key === 'ArrowRight') showImage(activeIndex + 1);
    });
}());
