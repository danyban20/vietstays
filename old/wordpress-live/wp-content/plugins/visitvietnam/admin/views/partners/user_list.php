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
                        <th style="width:150px"><?php vv_e( 'Status' ); ?></th>
                        <th style="width:40px">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach($customers as $customer){
                        $customer_id    = $customer['ID'];
                        $data           = (array) $customer['data'];
                        $status         = vv_get_status_name($data['user_status']);
                        ?>
                        <tr data-Customer_id="<?php echo $customer_id ?>">
                            <td>
                                <input type="checkbox" name="customer_id[]" id="customer_<?php echo $customer_id ?>" value="<?php echo $customer_id ?>" required>
                            </td>
                            <td><a href="<?php echo vv_admin_url().'customers/edit?id='.$customer_id ?>" ><?php echo $data['user_email'] ?></a></td>
                            <td><?php echo stripslashes($data['display_name']) ?></td>
                            <td><span class="badge status-<?php echo strtolower(str_replace(" ","-",$status)) ?>" ><?php echo $status ?></span></td>
                            <td class="text-right actions">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-bars"></i></button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <?php 
                                        if($is_admin_list){
                                            ?>
                                            <a class="dropdown-item" href="<?php echo vv_admin_url('admins/edit').'?id='.$customer_id ?>" ><?php vv_e( 'Edit' ); ?></a>
                                            <a class="dropdown-item" href="<?php echo vv_admin_url('services').'?assignee='.$customer_id ?>" ><?php vv_e( 'View Services' ); ?></a>
                                            <?php
                                        }else{
                                            ?>
                                            <a class="dropdown-item" href="<?php echo vv_admin_url('customers/edit').'?id='.$customer_id ?>" ><?php vv_e( 'Edit' ); ?></a>
                                            <a class="dropdown-item" href="<?php echo vv_admin_url('services').'?customer='.$customer_id ?>" ><?php vv_e( 'View Services' ); ?></a>
                                            <?php
                                        }
                                        ?>
                                        <a class="dropdown-item btnDeleteCustomer" data-customer_id="<?php echo $customer_id ?>" href="#"  ><?php vv_e( 'Delete' ); ?></a>
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
