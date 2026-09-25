$(function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
        new bootstrap.Tooltip(element);
    });

    $(document).on('click', 'button.js-generate-surl', function () {
        var $button = $(this);
        var $slug = $('input[name="' + $button.data('slug-input') + '"]');
        var $source = $('input[name="' + $button.data('sluggable-input') + '"]');

        if ($slug.length === 0 || $source.length === 0 || $source.val() === '') {
            return;
        }

        if (typeof getSlug !== 'function') {
            console.warn('speakingurl не загружен — генерация ЧПУ недоступна');

            return;
        }

        $slug.val(getSlug($source.val()));
    });
});
