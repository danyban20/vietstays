<?php
global $wpdb;

$pg_title   = 'Admins';
vv_show_page_title_bar(['pg_title' => $pg_title, 'add_link' => vv_admin_url('admins/add'), 'add_txt' => 'Add New Admin']);
$meta_title = vv_admin_meta_title( $pg_title );


$filter = ['per_page' => 30, 'pgnum' => 1, 'return_total' => 1];

$data           = $this->users_class->get_admins( $filter );
$users          = gArrayItem($data,'users');
$total_users    = gArrayItem($data,'total_users');


$is_admin_list = true;
include($this->views_path.'/users/user_list.php');

?>


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
                if(confirm('<?php echo esc_js( vv__( 'Delete Admin?' ) ); ?>')){
                    window.location = '<?php echo vv_admin_url().'?vv_action=delete_user&id=' ?>' + $(this).attr('data-user_id');
                }
                e.preventDefault();
            });
        });
    </script>
<?php $foot_codes = ob_get_clean() ?>
