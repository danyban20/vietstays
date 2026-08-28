<?php 
$firstname      = '';
$lastname       = '';
$location_id            = gArrayItem($location,'ID');
$district_id            = gArrayItem($location,'post_parent');
$name                   = gArrayItem($location,'post_title');
$district                   = [];
$location_facilities        = [];
$location_security_features = [];
$main_image             = 0;
$header_bg_image        = 0;
$header_text            = '';
if($location_id > 0){

    $location_facilities = get_post_meta($location_id,'facilities',true);

    if(!is_array($location_facilities)) $location_facilities = [];

    $location_security_features = get_post_meta($location_id,'security_features',true);

    if(!is_array($location_security_features)) $location_security_features = [];

    $main_image         = get_post_meta($location_id,'main_image',true);
    $header_bg_image    = get_post_meta($location_id,'header_bg_image',true);
    $header_text        = get_post_meta($location_id,'header_text',true);

    if($district_id > 0){
        $district = $this->district_class->get_district($district_id);
    }

}

if($name == '')     $name = POST_Request('name');

$facilities         = vv_get_facilities(['type' => 'location']);
$security_features  = vv_get_security_features(['type' => 'location']);

//echo print_r_pre($location);

?>
<form method="post" enctype="multipart/form-data" >
    <input type="hidden" name="vv_action" value="save_location">
    <input type="hidden" name="location_id" value="<?php echo $location_id ?>" >
    <input type="hidden" name="district_id" value="<?php echo $district_id ?>" >
    <div class="row" style="max-width:1000px">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><?php vv_e( 'Basic Information' ); ?></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php vv_e( 'Name' ); ?></label>
                                <input type="text" name="name" value="<?php echo $name ?>" class="form-control form-control-sm" required >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php vv_e( 'District' ); ?></label>
                                <input type="text" name="district_search" value="<?php echo gArrayItem($district,'post_title') ?>" class="form-control form-control-sm" required >
                            </div>
                        </div>
                    </div>
                    <div  class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php vv_e( 'Facilities' ); ?></label>
                                <div class=""><input type="checkbox" name="chkAll" value="all" > <?php vv_e( 'All' ); ?></div>
                                <?php 
                                foreach($facilities as $facility){ 
                                    $checked = '';
                                    if(in_array(gArrayItem($facility,'facility_id'),$location_facilities,true)) $checked = 'checked';
                                    ?>
                                    <div class=""><input type="checkbox" name="facilities[]" value="<?php echo gArrayItem($facility,'facility_id') ?>" <?php echo $checked ?> > <?php echo gArrayItem($facility,'name') ?></div>
                                    <?php 
                                } 
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php vv_e( 'Security Features' ); ?></label>
                                <div class=""><input type="checkbox" name="chkAll" value="all" > <?php vv_e( 'All' ); ?></div>
                                <?php 
                                foreach($security_features as $security_feature){ 
                                    $checked = '';
                                    if(in_array(gArrayItem($security_feature,'security_feature_id'),$location_security_features,true)) $checked = 'checked';
                                    ?>
                                    <div class=""><input type="checkbox" name="security_features[]" value="<?php echo gArrayItem($security_feature,'security_feature_id') ?>" <?php echo $checked ?> > <?php echo gArrayItem($security_feature,'name') ?></div>
                                    <?php 
                                } 
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?php vv_e( 'Main Image' ); ?></label>
                        <?php 
                        if($main_image > 0){
                            $image  = vv_get_image_array($main_image);
                            $sizes = gArrayItem($image,'sizes');
                            $thumbnail = gArrayItem($sizes,'thumbnail');
                            if($thumbnail == '') $thumbnail = gArrayItem($image,'url');
                            ?>
                            <div class="mb-2"><img src="<?php echo $thumbnail ?>" style="max-width:200px" class="border p-2" ></div>
                            <input type="file" name="main_image" class="form-control form-control-sm d-inline w-auto"> <?php vv_e( 'Replace Image' ); ?>
                            <?php
                        }else{
                            ?>
                            <input type="file" name="main_image" class="form-control form-control-sm d-inline w-auto"> <?php vv_e( 'Upload New Image' ); ?>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><?php vv_e( 'Header Settings' ); ?></div>
                <div class="card-body">
                    <div class="form-group">
                        <label><?php vv_e( 'Background Image' ); ?></label>
                        <?php 
                        if($header_bg_image > 0){
                            $image  = vv_get_image_array($header_bg_image);
                            $sizes = gArrayItem($image,'sizes');
                            $thumbnail = gArrayItem($sizes,'thumbnail');
                            if($thumbnail == '') $thumbnail = gArrayItem($image,'url');
                            ?>
                            <div class="mb-2"><img src="<?php echo $thumbnail ?>" style="max-width:200px" class="border p-2" ></div>
                            <input type="file" name="header_bg_image" class="form-control form-control-sm d-inline w-auto"> <?php vv_e( 'Replace Image' ); ?>
                            <?php
                        }else{
                            ?>
                            <input type="file" name="header_bg_image" class="form-control form-control-sm d-inline w-auto"> <?php vv_e( 'Upload New Image' ); ?>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="form-group">
                        <label><?php vv_e( 'Header Text' ); ?></label>
                        <textarea name="header_text" class="form-control form-control-sm wysiwyg_editor" ><?php echo $header_text ?></textarea>           
                    </div>             
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button class="btn btn-primary" ><?php vv_e( 'Submit' ); ?></button>
    </div>
</form>


<?php ob_start(); ?>
<style>
.bootstrap-autocomplete{
    font-size: 12px;
}    
</style>
<?php $header_codes .= ob_get_clean(); ?>

<?php ob_start(); ?>
<script>
    function removeLocation(obj){
        if(confirm('<?php echo esc_js( vv__( 'Remove location?' ) ); ?>')){
            $(obj).closest('tr').remove();
        }
        return false;
    }
    jQuery(document).ready(function (){

        $('input[name="chkAll"]').click(function (){
            $('input[name="facilities[]"]').prop('checked',$(this).is(":checked"));
        });

        $('.btnAddLocation').click(function (e){

            html = '<tr>';
            html += '<td><input name="locations[0]" value="" placeholder="<?php echo esc_js( vv__( 'New' ) ); ?>" class="form-control form-control-sm" ></td>';
            html += '<td><a href="#" onclick="return removeLocation(this)" ><i class="fa fa-trash" ></i></a></td>';
            html += '</tr>';

            $('.table-locations').find('tbody').append(html);

            e.preventDefault();
        });

        $('input[name="district_search"]').autoComplete({
            resolverSettings: {
                url: '<?php echo vv_admin_url().'?vv_action=districts_autocomplete' ?>'
            }
        });

        $('input[name="district_search"]').on('keyup',function (){

        });
        $('input[name="district_search"]').on('autocomplete.select', function (evt, item) {
            console.log(item);
            if(typeof item == 'undefined'){
                $('input[name="district_search"]').val('');
            }else{
                $('input[name="district_search"]').val(item.text);
                $('input[name="district_id"]').val(item.value);

            }
        });    


    });
</script>

<?php $footer_codes .= ob_get_clean(); ?>
