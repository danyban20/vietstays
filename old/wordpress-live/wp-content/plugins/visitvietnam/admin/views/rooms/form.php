<?php
global $wpdb;
$districts = vv_get_districts();

$pricing = json_decode(gArrayItem($room,'pricing'),true);

if(!is_array($pricing)) $pricing = array();

$daysofweek = ['sun','mon','tue','wed','thu','fri','sat'];


?>
<form method="post">
    <input type="hidden" name="action" value="vv_save_room">
    <input type="hidden" name="room_id" value="<?php echo intval(gArrayItem($room,'ID')) ?>" >
    <input type="hidden" name="apartment_id" value="<?php echo intval(gArrayItem($apartment,'ID')) ?>" >
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label"><?php vv_e( 'Name' ); ?></label>
                <input type="text" name="name" class="form-control form-control-sm" value="<?php echo gArrayItem($room,'name') ?>" >
            </div>
            <div class="form-group">
                <label class="control-label"><?php vv_e( 'Adults' ); ?></label>
                <input type="text" name="adults" class="form-control form-control-sm" value="<?php echo gArrayItem($room,'adults') ?>" >
            </div>
            <div class="form-group">
                <label class="control-label"><?php vv_e( 'Children' ); ?></label>
                <input type="text" name="children" class="form-control form-control-sm" value="<?php echo gArrayItem($room,'children') ?>" >
            </div>
            <div class="form-group">
                <label class="control-label"><?php vv_e( 'Extra Person' ); ?></label>
                <input type="text" name="extra" class="form-control form-control-sm" value="<?php echo gArrayItem($room,'extra') ?>" >
            </div>
        </div>
    </div>
    <div class="form-group border p-4">
        <label class="control-label"><?php vv_e( 'Pricing' ); ?></label>
        <div class="row">
            <?php 
            for($i = 0; $i < count($daysofweek); $i++){
                ?>
                <div class="col-1">
                    <div><b><?php echo strtoupper($daysofweek[$i]) ?></b></div>
                    <div><input type="text" name="pricing[<?php echo $i ?>]" class="form-control form-control-sm" value="<?php echo gArrayItem($pricing,$i) ?>" ></div>
                </div>
                <?php 
            }
            ?>
        </div>
    </div>
    <div class="mt-4 row">
        <div class="col-md-4"><button type="submit" class="btn btn-block btn-primary"><?php vv_e( 'Submit' ); ?></button></div>
    </div>
</form>
                
