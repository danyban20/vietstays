<?php 
if(is_404()) {
	$banner_title = '404';
} elseif(get_post_type()=='post') {
	$banner_image = get_the_post_thumbnail_url(66, 'extra-large');
	$banner_title = get_the_title(66);
} elseif(get_post_type()=='product') {
	$banner_image = get_field('products_banner_image', 'options');
	$banner_title = get_field('products_banner_title', 'options');
} else {
	if(has_post_thumbnail()) { $banner_image = get_the_post_thumbnail_url($post->ID, 'extra-large'); }
	$banner_title = get_the_title();
} 
?>

<?php if($banner_image==true) { ?>
<div id="banner" style="background-image:url(<?php echo $banner_image; ?>);">
<?php } else { ?>
<div id="banner">
<?php } ?>
    <div class="cap">
        <div class="container">
            <h1><?php echo $banner_title; ?></h1>
            <div class="breadcrumb">
                <ul><?php bcn_display_list(); ?></ul>
            </div>
        </div>
    </div>
</div>