<?php
/**
 *
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

get_header(); ?>     
	<div id="city_wrap">
		<div class="container">
			<h1 class="heading-2">Available Apartments in <br/>Ho Chi Minh City (25)</h1>
			<div class="city_filter">
				<div class="city_filter_left">
				<select name="boxes" class="sel_filter"><option value="all">Rooms & spaces</option><option value="opt1">Option 1</option><option value="opt2">Option 2</option><option value="opt3">Option 3</option></select>
					<select><option>Price</option></select>
					<select><option>Sort by recommended</option></select>
				</div>
				<div class="city_filter_right">
					<a href="#" class="clear_all_btn">Clear All</a>
				</div>		
			</div>
			<div class="city_feat">	
				<ul>
					<li class="all opt1"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/beach.svg"><img src="<?php bloginfo('template_url'); ?>/images/beach_h.svg" class="h_icon"></span>Near beach (0)</a></li>
					<li class="all opt2"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/view_to_city.svg"><img src="<?php bloginfo('template_url'); ?>/images/view_to_city_h.svg" class="h_icon"></span>view to city</a></li>
					<li class="all opt3"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/sea_view.svg"><img src="<?php bloginfo('template_url'); ?>/images/sea_view_h.svg" class="h_icon"></span>sea view</a></li>
					<li class="all opt4"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/mountain_view.svg"><img src="<?php bloginfo('template_url'); ?>/images/mountain_view_h.svg" class="h_icon"></span>mountain view</a></li>
					<li class="all"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/fitness.svg"><img src="<?php bloginfo('template_url'); ?>/images/fitness_h.svg" class="h_icon"></span>fitness</a></li>
					<li class="all"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/swimming_pool.svg"><img src="<?php bloginfo('template_url'); ?>/images/swimming_pool_h.svg" class="h_icon"></span>swimming pool</a></li>
					<li class="all"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/kitchen.svg"><img src="<?php bloginfo('template_url'); ?>/images/kitchen_h.svg" class="h_icon"></span>kitchen</a></li>
					<li class="all"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/security_cameras.svg"><img src="<?php bloginfo('template_url'); ?>/images/security_cameras_h.svg" class="h_icon"></span>security cameras</a></li>
				</ul>	
			</div>	
			<div class="city_display_title">
				<div class="city_tab">
					<a href="#tab_content_2" class="tab_btn">
						<span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/home.svg"><img src="<?php bloginfo('template_url'); ?>/images/home_h.svg" class="h_icon"></span>
						Districts
					</a>
					<a href="#tab_content_1" class="tab_btn active">
					<span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/appartment.svg"><img src="<?php bloginfo('template_url'); ?>/images/appartment_h.svg" class="h_icon"></span>
						Apartments
					</a>
				</div>	
			</div>
			<div class="city_app_wrap tab_content show_div" id="tab_content_1">
				<div class="city_display_title_left">
					<h3>Now displaying 25 Apartments from Ho Chi Minh City</h3>
				</div>
				<div class="row">
					<div class="col-sm-8">
						<div class="city_app_list">
							<div class="row">
								<div class="col-md-4 col-sm-6">
									<div class="city_app_block_1">
										<a href="#" class="city_app_block_1_inner">
											<div class="img"><img src="<?php bloginfo('template_url'); ?>/images/app_1.png" alt=""></div>
											<span class="fav"></span>
											<span class="discount">25%</span>
											<div class="cap">
												<div class="price"><strong>1.500 NOK</strong>1.050 NOK</div>
												<p>Luxury Aprt 3BR – Zenity , Center View , D1</p>
											</div>
										</a>	
									</div>	
								</div>
								<div class="col-md-4 col-sm-6">
									<div class="city_app_block_1">
										<a href="#" class="city_app_block_1_inner">
											<div class="img"><img src="<?php bloginfo('template_url'); ?>/images/app_2.png" alt=""></div>
											<span class="fav"></span>
											<div class="cap">
												<div class="price">1.050 NOK</div>
												<p>3BR, Luksus Oslo Aprt, Zenity, utsikt Bitexco #1</p>
											</div>
										</a>	
									</div>	
								</div>
								<div class="col-md-4 col-sm-6">
									<div class="city_app_block_1">
										<a href="#" class="city_app_block_1_inner">
											<div class="img"><img src="<?php bloginfo('template_url'); ?>/images/app_3.png" alt=""></div>
											<span class="fav"></span>
											<span class="discount">25%</span>
											<div class="cap">
												<div class="price"><strong>1.500 NOK</strong>1.050 NOK</div>
												<p>Luxury Aprt 3BR – Zenity , Center View , D1</p>
											</div>
										</a>	
									</div>	
								</div>
								<div class="col-md-4 col-sm-6">
									<div class="city_app_block_1">
										<a href="#" class="city_app_block_1_inner">
											<div class="img"><img src="<?php bloginfo('template_url'); ?>/images/app_1.png" alt=""></div>
											<span class="fav"></span>
											<span class="discount">25%</span>
											<div class="cap">
												<div class="price"><strong>1.500 NOK</strong>1.050 NOK</div>
												<p>Luxury Aprt 3BR – Zenity , Center View , D1</p>
											</div>
										</a>	
									</div>	
								</div>
								<div class="col-md-4 col-sm-6">
									<div class="city_app_block_1">
										<a href="#" class="city_app_block_1_inner">
											<div class="img"><img src="<?php bloginfo('template_url'); ?>/images/app_2.png" alt=""></div>
											<span class="fav"></span>
											<div class="cap">
												<div class="price">1.050 NOK</div>
												<p>3BR, Luksus Oslo Aprt, Zenity, utsikt Bitexco #1</p>
											</div>
										</a>	
									</div>	
								</div>
								<div class="col-md-4 col-sm-6">
									<div class="city_app_block_1">
										<a href="#" class="city_app_block_1_inner">
											<div class="img"><img src="<?php bloginfo('template_url'); ?>/images/app_3.png" alt=""></div>
											<span class="fav"></span>
											<span class="discount">25%</span>
											<div class="cap">
												<div class="price"><strong>1.500 NOK</strong>1.050 NOK</div>
												<p>Luxury Aprt 3BR – Zenity , Center View , D1</p>
											</div>
										</a>	
									</div>	
								</div>
							</div>
							<div class="show_more_btn"><a href="#">Show more</a></div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="city_map">
							<img src="<?php bloginfo('template_url'); ?>/images/map_img.png" alt="">
						</div>
					</div>
				</div>
			</div>
			<div class="city_app_wrap tab_content" id="tab_content_2">
				<div class="city_display_title_left">
					<h3>Now displaying 10 District   from Ho Chi Minh City</h3>
				</div>
				<div class="row">
					<div class="col-sm-8">
						<div class="city_app_list">
							<div class="row">
								<div class="col-md-4 col-sm-6">
									<div class="district_block_1">
										<div class="district_block_1_inner">
											<a href="https://visitvietnam.dev.wiise.no/luxury-the-marq-district-1/">
                                                <div class="img"><img src="<?php bloginfo('template_url'); ?>/images/district_img_1.png" alt=""></div>
                                                <div class="cap_1">
                                                    <p>Ho Chi Minh City</p>
                                                    <h5>Luxury The MarQ, District 1</h5>
                                                </div>
											</a>
                                            <div class="cap_2">
												<span class="date_text">25 apartsments available</span>
												<div class="btn_wrap">
													<a href="#city_app_list" class="btn view_btn">Show apartments</a>
												</div>
											</div>	
										</div>	
									</div>	
								</div>
								<div class="col-md-4 col-sm-6">
									<div class="district_block_1">
										<div class="district_block_1_inner">
											<a href="https://visitvietnam.dev.wiise.no/luxury-the-marq-district-1/">
                                                <div class="img"><img src="<?php bloginfo('template_url'); ?>/images/district_img_2.png" alt=""></div>
                                                <div class="cap_1">
                                                    <p>Ho Chi Minh City</p>
                                                    <h5>Luxury The MarQ, District 1</h5>
                                                </div>
											</a>
                                            <div class="cap_2">
												<span class="date_text">25 apartsments available</span>
												<div class="btn_wrap">
													<a href="#city_app_list" class="btn view_btn">Show apartments</a>
												</div>
											</div>	
										</div>	
									</div>	
								</div>
								<div class="col-md-4 col-sm-6">
									<div class="district_block_1">
										<div class="district_block_1_inner">
											<a href="https://visitvietnam.dev.wiise.no/luxury-the-marq-district-1/">
                                                <div class="img"><img src="<?php bloginfo('template_url'); ?>/images/district_img_1.png" alt=""></div>
                                                <div class="cap_1">
                                                    <p>Ho Chi Minh City</p>
                                                    <h5>Luxury The MarQ, District 1</h5>
                                                </div>
											</a>
                                            <div class="cap_2">
												<span class="date_text">25 apartsments available</span>
												<div class="btn_wrap">
													<a href="#city_app_list" class="btn view_btn">Show apartments</a>
												</div>
											</div>	
										</div>	
									</div>	
								</div>
								<div class="col-md-4 col-sm-6">
									<div class="district_block_1">
										<div class="district_block_1_inner">
											<a href="https://visitvietnam.dev.wiise.no/luxury-the-marq-district-1/">
                                                <div class="img"><img src="<?php bloginfo('template_url'); ?>/images/district_img_1.png" alt=""></div>
                                                <div class="cap_1">
                                                    <p>Ho Chi Minh City</p>
                                                    <h5>Luxury The MarQ, District 1</h5>
                                                </div>
											</a>
                                            <div class="cap_2">
												<span class="date_text">25 apartsments available</span>
												<div class="btn_wrap">
													<a href="#city_app_list" class="btn view_btn">Show apartments</a>
												</div>
											</div>	
										</div>	
									</div>	
								</div>
								<div class="col-md-4 col-sm-6">
									<div class="district_block_1">
										<div class="district_block_1_inner">
                                        	<a href="https://visitvietnam.dev.wiise.no/luxury-the-marq-district-1/">
                                                <div class="img"><img src="<?php bloginfo('template_url'); ?>/images/district_img_2.png" alt=""></div>
                                                <div class="cap_1">
                                                    <p>Ho Chi Minh City</p>
                                                    <h5>Luxury The MarQ, District 1</h5>
                                                </div>
											</a>
                                            <div class="cap_2">
												<span class="date_text">25 apartsments available</span>
												<div class="btn_wrap">
													<a href="#city_app_list" class="btn view_btn">Show apartments</a>
												</div>
											</div>	
										</div>	
									</div>	
								</div>
								<div class="col-md-4 col-sm-6">
									<div class="district_block_1">
										<div class="district_block_1_inner">
											<a href="https://visitvietnam.dev.wiise.no/luxury-the-marq-district-1/">
                                                <div class="img"><img src="<?php bloginfo('template_url'); ?>/images/district_img_1.png" alt=""></div>
                                                <div class="cap_1">
                                                    <p>Ho Chi Minh City</p>
                                                    <h5>Luxury The MarQ, District 1</h5>
                                                </div>
											</a>
                                            <div class="cap_2">
												<span class="date_text">25 apartsments available</span>
												<div class="btn_wrap">
													<a href="#city_app_list" class="btn view_btn">Show apartments</a>
												</div>
											</div>	
										</div>	
									</div>	
								</div>
                                <div class="city_app_list city_app_list_filtred" id="city_app_list">
                                    <div class="city_display_title_left">
                                        <a href="#" class="back_btn">back</a>
                                        <h3>Now displaying 4 apartments from Luxury The MarQ, District 1</h3>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 col-sm-6">
                                            <div class="city_app_block_1">
                                                <a href="https://visitvietnam.dev.wiise.no/apartments/da-nang-city-3br-luksus-oslo-aprt-zenity-utsikt-bitexco-1-2/" class="city_app_block_1_inner">
                                                    <div class="img"><img width="490" height="372" src="https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/10/img_03-1.png" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" decoding="async" srcset="https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/10/img_03-1.png 490w, https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/10/img_03-1-300x228.png 300w" sizes="(max-width: 490px) 100vw, 490px"></div>
                                                    <span class="fav"></span>
                                                                                                                    <div class="cap">
                                                        <div class="price"><strong>1.500 NOK</strong>1.050 NOK</div>                                                                    <p>Da Nang city 3BR, Luksus Oslo Aprt, Zenity, utsikt Bitexco #1</p>
                                                    </div>
                                                </a>	
                                            </div>	
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="city_app_block_1">
                                                <a href="https://visitvietnam.dev.wiise.no/apartments/ho-chi-minh-city-3br-luksus-oslo-aprt-zenity-utsikt-bitexco-1/" class="city_app_block_1_inner">
                                                    <div class="img"><img width="490" height="372" src="https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/10/img_03-1.png" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" decoding="async" srcset="https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/10/img_03-1.png 490w, https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/10/img_03-1-300x228.png 300w" sizes="(max-width: 490px) 100vw, 490px"></div>
                                                    <span class="fav"></span>
                                                    <span class="discount">25%</span>                                                                <div class="cap">
                                                        <div class="price"><strong>1.500 NOK</strong>1.050 NOK</div>                                                                    <p>Ho Chi Minh City 3BR, Luksus Oslo Aprt, Zenity, utsikt Bitexco #1</p>
                                                    </div>
                                                </a>	
                                            </div>	
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="city_app_block_1">
                                                <a href="https://visitvietnam.dev.wiise.no/apartments/da-nang-city-3br-luksus-oslo-aprt-zenity-utsikt-bitexco-1/" class="city_app_block_1_inner">
                                                    <div class="img"><img width="490" height="372" src="https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/10/img_03-1.png" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" decoding="async" srcset="https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/10/img_03-1.png 490w, https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/10/img_03-1-300x228.png 300w" sizes="(max-width: 490px) 100vw, 490px"></div>
                                                    <span class="fav"></span>
                                                    <span class="discount">25%</span>                                                                <div class="cap">
                                                        <div class="price"><strong>1.500 NOK</strong>1.050 NOK</div>                                                                    <p>Da Nang city 3BR, Luksus Oslo Aprt, Zenity, utsikt Bitexco #1</p>
                                                    </div>
                                                </a>	
                                            </div>	
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="city_app_block_1">
                                                <a href="https://visitvietnam.dev.wiise.no/apartments/3br-luksus-oslo-aprt-zenity-utsikt-bitexco-1-da-nang-city/" class="city_app_block_1_inner">
                                                    <div class="img"><img width="490" height="372" src="https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/10/img_03-1.png" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" decoding="async" srcset="https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/10/img_03-1.png 490w, https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/10/img_03-1-300x228.png 300w" sizes="(max-width: 490px) 100vw, 490px"></div>
                                                    <span class="fav"></span>
                                                    <span class="discount">25%</span>                                                                <div class="cap">
                                                        <div class="price"><strong>1.500 NOK</strong>1.050 NOK</div>                                                                    <p>Da Nang city 3BR, Luksus Oslo Aprt, Zenity, utsikt Bitexco #1</p>
                                                    </div>
                                                </a>	
                                            </div>	
                                        </div>
                                    </div>
                                </div>
							</div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="city_map">
							<img src="<?php bloginfo('template_url'); ?>/images/map_img.png" alt="">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="city_visit">		
			<div class="container">
				<h2>Excited to visit the amaxing <br/>Ho Chi Minh City?</h2>
				<p>Click here to read more about the city and the unique attraction</p>
				<a href="#" class="btn">Explore now</a>
				<div class="img_wrap">
					<div class="img_1"><img src="<?php bloginfo('template_url'); ?>/images/app_4.png" alt=""></div>
					<div class="img_2"><img src="<?php bloginfo('template_url'); ?>/images/app_5.png" alt=""></div>
					<div class="img_3"><img src="<?php bloginfo('template_url'); ?>/images/app_6.png" alt=""></div>
				</div>	
			</div>
		</div>
        <div id="search_form_app">
			<div class="container">
				<div class="row">
					<div class="col-sm-6">
						<div class="search_form_app_left">
							<h2>Explore More Exclusive Apartments from Visit Vietnam</h2>
							<p>Discover our wide range of apartments to find the perfect apartment to suit your lifestyle.</p>
							<div class="desc">
								<h4>Free Membership and Rewards</h4>
								<ul>
									<li>Save point and get discount on your next stay</li>
									<li>Register and win exclusive stays for your family</li>
									<li>Get discount on the most popular apartments</li>
									<li>Be the first to know of new offers</li>
								</ul>	
							</div>
							<hr/>
							<div class="desc">
								<h4>Why choose Stays from Visit Vietnam</h4>
								<ul>
									<li>Luxury apartments in high quality</li>
									<li>Private swimming pool included</li>
									<li>Perfect environment for family and business trips</li>
								</ul>	
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="search_form_app_right">
							<h4>Do you need help for <br/>your search?</h4>
                            <?php echo do_shortcode('[contact-form-7 id="07ed583" title="Search form"]'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="other_city">	
			<div class="container">
				<h2>Other Cities</h2>
				<div class="row">
					<div class="col-sm-4">
						<a href="#" class="other_city_block" style="background-image:url(<?php bloginfo('template_url'); ?>/images/app_7.png);">
							<div class="cap">
								<h3>Da Nang</h3>	
							</div>
							<span class="btn">Show available apartments</span>	
						</a>
					</div>
					<div class="col-sm-4">
						<a href="#" class="other_city_block" style="background-image:url(<?php bloginfo('template_url'); ?>/images/app_7.png);">
							<div class="cap">
								<h3>Hanoi</h3>	
							</div>
							<span class="btn">Show available apartments</span>	
						</a>
					</div>
					<div class="col-sm-4">
						<a href="#" class="other_city_block" style="background-image:url(<?php bloginfo('template_url'); ?>/images/app_7.png);">
							<div class="cap">
								<h3>Nha Trang</h3>	
							</div>
							<span class="btn">Show available apartments</span>	
						</a>
					</div>
				</div>
			</div>	
		</div>	
	</div>

<?php get_footer(); ?>