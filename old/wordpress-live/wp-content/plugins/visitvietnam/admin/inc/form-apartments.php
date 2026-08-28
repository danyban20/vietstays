<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<style type="text/css">
    .form-group label{ font-weight:bold }

    a.apartment_img-thumb > div {
        display: inline-block;
        width: 150px;
        height: 150px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position-x: 50%;
        background-position-y: 50%;
    }

.tab-vertical .nav.nav-tabs {
    float: left;
    display: block;
    margin-right: 0px;
    border-bottom: 0;
}

.tab-vertical .nav.nav-tabs .nav-item {
    margin-bottom: 6px;
}

.tab-vertical .nav-tabs .nav-link {
    border: 1px solid transparent;
    border-top-left-radius: .25rem;
    border-top-right-radius: .25rem;
    background: #fff;
    padding: 17px 25px;
    color: #fff;
    background-color: #004041;
    -webkit-border-radius: 4px 0px 0px 4px;
    -moz-border-radius: 4px 0px 0px 4px;
    border-radius: 4px 0px 0px 4px;
}

.tab-vertical .nav-tabs .nav-link.active {
    color: #004041;
    background-color: #fff !important;
    border-color: transparent !important;
}

.tab-vertical .nav-tabs .nav-link {
    border: 1px solid transparent;
    border-top-left-radius: 4px !important;
    border-top-right-radius: 0px !important;
}

.tab-vertical .tab-content {
    overflow: auto;
    -webkit-border-radius: 0px 4px 4px 4px;
    -moz-border-radius: 0px 4px 4px 4px;
    border-radius: 0px 4px 4px 4px;
    background: #fff;
    padding: 30px;
    min-height:500px;
}

#imagesTable{
    list-style: none;
    padding: 0;
    margin: 0;
}
#imagesTable li{
    display:inline-block;
    max-width:350px;
    margin:5px;
}

.ui-sortable-handle{ cursor:all-scroll; }
.ui-state-highlight{ min-height:150px; background-color:#FD780E; width:350px;}

</style>
<?php
global $wpdb;

$apartment_id           = gArrayItem($apartment,'ID');
$districts              = vv_get_districts();
$facilities             = vv_get_facilities();
$cleaners_checklists    = vv_get_cleaners_checklists();
//echo print_r_pre($districts);

//echo print_r_pre($apartment);

$pricing = json_decode(gArrayItem($apartment,'pricing'),true);
if(!is_array($pricing)) $pricing = array();

$daysofweek = ['sun','mon','tue','wed','thu','fri','sat'];

?>
<link rel="stylesheet" href="<?php echo vv_plugins_url() ?>/admin/plugins/ekko-lightbox/ekko-lightbox.css">
<link rel="stylesheet" href="<?php echo vv_plugins_url() ?>/admin/plugins/dropzone/min/dropzone.min.css">


<form method="post" id="formApartment" enctype="multipart/form-data">
    <input type="hidden" name="action" value="vv_save_apartment">
    <input type="hidden" name="apartment_id" value="<?php echo intval(gArrayItem($apartment,'ID')) ?>" >



        <div class="tab-vertical">
            <ul class="nav nav-tabs" id="myTab3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="information-tab" data-toggle="tab" href="#information" role="tab" aria-controls="home" aria-selected="true">Apartment Info</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pricing-discounts-tab" data-toggle="tab" href="#pricing-discounts" role="tab" aria-controls="contact" aria-selected="false">Pricing & Discounts</a>
                </li>
                <?php if(gArrayItem($apartment,'ID') > 0){ ?>
                    <li class="nav-item">
                        <a class="nav-link" id="images-tab" data-toggle="tab" href="#images" role="tab" aria-controls="profile" aria-selected="false">Images</a>
                    </li>
                <?php } ?>
                <li class="nav-item">
                    <a class="nav-link" id="foods-tab" data-toggle="tab" href="#foods" role="tab" aria-controls="contact" aria-selected="false">Foods</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="facilities-tab" data-toggle="tab" href="#facilities" role="tab" aria-controls="contact" aria-selected="false">Facilities</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="cleaners-tab" data-toggle="tab" href="#cleaners" role="tab" aria-controls="contact" aria-selected="false">Cleaners Checklist</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent3">
                <div class="tab-pane fade show active" id="information" role="tabpanel" aria-labelledby="information-tab">
                    <div class="form-group">
                        <label class="control-label">Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" value="<?php echo gArrayItem($apartment,'name') ?>" required >
                    </div>
                    <div class="form-group">
                        <label class="control-label">District</label>
                        <select name="district" class="form-control form-control-sm"  >
                            <?php foreach($districts as $district){ ?>
                                <option value="<?php echo $district['ID'] ?>" <?php if(gArrayItem($apartment,'district') == $district['ID']) echo 'selected' ?> ><?php echo $district['name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Address</label>
                        <input type="text" name="address" class="form-control form-control-sm" value="<?php echo gArrayItem($apartment,'address') ?>" required >
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Address - Latitude</label>
                                <input type="text" name="address_latitude" class="form-control form-control-sm" value="<?php echo gArrayItem($apartment,'address_latitude') ?>" required >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Address - Longitude</label>
                                <input type="text" name="address_longitude" class="form-control form-control-sm" value="<?php echo gArrayItem($apartment,'address_longitude') ?>" required >
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Description</label>
                        <textarea name="description" class="form-control form-control-sm" ><?php echo gArrayItem($apartment,'description') ?></textarea>
                    </div>
                    <?php 
                    if($logged_user_role == 'administrator'){


                        $partners_r = $this->users_class->get_users(['position' => 'partner']);
                        $partners   = gArrayItem($partners_r,'users');
                        ?>
                        <div class="form-group">
                            <label class="control-label">Owner</label>
                            <select name="user_id" class="form-control form-control-sm" >
                                <option value="0">Visit Vietnam Owner</option>
                                <?php foreach($partners as $partner){ ?>
                                    <option value="<?php echo $partner->ID ?>" <?php if(gArrayItem($apartment,'user_id') == $partner->ID) echo 'selected' ?> >Partner: <?php echo $partner->display_name ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <?php 
                    }else{
                        echo '<input type="hidden" name="user_id" value="'.$logged_user_id.'" >';
                    }
                    ?>
                    <div class="form-group">
                        <label class="control-label">Maximum Guests</label>
                        <input type="text" name="max_guests" class="form-control form-control-sm" value="<?php echo gArrayItem($apartment,'max_guests') ?>" required >
                    </div>
                    <div class="form-group">
                        <label class="control-label"># of Rooms</label>
                        <input type="text" name="rooms" class="form-control form-control-sm" value="<?php echo gArrayItem($apartment,'rooms') ?>" required >
                    </div>
                    <div class="form-group">
                        <label class="control-label"># of Beds</label>
                        <input type="text" name="num_beds" class="form-control form-control-sm" value="<?php echo gArrayItem($apartment,'num_beds') ?>" required >
                    </div>
                    <div class="form-group">
                        <label class="control-label"># of Bathrooms</label>
                        <input type="text" name="num_bathrooms" class="form-control form-control-sm" value="<?php echo gArrayItem($apartment,'num_bathrooms') ?>" required >
                    </div>
                    <div class="form-group">
                        <label class="control-label">Area sqm</label>
                        <input type="text" name="area_sqm" class="form-control form-control-sm" value="<?php echo gArrayItem($apartment,'area_sqm') ?>"  >
                    </div>
                    <div class="form-group">
                        <label class="control-label">About This</label>
                        <textarea name="about_this" class="form-control form-control-sm" ><?php echo gArrayItem($apartment,'about_this') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label">About This (Short)</label>
                        <textarea name="about_this_short" class="form-control form-control-sm" ><?php echo gArrayItem($apartment,'about_this_short') ?></textarea>
                    </div>
                    <div class="form-group">
                        <div>
                            <input type="checkbox" name="checkin_without_host" value="1" <?php echo (gArrayItem($apartment,'checkin_without_host') == 1) ? 'checked' : '' ?> >
                            <label>Check-in without Host</label>
                        </div>
                        <div>
                            <input type="checkbox" name="airport_pickup" value="1" <?php echo (gArrayItem($apartment,'airport_pickup') == 1) ? 'checked' : '' ?> >
                            <label>Air Port Pick Up</label>
                        </div>
                        <div>
                            <input type="checkbox" name="flexible_reservation" value="1" <?php echo (gArrayItem($apartment,'flexible_reservation') == 1) ? 'checked' : '' ?> >
                            <label>Flexible reservation</label>
                        </div>
                        <div>
                            <input type="checkbox" name="scooter_rental" value="1" <?php echo (gArrayItem($apartment,'scooter_rental') == 1) ? 'checked' : '' ?> >
                            <label>Scooters availability</label>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pricing-discounts" role="tabpanel" aria-labelledby="pricing-discounts-tab">
                    <div class="form-group" style="max-width:200px;">
                        <label class="control-label">Pricing</label>
                        <table class="table w-auto" >
                            <?php 
                            for($i = 0; $i < count($daysofweek); $i++){
                                ?>
                                <tr>
                                    <td><?php echo strtoupper($daysofweek[$i]) ?></td>
                                    <td><input type="text" name="pricing[<?php echo $i ?>]" class="form-control form-control-sm" value="<?php echo gArrayItem($pricing,$i) ?>" ></td>
                                </tr>
                                <?php 
                            }
                            ?>
                        </table>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Discounts</label>
                        <table class="discounts table w-auto border">
                            <tr>
                                <th>Start</th>
                                <th>End</th>
                                <th>Discount %</th>
                                <th>Action</th>
                            </tr>
                            <?php
                            $discounts = array(); 
                            if(gArrayItem($apartment,'ID') > 0){
                                $discounts = $this->apartment_class->get_discounts(gArrayItem($apartment,'ID'));
                            }
                            foreach($discounts as $discount){
                                ?>
                                <tr>
                                    <td><input type="text" name="discount_start[]" autocomplete="no" class="form-control form-control-sm datepick" value="<?php echo ($discount['datestart'] > 0) ? date("m/d/Y",$discount['datestart']) : '' ?>" ></td>
                                    <td><input type="text" name="discount_end[]" autocomplete="no" class="form-control form-control-sm datepick" value="<?php echo ($discount['dateend'] > 0) ? date("m/d/Y",$discount['dateend']) : '' ?>" ></td>
                                    <td><input type="text" name="discount[]" class="form-control form-control-sm" value="<?php echo $discount['discount'] ?>" ></td>
                                    <td>
                                        <input type="hidden" name="discount_id[]" value="<?php echo $discount['apt_discount_id'] ?>" >
                                        <a href="#" onclick="return removeDiscount(this)" ><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php 
                            }
                            ?>
                            <tr> 
                                <td><input type="text" name="discount_start[]" autocomplete="no" class="form-control form-control-sm datepick" value="" ></td>
                                <td><input type="text" name="discount_end[]" autocomplete="no" class="form-control form-control-sm datepick" value="" ></td>
                                <td><input type="text" name="discount[]" class="form-control form-control-sm" value="" ></td>
                                <td>
                                    <input type="hidden" name="discount_id[]" value="0" >
                                    <em>new</em>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="form-group">
                        <label>Cleaning Fee</label>
                        <input type="text" name="cleaning_fee" class="form-control" value="<?php echo gArrayItem($apartment,'cleaning_fee') ?>">
                    </div>
                </div>
                <?php if(gArrayItem($apartment,'ID') > 0){ ?>
                    <div class="tab-pane fade" id="images" role="tabpanel" aria-labelledby="images-tab">
                        <div class="form-group">
                            <label class="control-label">Images</label>
                            <div id="apartment_images_wrap">
                            <?php 
                            $this->apartment_class->get_apartment_images_html($apartment);
                            ?>                
                            </div>
                            <div class="mt-4"><button type="button" class="btn btn-info btn-sm btnAddImage" data-toggle="modal" data-target="#uploadImagesModal" >Add Image</button></div>
                        </div>
                    </div>
                <?php } ?>
                <div class="tab-pane fade" id="foods" role="tabpanel" aria-labelledby="foods-tab">
                    <div class="form-group">
                        <label class="control-label">Foods</label>
                        <div class="form-wrap2">
                            <table class="foods FoodsWrap table border">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $foods = array();
                                    if(gArrayItem($apartment,'ID') > 0){
                                        $foods = $this->apartment_class->get_apartment_foods(gArrayItem($apartment,'ID'));

                                        //echo print_r_pre($foods);
                                    }
                                    foreach($foods as $food){
                                        ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                if($food['image'] != '') echo '<img src="'.$food['image'].'" class="img-fluid" style="width:80px" >';
                                                else echo '&nbsp;';
                                                ?>
                                            </td>
                                            <td><?php echo stripslashes(gArrayItem($food,'name'))  ?></td>
                                            <td><input type="text" name="food_price[]" autocomplete="no" class="form-control form-control-sm " value="<?php echo gArrayItem($food,'price')  ?>" ></td>
                                            <td>
                                                <input type="hidden" name="food_name[]" value="<?php echo $food['name'] ?>" >
                                                <input type="hidden" name="food_id[]" value="<?php echo $food['food_id'] ?>" >
                                                <input type="hidden" name="apt_food_id[]" value="<?php echo $food['apt_food_id'] ?>" >
                                                <a href="#" onclick="return removeFood(this)" ><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php 
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2"><a href="#" data-toggle="modal" data-target="#addFoodModal" class="btn btn-info btn-sm" >Add Food</a></div>
                    </div>
                </div>
                <div class="tab-pane fade" id="facilities" role="tabpanel" aria-labelledby="facilities-tab">
                    <div class="form-group">
                        <label>Facilities</label>
                        <?php 
                        $apartment_facilicies = json_decode(gArrayItem($apartment,'facilities'));
                        if(!is_array($apartment_facilicies)) $apartment_facilicies = [];
                        foreach($facilities as $facility){ 
                            $checked = '';
                            if(in_array(gArrayItem($facility,'facility_id'),$apartment_facilicies)) $checked = 'checked';
                            ?>
                            <div class=""><input type="checkbox" name="facilities[]" value="<?php echo gArrayItem($facility,'facility_id') ?>" <?php echo $checked ?> > <?php echo gArrayItem($facility,'name') ?></div>
                            <?php 
                        } 
                        ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="cleaners" role="tabpanel" aria-labelledby="cleaners-tab">
                        <div class="form-group">
                            <label class="control-label">Clearners Checklist</label>
                            <div class="form-wrap2">
                                <table class="foods CleanersChecklistWrap table border">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th style="width:100px">Qty</th>
                                            <th style="width:100px">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $apt_cleaners_checklists = json_decode(gArrayItem($apartment,'cleaners_checklists'),true);
                                        if(!is_array($apt_cleaners_checklists)) $apt_cleaners_checklists = [];
                                        foreach($apt_cleaners_checklists as $checklist){
                                            ?>
                                            <tr>
                                                <td><?php echo stripslashes(gArrayItem($checklist,'name'))  ?></td>
                                                <td><input type="text" name="checklist_qty[]" autocomplete="no" class="form-control form-control-sm " value="<?php echo gArrayItem($checklist,'qty')  ?>" ></td>
                                                <td>
                                                    <input type="hidden" name="checklist_name[]" value="<?php echo gArrayItem($checklist,'name') ?>" >
                                                    <input type="hidden" name="checklist_id[]" value="<?php echo gArrayItem($checklist,'id') ?>" >
                                                    <input type="hidden" name="checklist_type[]" value="<?php echo gArrayItem($checklist,'type') ?>" >
                                                    <a href="#" onclick="return removeCleanersChecklist(this)" ><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                            <?php 
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2"><a href="#" data-toggle="modal" data-target="#addCleanersChecklistModal" class="btn btn-info btn-sm" >Add Item</a></div>
                        </div>
                </div>
            </div>
        </div>


    <hr>
    <div class="">
        <button type="submit" class="btn btn-primary pl-4 pr-4">Submit</button> 
    </div>
</form>



<?php 
global $footer_codes;
ob_start();

$food_categories = vv_get_food_categories();
?>
<div class="modal fade" id="addFoodModal" tabindex="-1" role="dialog" aria-labelledby="addFoodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFoodModalLabel">Add Food</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category</label >
                        <select name="category" id="food_category" class="form-control" required>
                            <option value="">-Select Category</option>
                            <?php 
                            foreach($food_categories as $food_cat){
                                ?>
                                <option value="<?php echo gArrayItem($food_cat,'food_category_id') ?>" ><?php echo $food_cat['name'] ?></option>
                                <?php 
                            }
                            ?>
                        </select>
                    </div>
                    <div id="addFoodList"></div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="addCleanersChecklistModal" tabindex="-1" role="dialog" aria-labelledby="addCleanersChecklistModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCleanersChecklistModalLabel">Add Cleaners Checklist</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                            <table class="table">
                                <?php 
                                foreach($cleaners_checklists as $cleaners_checklist){
                                    ?>
                                    <tr>
                                        <td><?php echo $cleaners_checklist['name'] ?></td>
                                        <td style="width:100px">
                                            <?php 
                                            if($cleaners_checklist['type'] == 'qty'){
                                                ?>
                                                <input type="text" name="qty_<?php echo $cleaners_checklist['checklist_id'] ?>" class="form-control form-control-sm" value="1" required>
                                                <?php 
                                            }else{
                                                ?>
                                                <input type="hidden" name="qty_<?php echo $cleaners_checklist['checklist_id'] ?>" class="form-control form-control-sm" value="1">
                                                &nbsp;
                                                <?php 
                                            }
                                            ?>
                                        </td>
                                        <td style="width:60px">
                                            <button type="button" class="btn btn-info btn-sm btnAddCleanersChecklist" data-checklist_id="<?php echo $cleaners_checklist['checklist_id'] ?>" data-name="<?php echo $cleaners_checklist['name'] ?>" data-type="<?php echo $cleaners_checklist['type'] ?>"  >Add</button>
                                        </td>
                                    </tr>
                                    <?php 
                                }
                                ?>
                            </table>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="uploadImagesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 900px;" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Images</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="actions" class="row">
                    <div class="col-lg-6">
                        <div class="btn-group w-100">
                            <span class="btn btn-success col fileinput-button">
                                <i class="fas fa-plus"></i>
                                <span>Add Images</span>
                            </span>
                            <button type="submit" class="btn btn-primary col start">
                                <i class="fas fa-upload"></i>
                                <span>Start upload</span>
                            </button>
                            <button type="reset" class="btn btn-warning col cancel">
                                <i class="fas fa-times-circle"></i>
                                <span>Cancel upload</span>
                            </button>
                        </div>
                    </div>
                    <div class="col-lg-6 d-flex align-items-center">
                        <div class="fileupload-process w-100">
                            <div id="total-progress" class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table table-striped files" id="previews">
                    <div id="addImageTemplate" class="row mt-2">
                        <div class="col-auto"><span class="preview"><img src="data:," alt="" data-dz-thumbnail /></span></div>
                        <div class="col d-flex align-items-center">
                            <p class="mb-0"><span class="lead" data-dz-name></span>(<span data-dz-size></span>)</p>
                            <strong class="error text-danger" data-dz-errormessage></strong>
                        </div>
                        <div class="col-4 d-flex align-items-center">
                            <div class="progress progress-striped active w-100" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                            </div>
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <div class="btn-group">
                                <button class="btn btn-primary start"><i class="fas fa-upload"></i><span>Start</span></button>
                                <button data-dz-remove class="btn btn-warning cancel"><i class="fas fa-times-circle"></i><span>Cancel</span></button>
                                <button data-dz-remove class="btn btn-danger delete"><i class="fas fa-trash"></i><span>Delete</span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="<?php echo vv_plugins_url() ?>admin/plugins/dropzone/min/dropzone.min.js"></script>
<script src="<?php echo vv_plugins_url() ?>admin/plugins/ekko-lightbox/ekko-lightbox.min.js"></script>

<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>


<script>
    var Foods;
    function removeDiscount(obj){
        if(confirm('Remove discount?')){
            jQuery(obj).parent('td').parent('tr').find('input[name="discount[]"]').val('delete');
            jQuery(obj).parent('td').parent('tr').hide();
        }
        return false;
    }
    function addFood(food_id , obj){

        for(i = 0; i < Foods.length; i++){
            if(Foods[i].food_id == food_id){
                html = '<tr>';
                html += '<td>'

                if(Foods[i].image != ''){
                    html += '<img src="'+Foods[i].image+'" class="img-fluid" style="width:80px" >';
                }else{
                    html += '&nbsp;';
                }

                html += '</td>';
                html += '<td>'+Foods[i].name+'</td>';
                html += '<td><input type="text" name="food_price[]" autocomplete="no" class="form-control form-control-sm " value="'+Foods[i].price+'" ></td>';
                html += '<td>';
                html += '<input type="hidden" name="food_name[]" value="'+Foods[i].name+'" >';
                html += '<input type="hidden" name="food_id[]" value="'+Foods[i].food_id+'" >';
                html += '<input type="hidden" name="apt_food_id[]" value="new" >';
                html += '<a href="#" onclick="return removeFood(this)" ><i class="fa fa-trash"></i></a>';
                html += '</td>';
                html += '</tr>';

                $('table.foods').find('tbody').append(html);
            }
        }

        $('#addFoodModal').modal('hide');


    }
    function removeFood(obj){
        if(confirm('Remove food?')){            
            jQuery(obj).parent('td').parent('tr').hide();
            jQuery(obj).parent('td').parent('tr').find('input[name="food_name[]"]').val('delete');    
        }
        return false;
    }

    function deleteImage(obj){
        if(confirm('Delete Image?')){
            prod_img = jQuery(obj);
            jQuery.get(prod_img.attr('href'),function (data){
                prod_img.parent('div').parent('div').parent('div').html('<div class="alert alert-danger">Image Deleted</div>');
            });
        }
        return false;
    }

    function removeCleanersChecklist(obj){
        if(confirm('Remove Item?')){
            jQuery(obj).parent('td').parent('tr').remove();
        }
        return false;
    }

    function init_images_sorting(){
        let $ = jQuery.noConflict();
        $( "#imagesTable" ).sortable({
            placeholder: "ui-state-highlight",
          handle: '.handle-sort'
        });
        $( "#imagesTable" ).disableSelection();

    }

    jQuery(document).ready(function (){
        let $ = jQuery.noConflict();

        $('.datepick').daterangepicker({
            timePicker: false,
            setStartDate : '',
            minDate: "<?php echo date("d/m/Y") ?>",
            minSpan: { "days" : 1 },
            autoApply: true,    
            singleDatePicker: true,
            locale: {
                format: 'MM/DD/YYYY',
                cancelLabel: 'Clear',
            },
            autoUpdateInput: false,
        });

        $('.datepick').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY'));
        });

        $('.btnAddImage').click(function (){
            $('#form-images').append('<div><input type="file" class="form-control" name="images[]" ></div>');
        });

        $('#food_category').change(function (){
            if($(this).val() > 0){
                $.get('<?php echo vv_admin_url() ?>/?action=get_foods_json&food_cat='+$(this).val(), function (data){
                    console.log(data);
                    if(data.status == 1){
                        Foods = data.data;
                        html = '';
                        for(i = 0; i < Foods.length; i++){
                            html += '<tr><td>';
                            if(Foods[i].image != '') html += '<img src="'+Foods[i].image+'" class="img-fluid" style="width:80px" >';
                            else html += '&nbsp;';
                            html += '</td>';
                            html += '<td>' + Foods[i].name+'</td>';
                            html += '<td>' + Foods[i].price + '</td>';
                            html += '<td class="text-right" ><button type="button" class="btn btn-sm btn-primary" onclick="addFood('+Foods[i].food_id+',this)" >Add</button></td>';
                            html += '</tr>';
                        }

                        if(html != ''){
                            html = '<table class="table border" >'+html+'</table>';
                        }else{
                            html = '<div class="alert alert-danger">No Food Found</div>'
                        }
                        $('#addFoodList').html(html);
                    }
                });
            }
        });

        $('.btnAddCleanersChecklist').on('click',function (e){ 

            checklist_id = $(this).attr('data-checklist_id');
            name = $(this).attr('data-name');
            type = $(this).attr('data-type');
            qty = $(this).parent('td').parent('tr').find('input[name="qty_'+checklist_id+'"]').val();


            html = '<tr>';
            html += '<td>'+name+'</td>';
            if(type == 'qty'){
                html += '<td><input type="text" name="checklist_qty[]" autocomplete="no" class="form-control form-control-sm " value="'+qty+'" ></td>';
            }else{
                html += '<td><input type="hidden" name="checklist_qty[]" autocomplete="no" class="form-control form-control-sm " value="'+qty+'" ></td>';
            }
            html += '<td>';
            html += '<input type="hidden" name="checklist_name[]" value="'+name+'" >';
            html += '<input type="hidden" name="checklist_id[]" value="'+checklist_id+'" >';
            html += '<input type="hidden" name="checklist_type[]" value="'+type+'" >';
            html += '<a href="#" onclick="return removeCleanersChecklist(this)" ><i class="fa fa-trash"></i></a>';
            html += '</td>';
            html += '</tr>';

            $('.CleanersChecklistWrap').find('tbody').append(html);

            e.preventDefault();
        });

        <?php if(gArrayItem($apartment,'ID') > 0){ ?>

            // DropzoneJS Demo Code Start
            Dropzone.autoDiscover = false

            // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
            var previewNode = document.querySelector("#addImageTemplate")
            previewNode.id = ""
            var previewTemplate = previewNode.parentNode.innerHTML
            previewNode.parentNode.removeChild(previewNode)

            var myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
                url: "<?php echo vv_admin_url()?>/?action=upload_apartment_image&id=<?php echo gArrayItem($apartment,'ID') ?>", // Set the url
                thumbnailWidth: 80,
                thumbnailHeight: 80,
                parallelUploads: 20,
                acceptedFiles: "image/jpeg,image/png,image/gif,image/webp",
                previewTemplate: previewTemplate,
                autoQueue: false, // Make sure the files aren't queued until manually added
                previewsContainer: "#previews", // Define the container to display the previews
                clickable: ".fileinput-button" // Define the element that should be used as click trigger to select files.
            })

            myDropzone.on("addedfile", function(file) {
                // Hookup the start button
                file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file) }
            })

            // Update the total progress bar
            myDropzone.on("totaluploadprogress", function(progress) {
                document.querySelector("#total-progress .progress-bar").style.width = progress + "%"
            })

            myDropzone.on("sending", function(file) {
                // Show the total progress bar when upload starts
                document.querySelector("#total-progress").style.opacity = "1"
                // And disable the start button
                file.previewElement.querySelector(".start").setAttribute("disabled", "disabled")
            })

            // Hide the total progress bar when nothing's uploading anymore
            myDropzone.on("queuecomplete", function(progress) {
                $('#apartment_images_wrap').html('<img src="<?php echo vv_plugins_url() ?>admin/images/ajax-loader.gif" >');
                $.get('<?php echo vv_admin_url().'?action=get_apartment_images_html&id='.$apartment_id.'&t=' ?>' + new Date().getTime(), 
                    function (data){ 
                        $('#apartment_images_wrap').html(data);
                        $('#uploadImagesModal').modal('hide');
                        init_images_sorting();
                    }
                );
                
            })

            // Setup the buttons for all transfers
            // The "add files" button doesn't need to be setup because the config
            // `clickable` has already been specified.
            document.querySelector("#actions .start").onclick = function() {
                myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED))
            }
            document.querySelector("#actions .cancel").onclick = function() {
                myDropzone.removeAllFiles(true)
            }
        <?php }else{ ?>
        <?php } ?>



        init_images_sorting();


    });
</script>
<?php 

$footer_codes .= ob_get_clean();
