jQuery(document).ready(function() {
	jQuery("div.PWGP_widget a.img_selector").each(function () {
		var img_size = jQuery(this).attr('title').split('x');
		if ( screen.width > img_size[0] ) jQuery(this).remove();
		if ( screen.height > img_size[1] ) jQuery(this).remove();
		jQuery(this).removeAttr('title');
	});  
	jQuery("div.PWGP_widget a.img_selector").click(function () {
		img_url = jQuery(this).attr('name');
		jQuery('body').css({backgroundAttachment: 'fixed', backgroundImage: 'url(\''+img_url+'\')', backgroundPosition: 'center center', backgroundRepeat: 'no-repeat'}).fadeIn(2000);
	});
});