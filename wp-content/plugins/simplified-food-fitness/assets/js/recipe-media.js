(function ($) {
    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        return;
    }

    var data = window.sffRecipeMediaData || {};
    var registry = $.extend({}, data.items || {});

    data.coverId = parseInt(data.coverId, 10) || 0;
    data.galleryIds = Array.isArray(data.galleryIds)
        ? data.galleryIds.map(function (value) { return parseInt(value, 10) || 0; }).filter(Boolean)
        : [];

    var i18n = $.extend({
        coverTitle: 'Choose cover image',
        coverButton: 'Use as cover',
        galleryTitle: 'Add gallery images',
        galleryButton: 'Add images',
        noCover: 'No cover image selected.',
        noGallery: 'No gallery images selected.',
        remove: 'Remove image',
        imageFallback: 'Image'
    }, data.i18n || {});

    function ensureStored(item) {
        if (item && item.id) {
            registry[item.id] = item;
        }
    }

    function bestImageUrl(item, preferredSizes) {
        preferredSizes = preferredSizes || ['medium_large', 'medium', 'large', 'thumbnail', 'full'];
        if (!item) {
            return '';
        }
        if (item.sizes) {
            for (var i = 0; i < preferredSizes.length; i++) {
                var sizeKey = preferredSizes[i];
                if (item.sizes[sizeKey] && item.sizes[sizeKey].url) {
                    return item.sizes[sizeKey].url;
                }
            }
        }
        return item.url || '';
    }

    function renderCover(id) {
        var $preview = $('#sff-recipe-cover-preview');
        var $remove = $('.sff-recipe-cover-remove');
        var $input = $('#sff_recipe_cover_id');

        if (id && registry[id]) {
            var attachment = registry[id];
            var url = bestImageUrl(attachment, ['large', 'medium_large', 'medium', 'thumbnail', 'full']);
            var alt = attachment.alt || attachment.title || '';
            $preview.empty().append($('<img>', {
                src: url,
                alt: alt
            }));
            $input.val(id);
            $remove.prop('disabled', false);
        } else {
            $preview.empty().append($('<p>', {
                'class': 'sff-recipe-media__empty',
                text: i18n.noCover
            }));
            $input.val('');
            $remove.prop('disabled', true);
        }
    }

    function renderGallery(ids) {
        var $container = $('#sff-recipe-gallery-preview');
        var $clear = $('.sff-recipe-gallery-clear');
        var $input = $('#sff_recipe_gallery_ids');

        $container.empty();

        if (!ids.length) {
            $container.append($('<p>', {
                'class': 'sff-recipe-media__empty',
                text: i18n.noGallery
            }));
            $clear.prop('disabled', true);
            $input.val('');
            return;
        }

        ids.forEach(function (id) {
            if (!registry[id]) {
                var attachment = wp.media.attachment(id);
                if (attachment) {
                    attachment.fetch();
                    ensureStored(attachment.toJSON());
                }
            }

            var details = registry[id];
            var item = $('<div>', {
                'class': 'sff-recipe-media__gallery-item',
                'data-id': id
            });

            if (details) {
                var thumbUrl = bestImageUrl(details, ['thumbnail', 'medium', 'medium_large', 'large', 'full']);
                if (thumbUrl) {
                    item.append($('<img>', {
                        src: thumbUrl,
                        alt: details.alt || details.title || ''
                    }));
                } else {
                    item.append($('<span>', {
                        'class': 'sff-recipe-media__gallery-fallback',
                        text: i18n.imageFallback
                    }));
                }
            } else {
                item.append($('<span>', {
                    'class': 'sff-recipe-media__gallery-fallback',
                    text: i18n.imageFallback
                }));
            }

            item.append($('<button>', {
                type: 'button',
                'class': 'sff-recipe-gallery-remove',
                'aria-label': i18n.remove,
                text: '×'
            }));

            $container.append(item);
        });

        $clear.prop('disabled', false);
        $input.val(ids.join(','));
    }

    function setCover(id) {
        data.coverId = id || 0;
        renderCover(data.coverId);
    }

    function setGallery(ids) {
        ids = ids.filter(function (value, index, array) {
            return value && array.indexOf(value) === index;
        });
        data.galleryIds = ids;
        renderGallery(data.galleryIds);
    }

    $(document).on('click', '.sff-recipe-cover-select', function (event) {
        event.preventDefault();
        var frame = $(this).data('frame');

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: i18n.coverTitle,
            button: {
                text: i18n.coverButton
            },
            library: {
                type: 'image'
            },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            ensureStored(attachment);
            setCover(attachment.id);
        });

        $(this).data('frame', frame);
        frame.open();
    });

    $(document).on('click', '.sff-recipe-cover-remove', function (event) {
        event.preventDefault();
        if ($(this).prop('disabled')) {
            return;
        }
        setCover(0);
    });

    $(document).on('click', '.sff-recipe-gallery-add', function (event) {
        event.preventDefault();
        var frame = $(this).data('frame');

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: i18n.galleryTitle,
            button: {
                text: i18n.galleryButton
            },
            library: {
                type: 'image'
            },
            multiple: true
        });

        frame.on('select', function () {
            var selection = frame.state().get('selection');
            var ids = data.galleryIds.slice();

            selection.each(function (attachment) {
                var details = attachment.toJSON();
                ensureStored(details);
                if (ids.indexOf(details.id) === -1) {
                    ids.push(details.id);
                }
            });

            setGallery(ids);
        });

        $(this).data('frame', frame);
        frame.open();
    });

    $(document).on('click', '.sff-recipe-gallery-remove', function (event) {
        event.preventDefault();
        var $item = $(this).closest('.sff-recipe-media__gallery-item');
        var id = parseInt($item.data('id'), 10) || 0;
        if (!id) {
            return;
        }
        var ids = data.galleryIds.filter(function (value) {
            return value !== id;
        });
        setGallery(ids);
    });

    $(document).on('click', '.sff-recipe-gallery-clear', function (event) {
        event.preventDefault();
        if ($(this).prop('disabled')) {
            return;
        }
        setGallery([]);
    });

    $(function () {
        // Ensure registry contains localized items before rendering.
        Object.keys(registry).forEach(function (key) {
            var parsedKey = parseInt(key, 10);
            if (parsedKey && !registry[parsedKey]) {
                registry[parsedKey] = registry[key];
            }
        });
        renderCover(data.coverId);
        renderGallery(data.galleryIds);
    });
})(jQuery);
