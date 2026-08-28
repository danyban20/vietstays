<?php 
$firstname      = '';
$lastname       = '';
$district_id            = gArrayItem($district,'ID');
$name                   = gArrayItem($district,'post_title');
$city_id                = 0;
$district_facilities    = [];
$locations              = [];
$main_image             = 0;
$header_bg_image        = 0;
$header_text            = '';
if($district_id > 0){

    $city_id             = get_post_meta($district_id,'city',true);
    $district_facilities = get_post_meta($district_id,'facilities',true);

    if(!is_array($district_facilities)) $district_facilities = [];

    $main_image         = get_post_meta($district_id,'main_image',true);
    $header_bg_image    = get_post_meta($district_id,'header_bg_image',true);
    $header_text        = get_post_meta($district_id,'header_text',true);

    $locations = $this->district_class->get_districts(['per_page' => 'all', 'parent' => $district_id, 'orderby' => 'post_title ASC']);

}

if($name == '')     $name = POST_Request('name');

$cities = vv_get_cities();
$facilities = vv_get_facilities();

//echo print_r_pre($district);

?>
<form method="post" enctype="multipart/form-data" >
    <input type="hidden" name="vv_action" value="save_district">
    <input type="hidden" name="district_id" value="<?php echo $district_id ?>" >
    <div class="row">
        <div class="col-md-6">
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
                                <label><?php vv_e( 'City' ); ?></label>
                                <select name="city_id" class="form-control form-control-sm" required >
                                    <option value=""><?php vv_e( 'Choose City' ); ?></option>
                                    <?php foreach($cities as $city){ ?>
                                        <option value="<?php echo $city['ID'] ?>" <?php echo ($city['ID'] == $city_id) ? 'selected' : '' ?> ><?php echo $city['post_title'] ?></option>
                                    <?php } ?>
                                </select>
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

    });
</script>

<?php $footer_codes .= ob_get_clean(); ?>
