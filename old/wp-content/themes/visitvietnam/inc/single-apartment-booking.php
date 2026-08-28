<?php 

//echo '<div style="color:#fff">'.print_r_pre($apartment).'</div>';
$check_in_date 	= gArrayItem($booking,'check_in_date');
$check_out_date = gArrayItem($booking,'check_out_date');
$rooms = gArrayItem($booking,'rooms');
$adults = intval(gArrayItem($booking,'adults'));
$children = intval(gArrayItem($booking,'children'));


if($rooms > gArrayItem($apartment,'rooms')){
	$error = 'This apartment only has '.gArrayItem($apartment,'rooms').' room(s)';
}elseif(($adults + $adults) > gArrayItem($apartment,'max_guests')){
	$error = 'This apartment only allowed '.gArrayItem($apartment,'max_guests').' guest(s)';
}else{

	$datediff = $check_out_date - $check_in_date;

	$num_nights =  round($datediff / (60 * 60 * 24));

	$subtotal = $subtotal * $num_nights;


	$num_nights_str = ($num_nights > 1) ? $num_nights.' nights' : '1 night';

	global $wpdb;


	$result = $wpdb->get_results("	SELECT * FROM vv_bookings 
									WHERE apartment_id = ".gArrayItem($apartment,'ID')." 
									AND (
											(check_out_date >= ".$check_out_date.") 
										)
									LIMIT 10
								");

	echo print_r_pre($results);

	if(count($result) > 0){
		$error = 'Selected dates are unavailable';
	}
}

if($error != ''){
	?>
	<div style="color:#fc790e;text-align: center;"><?php echo $error ?></div>
	<?php 	
}else{
	?>
    <a href="#" class="book_now_btn">Book now</a>
    <div class="app_price">
        <h2>Your price:</h2>
        <ul>
            <li><span class="lbltxt"><?php echo number_format($price,2) ?> x <?php echo $num_nights_str ?></span><span class="valtxt"><?php echo number_format($subtotal,2) ?></span></li>
            <?php 
            if($discount > 0){
                $discount_amt = $subtotal * ($discount / 100);
                $save = $save + $discount_amt;
                $subtotal = $subtotal - $discount_amt;
                ?>
                <li><span class="lbltxt"><?php echo $discount ?>% discount</span><span class="valtxt">- <?php echo number_format($discount_amt,2) ?></span></li>
                <?php 
            }
            $cleaning_fee = gArrayItem($apartment,'cleaning_fee');
            if($cleaning_fee > 0){
                $subtotal = $subtotal + $cleaning_fee;
                ?>
                <li><span class="lbltxt">Cleaning fee</span><span class="valtxt"><?php echo number_format($cleaning_fee,2) ?> kr</span></li>
                <?php 
            }
            ?>
            <li><span class="lbltxt">Total</span>
                <span class="valtxt">
                    <?php 
                    if($save > 0){ 
                        echo '<i>'.number_format($save + $subtotal,2).'</i>';
                    }
                    echo number_format($subtotal,2);
                    ?>
                </span>
            </li>
        </ul>
    </div>
    <?php
}