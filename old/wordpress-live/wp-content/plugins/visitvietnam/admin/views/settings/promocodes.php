<?php 
global $wpdb;

$filter = ['return_total' => 1];

if(GET_Request('user') > 0) $filter['user_id'] = GET_Request('user');

$result     = $this->promocodes_class->get_promocodes($filter);
$promocodes = gArrayItem($result,'promocodes');


for($i = 0; $i < count($promocodes); $i++){
    $apartment_ids = json_decode($promocodes[$i]['apartment_ids']);
    $apartments = [];
    if(is_array($apartment_ids)){
        foreach($apartment_ids as $id){
            $result = $this->apartment_class->get_apartment($id);

            $apartment = [];
            $apartment['ID']            = gArrayItem($result,'ID');
            $apartment['name']          = stripslashes(gArrayItem($result,'name'));
            $apartment['display_name']  = stripslashes(gArrayItem($result,'display_name'));

            $apartments[]  = $apartment;
        }
    }

    $promocodes[$i]['apartments'] = $apartments;



    $ambassador_id = $promocodes[$i]['ambassador_id'];
    $ambassador = [];
    $result = $this->users_class->get_user($ambassador_id);

    $ambassador = [];
    $ambassador['ID']            = gArrayItem($result,'ID');
    $ambassador['firstname']     = gArrayItem($result,'firstname');
    $ambassador['lastname']      = gArrayItem($result,'lastname');
    $ambassador['email']         = gArrayItem($result,'user_email');

    $promocodes[$i]['ambassador'] = $ambassador;

}


ob_start();
?>
<style>

.apartment_ids .apartment, .ambassador_ids .ambassador{
    border: 1px solid #ccc;
    background: #efefef;
    border-radius: 3px;
    margin: 5px 5px 5px 0px;
    display: inline-block;
    padding: 3px 10px;
}

.apartment_ids .apartment span, .ambassador_ids .ambassador span{
    cursor: pointer;
}

</style>
<?php

$header_codes .= ob_get_clean();

$delete_confirm = esc_js( vv__( 'Delete this promocode?' ) );

?>



<div class="row">
    <div class="col-md-9">
        <h1><?php vv_e( 'Promo Codes' ); ?></h1>
    </div>
    <div class="col-md-3">
        <div class="text-right mb-3">
            <a href="#" class="btn btn-sm btn-primary btnAddPromoCode"><?php vv_e( 'Add Promo Code' ); ?></a>
        </div>
    </div>
</div>

<div class="">
        <form method="post">
            <input type="hidden" name="action" value="vv_save_promocodes" >
            <div class=" m-b-30">
                <table class="table table-borderless table-striped table-earning table-promocodes">
                    <thead>
                        <tr>
                            <th><?php vv_e( 'Promo Code' ); ?></th>
                            <th><?php vv_e( 'Discount (%)' ); ?></th>
                            <th><?php vv_e( 'Apartments' ); ?></th>
                            <th><?php vv_e( 'Ambassador' ); ?></th>
                            <th style="width:90px"><?php vv_e( 'Status' ); ?></th>
                            <th class="text-center" style="width:50px;"><?php vv_e( 'Action' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach($promocodes as $promocode){
                            $id = $promocode['ID'];
                            $status_label = vv__( ucfirst( strtolower( trim( gArrayItem( $promocode, 'status' ) ) ) ) );
                            ?>
                            <tr class="promocode_<?php $id ?>" >
                                <td><?php echo esc_html( $promocode['code'] ); ?></td>
                                <td class="text-center"><?php echo esc_html( $promocode['discount'] ); ?></td>
                                <td class="apartment_ids" >
                                    <ul class="pl-2">
                                    <?php 
                                    $apartments = gArrayItem($promocode,'apartments');
                                    foreach($apartments as $apartment){
                                        $name = gArrayItem($apartment,'display_name');
                                        if($name == '') $name = gArrayItem($apartment,'name');
                                        ?>
                                        <li>
                                            <a href="<?php echo vv_admin_url('apartments/edit').'?id='.$apartment['ID'] ?>" target="_blank" ><?php echo esc_html( $name ); ?></a>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                    </ul>
                                </td>
                                <td class="ambassador_ids">
                                    <?php 
                                    $ambassador = gArrayItem($promocode,'ambassador');
                                    if(gArrayItem($ambassador,'ID')){
                                        ?>
                                        <a href="<?php echo vv_admin_url('users/edit').'?id='.$ambassador['ID'] ?>" target="_blank" ><?php echo esc_html( gArrayItem($ambassador,'email') ); ?></a>
                                        <?php 
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html( $status_label ); ?></td>
                                <td class="text-right p-2">
                                    <a href="#" class="btnEditPromoCode" data-id="<?php echo $id ?>" title="<?php echo esc_attr( vv__( 'Edit' ) ); ?>"><i class="fa fa-pencil-alt"></i></a>
                                    <a href="<?php echo vv_admin_url('?action=delete-promocode&id='.$id) ?>" onclick="return confirm('<?php echo $delete_confirm; ?>')" title="<?php echo esc_attr( vv__( 'Delete' ) ); ?>"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </form>
</div>



<?php 
ob_start();
?>
<div class="modal fade" id="promoCodeModal" tabindex="-1" role="dialog" aria-labelledby="promoCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg " role="document" >
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="save_promocode">
                <input type="hidden" name="promocode_id" value="0" >
                <div class="modal-header">
                    <h5 class="modal-title" id="promoCodeModalLabel"><?php vv_e( 'Promo Code' ); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="">
                        <div class="p-3">
                            <div class="row mb-2">
                                <div class="col-md-3"><?php vv_e( 'Promo Code' ); ?></div>
                                <div class="col-md-9"><input type="text" name="code" class="form-control form-control-sm" value="" ></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-3"><?php vv_e( 'Discount' ); ?></div>
                                <div class="col-md-9"><input type="text" name="discount" class="form-control form-control-sm" value="" ></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-3"><?php vv_e( 'Apartments' ); ?></div>
                                <div class="col-md-9">
                                    <div><input type="text" name="apartment_search" class="form-control form-control-sm" placeholder="<?php echo esc_attr( vv__( 'Apartment #, Apartment Name' ) ); ?>" value="" ></div>
                                    <div class="apartment_ids"></div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-3"><?php vv_e( 'Ambassadors' ); ?></div>
                                <div class="col-md-9">
                                    <div><input type="text" name="ambassador_search" class="form-control form-control-sm" value="" ></div>
                                    <div class="ambassador_ids"></div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-3"><?php vv_e( 'Status' ); ?></div>
                                <div class="col-md-9">
                                    <select name="status" class="form-control form-control-sm" >
                                        <option value="active"><?php vv_e( 'Active' ); ?></option>
                                        <option value="inactive"><?php vv_e( 'Inactive' ); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="msg"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm"><?php vv_e( 'Submit' ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>    
    var promoCodes = <?php echo json_encode($promocodes) ?>;
    var $promoCodeModal = $('#promoCodeModal');
    var vvPromocodeI18n = <?php echo wp_json_encode( [
        'promoCode'        => vv__( 'Promo Code' ),
        'addPromoCode'     => vv__( 'Add Promo Code' ),
        'editPromoCode'    => vv__( 'Edit Promo Code' ),
        'removeApartment'  => vv__( 'Remove Apartment?' ),
        'removeAmbassador' => vv__( 'Remove Ambassador?' ),
    ] ); ?>;

    function removePromocodeApartment(obj){
        if(confirm(vvPromocodeI18n.removeApartment)){
            $(obj).parent('div').remove();
        }
    }
    function removePromocodeUser(obj){
        if(confirm(vvPromocodeI18n.removeAmbassador)){
            $(obj).parent('div').remove();
        }
    }
    
    jQuery(document).ready(function (){

        $('.btnAddPromoCode').click(function (){
            $promoCodeModal.find('.modal-title').html(vvPromocodeI18n.addPromoCode);
            $promoCodeModal.modal('show');
        });

        $('.btnEditPromoCode').click(function (){
            id = $(this).attr('data-id');

            for(i = 0; i < promoCodes.length; i++){
                if(promoCodes[i].ID == id){
                    $promoCodeModal.find('input[name="promocode_id"]').val(id);
                    $promoCodeModal.find('input[name="code"]').val(promoCodes[i].code);
                    $promoCodeModal.find('input[name="discount"]').val(promoCodes[i].discount);
                    $promoCodeModal.find('select[name="status"]').val(promoCodes[i].status);

                    apartments = promoCodes[i].apartments;
                    html = '';

                    for(x = 0; x < apartments.length; x++){
                        html += '<div class="apartment">';
                        html +=  apartments[x].display_name+' <span onclick="removePromocodeApartment(this)">&times;</span>';
                        html += '<input type="hidden" name="apartment_ids[]" value="'+apartments[x].ID+'" >';
                        html += '</div>';
                    }

                    $promoCodeModal.find('.apartment_ids').html(html);


                    ambassador = promoCodes[i].ambassador;
                    html = '';
                    if(promoCodes[i].ambassador_id > 0 && typeof ambassador.ID != 'undefined'){
    
                        html += '<div class="ambassador">';
                        html +=  ambassador.email+' <span onclick="removePromocodeUser(this)">&times;</span>';
                        html += '<input type="hidden" name="ambassador_id" value="'+ambassador.ID+'" >';
                        html += '</div>';
                    }

                    $promoCodeModal.find('.ambassador_ids').html(html);


                    $promoCodeModal.find('.modal-title').html(vvPromocodeI18n.editPromoCode);
                    $promoCodeModal.modal('show');
                }
            }
        });



        $promoCodeModal.find('input[name="apartment_search"]').autoComplete({
            resolverSettings: {
                url: '<?php echo vv_admin_url().'?vv_action=apartment_autocomplete' ?>'
            }
        });

        $promoCodeModal.find('input[name="apartment_search"]').on('keyup',function (){

        });
        $promoCodeModal.find('input[name="apartment_search"]').on('autocomplete.select', function (evt, item) {
            console.log(item);
            if(typeof item == 'undefined'){
                
            }else{
                html = '<div class="apartment">';
                html +=  item.text+' <span onclick="removePromocodeApartment(this)">&times;</span>';
                html += '<input type="hidden" name="apartment_ids[]" value="'+item.value+'" >';
                html += '</div>';

                $promoCodeModal.find('.apartment_ids').append(html);
                $promoCodeModal.find('input[name="apartment_search"]').val('');

            }
        });    

        $promoCodeModal.find('input[name="ambassador_search"]').autoComplete({
            resolverSettings: {
                url: '<?php echo vv_admin_url().'?vv_action=users_autocomplete' ?>'
            }
        });

        $promoCodeModal.find('input[name="ambassador_search"]').on('keyup',function (){

        });
        $promoCodeModal.find('input[name="ambassador_search"]').on('autocomplete.select', function (evt, item) {
            console.log(item);
            if(typeof item == 'undefined'){
                
            }else{
                html = '<div class="ambassador">';
                html +=  item.text+' <span onclick="removePromocodeUser(this)">&times;</span>';
                html += '<input type="hidden" name="ambassador_id" value="'+item.value+'" >';
                html += '</div>';

                $promoCodeModal.find('.ambassador_ids').html(html);
                $promoCodeModal.find('input[name="ambassador_search"]').val('');

            }
        });    

    });

</script>
<?php
$footer_codes .= ob_get_clean();
