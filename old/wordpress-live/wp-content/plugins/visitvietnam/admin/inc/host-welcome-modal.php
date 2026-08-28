<?php
if ( ! isset( $logged_user_role ) || $logged_user_role !== 'partner' ) {
	return;
}
if ( intval( get_user_meta( get_current_user_id(), vvHostApplications::META_SHOW_WELCOME, true ) ) !== 1 ) {
	return;
}
?>
<div class="modal fade" id="hostWelcomeModal" tabindex="-1" role="dialog" aria-labelledby="hostWelcomeModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="hostWelcomeModalLabel">Welcome to Vietstays</h5>
                <button type="button" class="close host-welcome-skip" aria-label="Skip tour"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body pt-2">
                <div id="hostWelcomeCarousel" class="carousel slide" data-ride="false" data-interval="false">
                    <ol class="carousel-indicators mb-3">
                        <li data-target="#hostWelcomeCarousel" data-slide-to="0" class="active"></li>
                        <li data-target="#hostWelcomeCarousel" data-slide-to="1"></li>
                        <li data-target="#hostWelcomeCarousel" data-slide-to="2"></li>
                        <li data-target="#hostWelcomeCarousel" data-slide-to="3"></li>
                        <li data-target="#hostWelcomeCarousel" data-slide-to="4"></li>
                    </ol>
                    <div class="carousel-inner px-2">
                        <div class="carousel-item active">
                            <h4 class="mb-3">Your host portal is ready</h4>
                            <p class="mb-0">Thank you for joining Vietstays. This short tour highlights the main areas of your host backend so you know where to start.</p>
                        </div>
                        <div class="carousel-item">
                            <h4 class="mb-3">Apartments</h4>
                            <p class="mb-0">After approval, add and manage your listings under <strong>Apartments</strong> in the sidebar. Our team can help you set up your first property.</p>
                        </div>
                        <div class="carousel-item">
                            <h4 class="mb-3">Bookings &amp; calendar</h4>
                            <p class="mb-0">Track reservations, check-ins, and availability from the bookings area. Keep your calendar up to date for smooth guest stays.</p>
                        </div>
                        <div class="carousel-item">
                            <h4 class="mb-3">Staff &amp; operations</h4>
                            <p class="mb-0">Invite cleaners and managers, assign tasks, and coordinate day-to-day operations as your portfolio grows.</p>
                        </div>
                        <div class="carousel-item">
                            <h4 class="mb-3">Get started</h4>
                            <p class="mb-0">Explore the sidebar at your own pace. You can reopen this tour anytime from <strong>Account</strong> settings.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-between">
                <button type="button" class="btn btn-link text-muted host-welcome-skip p-0">Skip tour</button>
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="hostWelcomePrev">Back</button>
                    <button type="button" class="btn btn-primary btn-sm" id="hostWelcomeNext">Next</button>
                    <button type="button" class="btn btn-primary btn-sm d-none host-welcome-skip" id="hostWelcomeDone">Get started</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ob_start(); ?>
<script>
jQuery(function ($) {
    var $modal = $('#hostWelcomeModal');
    var $carousel = $('#hostWelcomeCarousel');
    var totalSlides = $carousel.find('.carousel-item').length;

    function dismissWelcome() {
        $.post('<?php echo esc_url( vv_admin_url() ); ?>', {
            vv_action: 'host_welcome_dismiss',
            ajax: '1'
        }).always(function () {
            $modal.modal('hide');
        });
    }

    function updateNav() {
        var index = $carousel.find('.carousel-item.active').index();
        $('#hostWelcomePrev').toggleClass('d-none', index <= 0);
        $('#hostWelcomeNext').toggleClass('d-none', index >= totalSlides - 1);
        $('#hostWelcomeDone').toggleClass('d-none', index < totalSlides - 1);
    }

    $modal.on('shown.bs.modal', updateNav);
    $carousel.on('slid.bs.carousel', updateNav);

    $('#hostWelcomeNext').on('click', function () {
        $carousel.carousel('next');
    });
    $('#hostWelcomePrev').on('click', function () {
        $carousel.carousel('prev');
    });
    $('.host-welcome-skip').on('click', dismissWelcome);

    $modal.modal('show');
});
</script>
<?php
global $footer_codes;
$footer_codes .= ob_get_clean();
