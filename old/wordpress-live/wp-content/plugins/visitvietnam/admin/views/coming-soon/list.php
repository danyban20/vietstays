<?php
$feature = sanitize_text_field( GET_Request( 'feature' ) );
$label   = $feature !== '' ? str_replace( '-', ' ', $feature ) : 'module';
?>
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h1 class="h3 mb-3"><?php vv_e( 'Coming soon' ); ?></h1>
                <p class="text-muted mb-0">
                    <?php
                    printf(
                        esc_html( vv__( 'The %s section is defined in the Backend Nav v3 specification and will be implemented in a future phase.' ) ),
                        esc_html( ucwords( $label ) )
                    );
                    ?>
                </p>
                <a href="<?php echo esc_url( vv_admin_url( 'dashboard' ) ); ?>" class="btn btn-sm btn-primary mt-4"><?php vv_e( 'Back to dashboard' ); ?></a>
            </div>
        </div>
    </div>
</div>
