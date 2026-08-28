<?php
/**
 * Template Name: Booking Confirmation
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

get_header(); ?>

<div id="booking_confirmation">
	<div class="container">
    	<div class="booking_confirmation_inn">
            <h2><a href="#" class="back_arr"></a>Booking confirmation</h2>
            <div class="row">
                <div class="col-sm-7">
                    <div class="booking_conf_left">
                        <div class="order_details">
                            <h4>Order details</h4>
                            <ul>
                                <li>Dates<strong>29. des 2023 – 2. Jan 2024 </strong><a href="#" class="change_btn">Change</a></li>
                                <li>Guests<strong>6 guests  </strong><a href="#" class="change_btn">Change</a></li>
                                <li>Airport Pick-up<strong>No </strong><a href="#" class="change_btn">Change</a></li>
                            </ul>
                        </div>
                        <hr>
                        <div class="payment_method">
                            <h4>Choose how you want to pay</h4>
                            <div class="payment_method_list">
                                <div class="block">
                                    <h5>Pay by card now</h5>
                                    <p>Pay the full amount (8 610,53 kr) now, all clear to go</p>
                                    <a href="#">More information</a>
	                                <input type="radio" name="radio1" checked="checked" />
                                    <span class="circle_btn"></span>
                                    <span class="border_btn"></span>
                                </div>
                                <div class="block">
                                    <h5>Pay when you arrive</h5>
                                    <p>Reserve your order and pay the full amount (8 610,53 kr) when you arrive</p>
                                    <a href="#">More information</a>
	                                <input type="radio" name="radio1" />
                                    <span class="circle_btn"></span>
                                    <span class="border_btn"></span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="login_reg_order">
                        	<h4>Login or register to order</h4>
                            <input type="email" placeholder="Email">
                            <p>We will match up your email and send you a verification to confirm your account.</p>
                            <input type="submit" value="Proceed Order">
                            <div class="or_text"><span>eller</span></div>
                            <div class="soc_btn_list">
                            	<div class="btn_block">
                                	<a href="#" class="soc_btn"><img src="<?php bloginfo('template_url'); ?>/images/fb_btn.png" alt=""></a>
                                </div>
                                <div class="btn_block">
                                    <a href="#" class="soc_btn"><img src="<?php bloginfo('template_url'); ?>/images/google_btn.png" alt=""></a>
                                </div>
                                <div class="btn_block">    
                                    <a href="#" class="soc_btn"><img src="<?php bloginfo('template_url'); ?>/images/apple_btn.png" alt=""></a>
                                </div>
                                <div class="btn_block">    
                                    <a href="#" class="soc_btn"><img src="<?php bloginfo('template_url'); ?>/images/telephone_btn.png" alt="">Fortsett med telefon</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="booking_conf_right">
                        <div class="your_order_block">
                        	<div class="order_list">
                            	<div class="block">
                                	<div class="img"><img src="<?php bloginfo('template_url'); ?>/images/order_img_1.png" alt=""></div>
                                    <div class="desc">
                                    	<h6>Apartment</h6>
                                        <h5>Luxury Aprt 3BR – Zenity, Center View,D1</h5>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="order_total">
                            	<h4>Your order</h4>
                                <ul>
                                	<li><span>1 788,42 kr NOK x 4 nights</span><span>7 153,66 kr NOK</span></li>
                                    <li><span>Cleaning fee</span><span>165, kr NOK</span></li>
                                    <li><span>Order fee</span><span>1 291,64 kr NOK</span></li>
                                    <li><span>Total</span><span>8 610,53 kr NOK</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    	</div>
    </div>
</div>

<?php get_footer(); ?>