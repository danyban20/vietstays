<?php
/**
 * Shared JS for apartment photo upload / reorder (wizard step 2 + manage photos tab).
 *
 * Expects: $apartment_id, $min_images, optional $images_wrap_id, $images_form_id, $require_min_on_save.
 */
$images_wrap_id        = isset( $images_wrap_id ) ? $images_wrap_id : 'apt2026ImagesWrap';
$images_form_id        = isset( $images_form_id ) ? $images_form_id : 'vvApt2026Step2';
$require_min_on_save   = isset( $require_min_on_save ) ? (bool) $require_min_on_save : false;
$disable_next_selector = isset( $disable_next_selector ) ? $disable_next_selector : '#apt2026NextBtn';
$building_id           = isset( $building_id ) ? intval( $building_id ) : 0;
$wizard_min_images     = isset( $wizard_min_images ) ? intval( $wizard_min_images ) : 0;
?>
<script>
jQuery(function($){
    var minImages = <?php echo intval( $min_images ); ?>;
    var wizardMinImages = <?php echo intval( $wizard_min_images ); ?>;
    var proceedMin = wizardMinImages > 0 ? wizardMinImages : minImages;
    var aptId = <?php echo intval( $apartment_id ); ?>;
    var buildingId = <?php echo intval( $building_id ); ?>;
    var wrapId = '<?php echo esc_js( $images_wrap_id ); ?>';
    var formId = '<?php echo esc_js( $images_form_id ); ?>';
    var $dropzone = $('#apt2026Dropzone');
    var $fileInput = $('#apt2026FileInput');
    var fileInputEl = $fileInput.get(0);
    var openingPicker = false;

    function openFilePicker(){
        if (openingPicker || !fileInputEl) return;
        openingPicker = true;
        fileInputEl.click();
        window.setTimeout(function(){ openingPicker = false; }, 400);
    }

    function updateCoverBadges(){
        var $grid = $('#' + wrapId + ' #vvApt2026ImagesGrid');
        if (!$grid.length) return;
        $grid.find('.vv-apt2026-image-card').each(function(i){
            var $card = $(this);
            $card.toggleClass('is-cover', i === 0);
            $card.find('.vv-apt2026-image-card__cover').remove();
            $card.find('.vv-apt2026-img-set-cover').remove();
            $card.find('.vv-apt2026-image-card__order').text(i + 1);
            if (i === 0) {
                $card.prepend('<span class="vv-apt2026-image-card__cover">★ <?php echo esc_js( vv__( 'Cover photo' ) ); ?></span>');
            } else {
                $card.find('.vv-apt2026-image-card__actions').prepend(
                    '<button type="button" class="btn btn-link btn-sm p-0 vv-apt2026-img-set-cover" title="<?php echo esc_js( vv__( 'Set as cover' ) ); ?>"><i class="fa fa-star"></i></button>'
                );
            }
        });
    }

    function getListingPhotoCount(){
        var aptCount = $('#' + wrapId + ' .vv-apt2026-image-card:not(.vv-apt2026-building-photo-card)').length;
        var buildingCount = $('#apt2026BuildingGalleryInputs input').length;
        return aptCount + buildingCount;
    }

    function updateImageCount(){
        var count = getListingPhotoCount();
        var $badge = $('#apt2026PhotoCountBadge');
        if ($badge.length) {
            $badge.text(count + ' / ' + minImages);
        }
        var $warning = $('#apt2026ImageWarning');
        if ($warning.length) {
            $warning.toggle(count < minImages);
            if (count < minImages) {
                $warning.text(count + ' / ' + minImages + ' <?php echo esc_js( vv__( 'listing photos (apartment + building) — full amount required before publishing.' ) ); ?>');
            }
        }
        var $nextBtn = $('<?php echo esc_js( $disable_next_selector ); ?>');
        if ($nextBtn.length) {
            $nextBtn.prop('disabled', count < proceedMin);
        }
        var $stepHint = $('#apt2026Step2Hint');
        if ($stepHint.length) {
            if (count < 1) {
                $stepHint.text('<?php echo esc_js( vv__( 'Upload at least 1 image to proceed' ) ); ?>');
            } else {
                $stepHint.text('');
            }
        }
    }

    function renderBuildingPhotosPreview(){
        var $section = $('#apt2026BuildingPhotosSection');
        var $grid = $('#apt2026BuildingPhotosGrid');
        if (!$grid.length) return;

        var html = '';
        $.each(buildingPicks, function(i, id){
            var item = null;
            $.each(buildingGalleryItems, function(j, row){
                if (parseInt(row.id, 10) === id) { item = row; return false; }
            });
            if (!item) return;
            html += '<div class="vv-apt2026-image-card vv-apt2026-building-photo-card" data-building-photo-id="'+id+'">' +
                '<span class="vv-apt2026-image-card__cover vv-apt2026-image-card__cover--building"><?php echo esc_js( vv__( 'Building' ) ); ?></span>' +
                '<img src="'+item.url+'" alt="">' +
                '<div class="vv-apt2026-image-card__body"><p class="small mb-0 text-center">'+(item.label || 'Building')+'</p></div></div>';
        });

        $grid.html(html);
        $section.toggleClass('d-none', buildingPicks.length === 0);
        updateImageCount();
    }

    function ensureUploadProgressUI(){
        var $progress = $('#apt2026UploadProgress');
        if ($progress.length) {
            return $progress;
        }
        var html = '<div id="apt2026UploadProgress" class="vv-apt2026-upload-progress d-none" role="status" aria-live="polite" aria-busy="false">' +
            '<div class="vv-apt2026-upload-progress__header">' +
                '<span class="vv-apt2026-upload-progress__spinner" aria-hidden="true"></span>' +
                '<span class="vv-apt2026-upload-progress__label"><?php echo esc_js( vv__( 'Uploading photos…' ) ); ?></span>' +
            '</div>' +
            '<div class="vv-apt2026-upload-progress__bar-wrap" aria-hidden="true">' +
                '<div class="vv-apt2026-upload-progress__bar"></div>' +
            '</div>' +
            '<p class="vv-apt2026-upload-progress__detail mb-0"></p>' +
        '</div>';
        if ($dropzone.length) {
            $dropzone.after(html);
        } else {
            $('#' + wrapId).before(html);
        }
        return $('#apt2026UploadProgress');
    }

    function setUploadControlsDisabled(disabled){
        $('#apt2026PickFiles').prop('disabled', disabled);
        $('<?php echo esc_js( $disable_next_selector ); ?>').prop('disabled', disabled ? true : getListingPhotoCount() < proceedMin);
        if ($dropzone.length) {
            $dropzone.toggleClass('is-uploading', disabled);
        }
    }

    function updateUploadProgress(currentIndex, totalFiles, filePercent, fileName){
        var $progress = ensureUploadProgressUI();
        var safePercent = Math.max(0, Math.min(100, filePercent || 0));
        var overall = totalFiles > 0 ? ((currentIndex + (safePercent / 100)) / totalFiles) * 100 : 0;
        overall = Math.max(0, Math.min(100, overall));

        $progress.removeClass('d-none is-complete is-error').attr('aria-busy', 'true');
        $progress.find('.vv-apt2026-upload-progress__bar').css('width', overall + '%');
        $progress.find('.vv-apt2026-upload-progress__label').text(
            '<?php echo esc_js( vv__( 'Uploading photos…' ) ); ?> (' + (currentIndex + 1) + ' / ' + totalFiles + ')'
        );

        var detail = fileName ? fileName : '';
        if (detail.length > 48) {
            detail = detail.substring(0, 45) + '…';
        }
        $progress.find('.vv-apt2026-upload-progress__detail').text(detail);
    }

    function finishUploadProgress(successCount, failCount){
        var $progress = ensureUploadProgressUI();
        $progress.attr('aria-busy', 'false');
        $progress.find('.vv-apt2026-upload-progress__bar').css('width', '100%');

        if (failCount > 0) {
            $progress.addClass('is-error');
            $progress.find('.vv-apt2026-upload-progress__label').text(
                successCount + ' <?php echo esc_js( vv__( 'uploaded' ) ); ?>, ' + failCount + ' <?php echo esc_js( vv__( 'failed' ) ); ?>'
            );
            $progress.find('.vv-apt2026-upload-progress__detail').text('<?php echo esc_js( vv__( 'Some images could not be uploaded. Check file size (max 10 MB) and format (JPG or PNG), then try again.' ) ); ?>');
        } else {
            $progress.addClass('is-complete');
            $progress.find('.vv-apt2026-upload-progress__label').text('<?php echo esc_js( vv__( 'Upload complete!' ) ); ?>');
            $progress.find('.vv-apt2026-upload-progress__detail').text(
                successCount === 1
                    ? '<?php echo esc_js( vv__( '1 photo added to your listing.' ) ); ?>'
                    : successCount + ' <?php echo esc_js( vv__( 'photos added to your listing.' ) ); ?>'
            );
        }

        window.setTimeout(function(){
            $progress.addClass('d-none').removeClass('is-complete is-error');
            $progress.find('.vv-apt2026-upload-progress__bar').css('width', '0%');
        }, failCount > 0 ? 5000 : 1800);
    }

    function refreshImages(){
        $.get('<?php echo esc_url( vv_admin_url() ); ?>?action=apt_wizard_images_html&id=' + aptId + '&t=' + Date.now(), function(html){
            $('#' + wrapId).html(html);
            bindImageControls();
            updateImageCount();
        });
    }

    function bindImageControls(){
        var $wrap = $('#' + wrapId);
        $wrap.off('click', '.vv-apt2026-image-card').on('click', '.vv-apt2026-image-card:not(.vv-apt2026-building-photo-card)', function(e){
            if ($(e.target).closest('button, a, input, .vv-apt2026-delete-img, .vv-apt2026-image-card__menu').length) return;
            $wrap.find('.vv-apt2026-image-card').removeClass('is-selected');
            $(this).addClass('is-selected');
        });
        $wrap.off('click', '.vv-apt2026-delete-img').on('click', '.vv-apt2026-delete-img', function(e){
            e.preventDefault();
            e.stopPropagation();
            if (!confirm('<?php echo esc_js( vv__( 'Remove this image?' ) ); ?>')) return;
            var $link = $(this);
            $link.prop('disabled', true).css('pointer-events', 'none');
            $.get($link.attr('href'))
                .done(function(resp){
                    var ok = true;
                    if (resp && typeof resp === 'object' && resp.success === false) {
                        ok = false;
                    }
                    if (!ok) {
                        alert('<?php echo esc_js( vv__( 'Could not remove this image. Please try again.' ) ); ?>');
                        $link.prop('disabled', false).css('pointer-events', '');
                        return;
                    }
                    refreshImages();
                })
                .fail(function(){
                    alert('<?php echo esc_js( vv__( 'Could not remove this image. Please try again.' ) ); ?>');
                    $link.prop('disabled', false).css('pointer-events', '');
                });
        });
        $wrap.off('click', '.vv-apt2026-img-move-left').on('click', '.vv-apt2026-img-move-left', function(e){
            e.preventDefault();
            var $card = $(this).closest('.vv-apt2026-image-card');
            var $prev = $card.prev('.vv-apt2026-image-card');
            if ($prev.length) { $card.insertBefore($prev); updateCoverBadges(); }
        });
        $wrap.off('click', '.vv-apt2026-img-move-right').on('click', '.vv-apt2026-img-move-right', function(e){
            e.preventDefault();
            var $card = $(this).closest('.vv-apt2026-image-card');
            var $next = $card.next('.vv-apt2026-image-card');
            if ($next.length) { $card.insertAfter($next); updateCoverBadges(); }
        });
        $wrap.off('click', '.vv-apt2026-img-set-cover').on('click', '.vv-apt2026-img-set-cover', function(e){
            e.preventDefault();
            var $card = $(this).closest('.vv-apt2026-image-card');
            var $grid = $card.closest('#vvApt2026ImagesGrid');
            if ($grid.length) { $grid.prepend($card); updateCoverBadges(); }
        });
    }

    function uploadFiles(fileList){
        if (!fileList || !fileList.length) return;
        var files = Array.prototype.slice.call(fileList);
        var uploaded = 0;
        var failed = 0;
        var $drop = $dropzone.length ? $dropzone : $(document.body);

        ensureUploadProgressUI();
        setUploadControlsDisabled(true);
        updateUploadProgress(0, files.length, 0, files[0] ? files[0].name : '');

        function uploadNext(){
            if (uploaded >= files.length) {
                setUploadControlsDisabled(false);
                finishUploadProgress(files.length - failed, failed);
                refreshImages();
                return;
            }
            var file = files[uploaded];
            var fd = new FormData();
            fd.append('file', file);
            updateUploadProgress(uploaded, files.length, 0, file.name);

            $.ajax({
                url: '<?php echo esc_url( vv_admin_url() ); ?>?action=upload_apartment_image&id=' + aptId,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                xhr: function(){
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        xhr.upload.addEventListener('progress', function(e){
                            if (e.lengthComputable) {
                                var pct = (e.loaded / e.total) * 100;
                                updateUploadProgress(uploaded, files.length, pct, file.name);
                            }
                        }, false);
                    }
                    return xhr;
                }
            }).done(function(){
                // uploaded successfully
            }).fail(function(){
                failed++;
            }).always(function(){
                uploaded++;
                uploadNext();
            });
        }
        uploadNext();
    }

    bindImageControls();
    updateCoverBadges();
    updateImageCount();

    $('#apt2026PickFiles').on('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        openFilePicker();
    });

    $dropzone.on('click', function(e){
        if (openingPicker) return;
        if ($(e.target).closest('#apt2026PickFiles, button, a, label, input').length) return;
        e.preventDefault();
        openFilePicker();
    });

    $fileInput.on('change', function(){
        var picked = Array.prototype.slice.call(this.files || []);
        this.value = '';
        uploadFiles(picked);
    });

    $dropzone.on('dragenter dragover', function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('is-dragover');
    });

    $dropzone.on('dragleave drop', function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('is-dragover');
    });

    $dropzone.on('drop', function(e){
        var dt = e.originalEvent && e.originalEvent.dataTransfer;
        if (!dt || !dt.files || !dt.files.length) return;
        uploadFiles(dt.files);
    });

    <?php if ( $require_min_on_save ) { ?>
    $('#' + formId).on('submit', function(e){
        var count = getListingPhotoCount();
        if (count < proceedMin) {
            e.preventDefault();
            alert('<?php echo esc_js( vv__( 'Please upload at least ' ) ); ?>' + proceedMin + ' <?php echo esc_js( vv__( 'images before continuing.' ) ); ?>');
        }
    });
    <?php } ?>

    <?php if ( $building_id > 0 ) { ?>
    var buildingPicks = [];
    var buildingGalleryItems = [];
    $('#apt2026BuildingGalleryInputs input').each(function(){ buildingPicks.push(parseInt($(this).val(),10)); });

    function renderBuildingGalleryHtml(items){
        var html = '';
        $.each(items || [], function(i, item){
            var sel = buildingPicks.indexOf(item.id) >= 0 ? ' is-selected' : '';
            var usage = item.host_count > 0 ? '<div class="small text-muted mt-1"><?php echo esc_js( vv__( 'Used by' ) ); ?> ' + item.host_count + ' <?php echo esc_js( vv__( 'hosts' ) ); ?></div>' : '';
            html += '<label class="'+sel+'"><input type="checkbox" class="d-none apt2026-bld-pick" value="'+item.id+'"><img src="'+item.url+'" alt=""><div class="vv-apt2026-building-grid__label">'+item.label+'</div>'+usage+'</label>';
        });
        return html || '<p class="text-muted mb-0"><?php echo esc_js( vv__( 'No building photos available.' ) ); ?></p>';
    }

    function loadBuildingGallery(){
        return $.getJSON('<?php echo esc_url( vv_admin_url() ); ?>?action=apt_wizard_building_gallery&building_id=' + buildingId, function(items){
            buildingGalleryItems = items || [];
            var html = renderBuildingGalleryHtml(items);
            $('#apt2026BuildingGrid, #apt2026BuildingGridInline').html(html);
            if (items.length && items[0].building_name) {
                $('#apt2026BuildingGalleryTitle').text(items[0].building_name + ' — ' + items.length + ' <?php echo esc_js( vv__( 'photos available' ) ); ?>');
            }
            updateBuildingPickCount();
        });
    }

    $('#apt2026OpenBuildingGallery').on('click', function(){
        var $modal = $('#apt2026BuildingModal');
        if (!$modal.parent().is('body')) {
            $modal.appendTo('body');
        }
        loadBuildingGallery().done(function(){
            $modal.modal('show');
        }).fail(function(){
            alert('<?php echo esc_js( vv__( 'Could not load building photos. Please try again.' ) ); ?>');
        });
    });

    $('a[href="#aptPhotoBuilding"]').on('shown.bs.tab', function(){
        loadBuildingGallery();
    });

    function updateBuildingPickCount(){
        var txt = buildingPicks.length + ' / 3 <?php echo esc_js( vv__( 'selected' ) ); ?>';
        $('#apt2026BuildingPickCount, #apt2026BuildingPickCountModal').text(txt);
        $('#apt2026BuildingPickBadge').text(txt);
    }

    $(document).on('click', '#apt2026BuildingGrid label, #apt2026BuildingGridInline label', function(e){
        e.preventDefault();
        var id = parseInt($(this).find('input').val(), 10);
        var idx = buildingPicks.indexOf(id);
        if (idx >= 0) {
            buildingPicks.splice(idx, 1);
            $(this).removeClass('is-selected');
        } else if (buildingPicks.length < 3) {
            buildingPicks.push(id);
            $(this).addClass('is-selected');
        }
        $('#apt2026BuildingGalleryInputs').empty();
        $.each(buildingPicks, function(i, v){ $('#apt2026BuildingGalleryInputs').append('<input type="hidden" name="building_gallery_ids[]" value="'+v+'">'); });
        updateBuildingPickCount();
        renderBuildingPhotosPreview();
        updateImageCount();
        $('#apt2026BuildingGrid label, #apt2026BuildingGridInline label').each(function(){
            var id = parseInt($(this).find('input').val(), 10);
            $(this).toggleClass('is-selected', buildingPicks.indexOf(id) >= 0);
        });
    });

    if (buildingPicks.length) {
        loadBuildingGallery().done(function(){
            renderBuildingPhotosPreview();
        });
    } else if ($('#apt2026BuildingGridInline').length) {
        loadBuildingGallery();
    }
    <?php } ?>
});
</script>
