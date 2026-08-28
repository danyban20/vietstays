<?php
global $wpdb;

$pg_title   = 'Ambassadors';
vv_show_page_title_bar(['pg_title' => $pg_title, 'add_link' => vv_admin_url('ambassadors/add'), 'add_txt' => 'Add New Ambassador']);
$meta_title = vv_admin_meta_title( $pg_title );


$filter = ['per_page' => 30, 'pgnum' => 1, 'return_total' => 1, 'vv_user_level' => 'ambassador'];



$data           = $this->users_class->get_users($filter);
$users          = gArrayItem($data,'users');
$total_users    = gArrayItem($data,'total_users');


$is_admin_list = false;

?>

<div class="row">
    <div class="col">
      <div class="au-card" style="overflow: auto;">
        <div class="table-responsive table--no-card m-b-30">
            <table class="table table-borderless table-striped table-earning Customers_list">
                <thead>
                    <tr>
                        <th style="width:20px">
                            <input name="checkAll" type="checkbox" value="1" >
                        </th>
                        <th class="text-left"><?php vv_e( 'Email' ); ?></th>
                        <th class="text-left"><?php vv_e( 'Name' ); ?></th>
                        <th class="text-left"><?php vv_e( 'Bookings' ); ?></th>
                        <th style="width:150px"><?php vv_e( 'Status' ); ?></th>
                        <th style="width:40px">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach($users as $user){
                        $user_id    = $user['ID'];
                        $data       = (array) $user['data'];
                        $status     = vv_get_status_name($data['user_status']);

                        $user_level = get_user_meta($user_id,'vv_user_level',true);
                        if($user_level == '') $user_level = 'customer';

                        $num_apartments = 0;
                        if($user_level == 'partner' || $user_level == 'administration'){
                            $num_apartments = $this->apartment_class->get_num_apartments($user_id);
                        }

                        $r = $this->booking_class->get_bookings(['ambassador_id' => $user_id, 'return_total_only' => 1, 'return_total' => 1]);
                        $num_bookings = intval(gArrayItem($r,'total_rows'));

                        //echo print_r_pre($user);
                        $host_num = date("Ymd",strtotime($data['user_registered'])).$user_id;

                        $user_level = get_user_meta($user_id,'vv_user_level',true);
                        $folder = $user_level;
                        if($folder == '') $folder = 'user';
                        $folder .= 's';

                        ?>
                        <tr data-Customer_id="<?php echo $user_id ?>">
                            <td>
                                <input type="checkbox" name="user_id[]" id="user_<?php echo $user_id ?>" value="<?php echo $user_id ?>" required>
                            </td>
                            <td><a href="<?php echo vv_admin_url().$folder.'/edit?id='.$user_id ?>" ><?php echo $data['user_email'] ?></a></td>
                            <td><?php echo stripslashes($data['display_name']) ?></td>
                            <td><a href="<?php echo vv_admin_url('booking').'?ambassador_id='.$user_id ?>" target="_blank" ><?php echo $num_bookings ?></a></td>
                            <td><span class="badge status-<?php echo strtolower(str_replace(" ","-",$status)) ?>" ><?php echo $status ?></span></td>
                            <td class="text-right actions">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-bars"></i></button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="<?php echo vv_admin_url($folder.'/edit').'?id='.$user_id ?>" ><?php vv_e( 'Edit' ); ?></a>
                                        <a class="dropdown-item btnDeleteCustomer" data-user_id="<?php echo $user_id ?>" href="#"  ><?php vv_e( 'Delete' ); ?></a>
                                    </div>
                                </div>                            
                            </td>
                        </tr>
                        <?php 
                    }
                    ?>
                </tbody>
            </table>
        </div>
      </div>
    </div><!-- .col -->
</div>




<?php ob_start() ?>
    <style>
    .Customer_calender{
        border-top:1px solid #024041;
        border-left:1px solid #024041;
        width:100%;
    }
    .Customer_calender th{
        border-right:1px solid #024041;
        border-bottom: 1px solid #024041;
        text-align: center;
        padding:10px;
        background: #024041;
        color:#fff;
        font-size:10px;
    }
    .Customer_calender .Customer_name{
        text-align: left;
        font-weight: normal;
        width:300px;
        min-width: 300px;
        font-size: 12px;
        background: #fff;
        color:#000;
    }
    .Customer_calender td{
        border-right:1px solid #024041;
        border-bottom: 1px solid #024041;;
        overflow: hidden;
        width:40px;
    }
    .Customer_calender td > div{
        font-size:12px;
    }
    .Customer_calender td.booked{
        background: #93D050;
    }

    </style>
<?php $head_codes = ob_get_clean(); ?>


<?php ob_start() ?>
    <script type="text/javascript">
        $(document).ready(function (){
            $('input[name="checkAll"]').click(function (){
                $('input[name="user_id[]"]').prop('checked',$(this).is(':checked'));
            });

            $('.btnDeleteCustomer').click(function (e){
                if(confirm('<?php echo esc_js( vv__( 'Delete Ambassador?' ) ); ?>')){
                    window.location = '<?php echo vv_admin_url().'?vv_action=delete_user&id=' ?>' + $(this).attr('data-user_id');
                }
                e.preventDefault();
            });
        });
    </script>
<?php $foot_codes = ob_get_clean() ?>
