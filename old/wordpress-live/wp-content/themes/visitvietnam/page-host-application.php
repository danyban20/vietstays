<?php
/*
 * Template Name: Host Application
 */

$submitted = intval( GET_Request( 'submitted' ) ) === 1;
$app_ref   = sanitize_text_field( GET_Request( 'ref' ) );

get_header();
?>

<style>
#host_application {
    background: #f7f3ea;
    padding: 60px 0 80px;
    min-height: 70vh;
}
#host_application .host-app-wrap {
    max-width: 820px;
    margin: 0 auto;
    background: #fff;
    border-radius: 16px;
    padding: 40px 36px;
    box-shadow: 0 8px 30px rgba(0, 64, 65, 0.08);
}
#host_application h2 {
    color: #004041;
    margin-bottom: 10px;
}
#host_application .host-app-intro {
    color: #555;
    font-size: 16px;
    margin-bottom: 30px;
}
#host_application label,
#host_application .form-group label {
    color: #004041;
}
#host_application .form-group label small {
    color: #004041;
    opacity: 0.75;
    font-weight: 400;
}
.host-app-step-title {
    color: #004041;
    font-size: 24px;
    margin-bottom: 8px;
}
.host-app-step-desc {
    color: #666;
    font-size: 15px;
    margin-bottom: 24px;
}
.host-app-progress {
    display: flex;
    gap: 6px;
    margin-bottom: 28px;
    overflow-x: auto;
    padding-bottom: 4px;
}
.host-app-progress-item {
    flex: 0 0 auto;
    text-align: center;
    min-width: 52px;
}
.host-app-progress-step {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #e8e4da;
    color: #888;
    font-weight: 600;
    font-size: 13px;
}
.host-app-progress-label {
    display: block;
    font-size: 10px;
    color: #888;
    margin-top: 4px;
    line-height: 1.2;
    max-width: 64px;
    margin-left: auto;
    margin-right: auto;
}
.host-app-progress-item.is-active .host-app-progress-step,
.host-app-progress-item.is-done .host-app-progress-step {
    background: #004041;
    color: #fff;
}
.host-app-progress-item.is-active .host-app-progress-label,
.host-app-progress-item.is-done .host-app-progress-label {
    color: #004041;
    font-weight: 600;
}
.host-app-choice-grid-3 {
    grid-template-columns: repeat(3, 1fr);
}
@media (max-width: 768px) {
    .host-app-choice-grid-3 { grid-template-columns: 1fr 1fr; }
}
.host-app-counter-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}
@media (min-width: 640px) {
    .host-app-counter-grid { grid-template-columns: repeat(4, 1fr); }
}
.host-app-photo-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}
.host-app-photo-preview img {
    width: 72px;
    height: 72px;
    object-fit: cover;
    border-radius: 8px;
}
.host-app-photo-more {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 72px;
    background: #f0ede6;
    border-radius: 8px;
    font-size: 13px;
    color: #666;
}
.host-app-price-field input {
    font-size: 22px;
    max-width: 200px;
}
.host-app-choice-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 640px) {
    .host-app-choice-grid { grid-template-columns: 1fr; }
}
.host-app-choice input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.host-app-choice-box {
    display: block;
    border: 2px solid #e4e4e4;
    border-radius: 12px;
    padding: 24px 20px;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    color: #004041;
}
.host-app-choice-box strong {
    display: block;
    font-size: 18px;
    margin-bottom: 6px;
}
.host-app-choice-box small {
    color: #777;
    font-size: 14px;
}
.host-app-choice input:checked + .host-app-choice-box,
.host-app-choice-box:hover {
    border-color: #FC780E;
    background: #fffaf5;
}
.host-app-check-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 16px;
}
@media (max-width: 640px) {
    .host-app-check-grid { grid-template-columns: 1fr; }
}
.host-app-check {
    font-size: 14px;
    color: #004041;
    font-weight: 400;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.host-app-check input {
    width: auto;
    margin: 0;
}
.host-app-actions {
    display: flex;
    gap: 12px;
    margin-top: 28px;
    flex-wrap: wrap;
}
.host-app-actions .btn-outline {
    background: transparent;
    color: #004041;
    border: 2px solid #004041;
}
.host-app-actions .btn-outline:hover {
    background: #004041;
    color: #fff;
}
.host-app-summary dl {
    margin: 0;
}
.host-app-summary dt {
    font-weight: 600;
    color: #004041;
    margin-top: 16px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.host-app-summary dd {
    margin: 4px 0 0;
    color: #333;
    font-size: 16px;
}
.host-app-alert {
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 20px;
    font-size: 15px;
}
.host-app-alert-error {
    background: #fdecea;
    color: #a94442;
    border: 1px solid #f5c6cb;
}
.host-app-success {
    text-align: center;
    padding: 20px 0;
}
.host-app-success-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #004041;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: 20px;
}
.host-app-success .app-ref {
    display: inline-block;
    background: #f0e8d5;
    color: #004041;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    margin: 12px 0 20px;
}
.host-app-hint {
    display: block;
    color: #888;
    font-size: 13px;
    margin-top: 6px;
}
.host-app-info-box {
    background: #f0f6f6;
    border-left: 4px solid #004041;
    padding: 14px 16px;
    margin-bottom: 24px;
    color: #004041;
    font-size: 15px;
    border-radius: 0 8px 8px 0;
}
.host-app-confirm {
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #e8e4da;
}
.host-app-confirm .host-app-check {
    font-size: 15px;
    line-height: 1.5;
}
.required { color: #FC780E; }
</style>

<div id="host_application">
    <div class="container">
        <div class="host-app-wrap">

            <?php if ( $submitted && $app_ref !== '' ) { ?>
                <div class="host-app-success">
                    <div class="host-app-success-icon">&#10003;</div>
                    <h2>Thank you for applying to Vietstays</h2>
                    <p class="host-app-intro">Our team will review your application and contact you within <?php echo intval( vvHostApplications::REVIEW_BUSINESS_DAYS ); ?> business days.</p>
                    <div class="app-ref"><?php echo esc_html( $app_ref ); ?></div>
                    <p>A confirmation has been sent to your email. Please include this application ID if you contact us.</p>
                    <p>Questions? Email <a href="mailto:<?php echo esc_attr( vv_admin_contact_email() ); ?>"><?php echo esc_html( vv_admin_contact_email() ); ?></a></p>
                    <p class="mt-4"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn">Back to homepage</a></p>
                </div>
            <?php } else { ?>
                <h2>Apply to become a Vietstays Host</h2>
                <p class="host-app-intro">Apply to list your apartment on Vietstays. Single-apartment hosts set up their listing step by step; it goes live after approval.</p>
                <?php include get_template_directory() . '/inc/host-application-form.php'; ?>
            <?php } ?>

        </div>
    </div>
</div>

<?php get_footer(); ?>
