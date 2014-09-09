jQuery(document).ready(function() {
	jQuery('#music_seller_add_files').click(function(e) {
		e.preventDefault();
		jQuery('#music_seller_files').append('<input name="music_seller_file[]" type="file" /><br />');
	});
	jQuery('#music_seller_code').click(function(e) {
		this.select();
	});
    try {
        //jQuery('.my-color-field:not(.music_seller_premiumFeature .my-color-field)').wpColorPicker();
        jQuery('.my-color-field').wpColorPicker();
    } catch (e) {

    }
	jQuery('.music_seller_premiumFeature input, .music_seller_premiumFeature .wp-color-result').off().click(function(event) {
        jQuery(this).prop('checked',false);
        if (confirm('Sorry, this feature is available in the Pro version only, would you like to upgrade now?')) {
            window.location = 'http://www.shopfiles.com/index.php/products/music-seller-plugin-for-wordpress';
        }
    });
    if (jQuery('.music_seller_premiumFeature').length < 1) {
        jQuery('.music_seller_upgrade_warning').hide();
    }
});
	function music_seller_update_icons(theDiv) {
        var cartcount = getCookie('cart' + theDiv.id);
        if (cartcount != null && cartcount != "") {
            //a cart exists so load it up
            for (i = 1; i <= cartcount; i++) {
                var thisline = getCookie('cart' + theDiv.id + i);
                if (thisline != null && thisline != "") {
                    ARRline = thisline.split("##");
                    for (j = 0; j < ARRline.length; j++) {
                        var keyname = ARRline[j].substr(0, ARRline[j].indexOf("=")).replace(/^\s+|\s+$/g, "");
                        var keyval = ARRline[j].substr(ARRline[j].indexOf("=") + 1);
                        if (keyname == 'code') {
                            jQuery('#music_seller_' + keyval).click();
                        }
                    }
                }
            }
        }
    }