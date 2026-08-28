<?php
global $wpdb;

$pg_title   = 'Tasks';
vv_show_page_title_bar(['pg_title' => $pg_title, 'add_link' => vv_admin_url('tasks/add'), 'add_txt' => 'Add New Task']);
$meta_title = vv_admin_meta_title( $pg_title );


$filter = ['per_page' => 30, 'pgnum' => 1, 'return_total' => 1, 'vv_task_level' => 'customer'];


$folder         = 'tasks';
$data           = $this->tasks_class->get_tasks($filter);
$tasks          = gArrayItem($data,'tasks');
$total_tasks    = gArrayItem($data,'total_tasks');
//echo print_r_pre($tasks);

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
                        <th class="text-left"><?php vv_e( 'Title' ); ?></th>
                        <th class="text-left"><?php vv_e( 'Assigned to' ); ?></th>
                        <th class="text-left"><?php vv_e( 'Status' ); ?></th>
                        <th style="width:40px">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach($tasks as $task){
                        $task_id    = $task['ID'];
                        $data       = (array) $task['data'];
                        $status     = vv_get_status_name($data['task_status']);

                        $staff_id = intval(get_post_meta($task_id,'staff_id',true));
                        $assignee = vv__( 'Staff' );
                        if($staff_id  > 0){
                            $firstname = get_user_meta($staff_id,'first_name',true);
                            $Lastname   = get_user_meta($staff_id,'last_name',true);
                            $assignee   = $firstname .' '.$lastname;
                        }

                        $status = vv_get_status_name($task['post_status']);
                        ?>
                        <tr data-Customer_id="<?php echo $task_id ?>">
                            <td>
                                <input type="checkbox" name="task_id[]" id="task_<?php echo $task_id ?>" value="<?php echo $task_id ?>" required>
                            </td>
                            <td><a href="<?php echo vv_admin_url().$folder.'/details?id='.$task_id ?>" ><?php echo $task['post_title'] ?></a></td>
                            <td><a hre="<?php echo vv_admin_url('staffs').'staff='.$staff_id ?>" targer="_blank"><?php echo ucwords($assignee) ?></a></td>
                            <td><span class="badge status-<?php echo strtolower(str_replace(" ","-",$status)) ?>" ><?php echo $status ?></span></td>
                            <td class="text-right actions">
                                <div class="dropdown">
                                    <?php echo 'staff_id= '.$staff_id ?>
                                    <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-bars"></i></button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="<?php echo vv_admin_url($folder.'/edit').'?id='.$task_id ?>" ><?php vv_e( 'Edit' ); ?></a>
                                        <a class="dropdown-item" href="<?php echo vv_admin_url($folder.'/details').'?id='.$task_id ?>" ><?php vv_e( 'Details' ); ?></a>
                                        <a class="dropdown-item btnDeleteCustomer" data-task_id="<?php echo $task_id ?>" href="#"  ><?php vv_e( 'Delete' ); ?></a>
                                        <?php if($staff_id > 0){
                                            ?>
                                            <a class="dropdown-item" href="<?php echo vv_admin_url($folder.'/list').'?vv_action=resend_task_created_notifiacation&id='.$task_id ?>" onclick="return confirm('<?php echo esc_js( vv__( 'Resend Task Created Notification?' ) ); ?>')" ><?php vv_e( 'Resend Task created email' ); ?></a>
                                            <?php
                                        }
                                        ?>
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
                $('input[name="task_id[]"]').prop('checked',$(this).is(':checked'));
            });

            $('.btnDeleteCustomer').click(function (e){
                if(confirm('<?php echo esc_js( vv__( 'Delete Task?' ) ); ?>')){
                    window.location = '<?php echo vv_admin_url().'?vv_action=delete_task&id=' ?>' + $(this).attr('data-task_id');
                }
                e.preventDefault();
            });
        });
    </script>
<?php $foot_codes = ob_get_clean() ?>
