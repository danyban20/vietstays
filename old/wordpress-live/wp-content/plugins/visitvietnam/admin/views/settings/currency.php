<?php 
global $wpdb;

$pg_title   = 'Currency Settings';
$meta_title = vv_admin_meta_title( $pg_title );

$usd = gArrayItem($conversion_rates,'USD');
$vnd = gArrayItem($conversion_rates,'VND');
$nok = gArrayItem($conversion_rates,'NOK');
$eur = gArrayItem($conversion_rates,'EUR');

$site_currencies = vv_get_site_currencies();
$site_currency = vv_site_currency();


$host_currency = get_user_meta(get_current_user_id(),'vv_host_currency',true);
if($host_currency == '' ) $host_currency = 'VND';

$conversion_rates= json_decode(get_option('vv_conversion_rates_'.$host_currency),true);
if(!is_array($conversion_rates))$conversion_rates = [];

$run_api_confirm = esc_js( vv__( 'Automatically calculate currency via api?' ) );
?>
<form method="post">
    <input type="hidden" name="action" value="save_currency_settings" >
    <div class="row">
        <div class="col-md-6">
            <div class="card">
            </div>
        </div><!-- .col -->
    </div>
      <div class="au-card" style="overflow: auto;">
            <div class="form-group">
              <form method="post">
                <input type="hidden" name="action" value="save_default_currency" >
                  <label><?php vv_e( 'Default Currency' ); ?></label>
                    <select name="default_currency" class="form-control form-control-sm d-inline" style="width:calc(100% - 45px)" >
                        <?php foreach($site_currencies as $currency){
                            ?>
                            <option value="<?php echo esc_attr( $currency ); ?>" <?php echo($host_currency ==  $currency) ? 'selected': '' ?> ><?php echo esc_html( $currency ); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <button class="btn btn-primary d-inline" class="width:4tpx" ><i class="fa fa-check"></i></button>
                </form>
            </div>
            
<form method="post">
    <input type="hidden" name="action" value="save_currency_settings" >
            <table class="table table-borderless table-striped table-earning Customers_list">
                <thead>
            <tr>
                <th><?php vv_e( 'Currency' ); ?></th>
                <th><?php vv_e( 'Ex Rate FROM USD' ); ?></th>
            </tr>
            </thead>
            
            <?php 
            $value3 = gArrayItem($conversion_rates,$host_currency);
            foreach($site_currencies as $currency){
                $value = gArrayItem($conversion_rates,$currency);
                ?>
                <tr>
                    <td><?php echo esc_html( $currency ); ?></td>
                    <td>

                        <?php 
                         if($logged_user_role == 'administrator'){
                            ?>
                            <input type="text" name="<?php echo esc_attr( $currency ); ?>" value="<?php echo esc_attr( $value ); ?>" class="form-control form-control-sm" >
                            <?php
                        }else{
                            echo esc_html( number_format($value,2) );
                        }
                        ?>
                    </td>
                </tr>
                <?php 
            }
            ?>
        </table>
        <?php 
        if($logged_user_role == 'administrator'){
            ?>   
            <div class="mt-4">
                <button type="submit" class="btn btn-primary mr-4"><?php vv_e( 'Save Changes' ); ?></button>
                <a href="<?php echo esc_url( vv_admin_url().'?vv_action=update_currency' ); ?>" onclick="return confirm('<?php echo $run_api_confirm; ?>')"><?php vv_e( 'Run API' ); ?></a>
            </div>
            <?php
        }
        ?>
    </div>
</form>
<?php ob_start(); ?>
<script>
    $(document).ready(function(){
        $('select[name="default_currency"]').change(function(){
            $(this).closest('form').submit();
        });
    })
</script>
<?php $footer_codes .= ob_get_clean(); ?>
