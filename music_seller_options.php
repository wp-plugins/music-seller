<?php
// create custom plugin settings menu

include_once('functions.php');

add_action('admin_menu', 'music_seller_create_menu');

add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );
function mw_enqueue_color_picker( $hook_suffix ) {
    // first check that $hook_suffix is appropriate for your admin page
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'music_seller_color_picker', plugins_url('js/music_seller.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}

include_once('functions.php');
function music_seller_create_menu() {

	//create new top-level menu
	add_options_page('Music Seller', 'Music Seller', 'manage_options', 'music_seller_options.php', 'music_seller_settings_page');

}

if ( is_admin() ){ // admin actions
	add_action( 'admin_init', 'register_music_seller_store_settings' );
} else {
	// non-admin enqueues, actions, and filters
}

class music_seller_WPOptions {

	var $paypal_currency = 'USD';

	var $paypal_return_button_text = 'Click here to go to download page';

	var $paypal_verify_transactions = 0;

	var $link_expiration = '1 month';

	var $email_delivery = 1;

	var $downloads_limit = 3;

    var $button_color_1 = '#52A8E8';

    var $button_color_2 = '#377AD0';

    var $border_color = '#20559A';

    var $button_color_1_hover = '#54A1D8';

    var $button_color_2_hover = '#1967CA';

    var $border_color_hover = '#20559A';

    var $music_seller_show_preview = 0;
	
    var $music_seller_file_title = '%%artist%% - %%title%% (%%year%%)';
    
	var $thankyou_page = '<h1>Thank you for your order %%first_name%% %%last_name%%!</h1>You have successfully completed the order process! Please use the link(s) below to download your copy:<strong>Use the link below the start the download:</strong>%%downloadlink%%<h3>Details for your order:</h3>Order #: %%order_id%%Transaction: %%txn_id%%Amount: %%mc_currency%% %%mc_gross%%<strong>Your password is: %%payer_email%%</strong>Thank you!';

	var $email_delivery_text = '<h1>Thank you for your order %%first_name%% %%last_name%%!</h1>
You have successfully completed the order process! Please use the link(s) below to download your copy:

%%downloadlink%%
<h3>Details for your order:</h3>
Order #: %%order_id%%
Transaction: %%txn_id%%
Amount: %%mc_currency%% %%mc_gross%%

Thank you!';

}

function register_music_seller_store_settings() {

	//register our settings
	register_setting( 'music_seller-settings-group', 'music_seller_paypal_account' );
	register_setting( 'music_seller-settings-group', 'music_seller_file_title' );
	register_setting( 'music_seller-settings-group', 'music_seller_paypal_return_button_text' );
	register_setting( 'music_seller-settings-group', 'music_seller_link_expiration' );
	register_setting( 'music_seller-settings-group', 'music_seller_email_delivery' );
	register_setting( 'music_seller-settings-group', 'music_seller_attach_files' );
	register_setting( 'music_seller-settings-group', 'music_seller_thankyou_page' );
	register_setting( 'music_seller-settings-group', 'music_seller_email_delivery_text' );
	register_setting( 'music_seller-settings-group', 'music_seller_email_delivery_subject' );
	register_setting( 'music_seller-settings-group', 'music_seller_paypal_sandbox' );
	register_setting( 'music_seller-settings-group', 'music_seller_paypal_verify_transactions' );
	register_setting( 'music_seller-settings-group', 'music_seller_vat_percent' );
	//register_setting( 'music_seller-settings-group', 'pdf_orientation' );
	register_setting( 'music_seller-settings-group', 'music_seller_paypal_currency' );
	register_setting( 'music_seller-settings-group', 'music_seller_paypal_language' );
	register_setting( 'music_seller-settings-group', 'music_seller_downloads_limit' );
    register_setting( 'music_seller-settings-group', 'music_seller_show_preview' );
    
    register_setting( 'music_seller-settings-group', 'music_seller_button_color_1' );
    register_setting( 'music_seller-settings-group', 'music_seller_button_color_2' );
    register_setting( 'music_seller-settings-group', 'music_seller_border_color' );
    register_setting( 'music_seller-settings-group', 'music_seller_button_color_1_hover' );
    register_setting( 'music_seller-settings-group', 'music_seller_button_color_2_hover' );
    register_setting( 'music_seller-settings-group', 'music_seller_border_color_hover' );
    
    register_setting( 'music_seller-settings-group', 'music_seller_color_scheme' );

	//email_delivery_subject
	
}



function music_seller_settings_page() {
    global $music_seller_versionClass;
    global $music_seller_premiumFeature;
	$op = new music_seller_WPOptions();
    wp_enqueue_style('music_seller_style', plugins_url( '/css/music_seller.css' , __FILE__ ));
$ppcurencies = array('USD' => 'US Dollar',
'EUR' => 'Euro',
'ILS' => 'Israeli New Sheqel',
'GBP' => 'Pounds Sterling',
'AUD' => 'Australian Dollar',
'CAD' => 'Canadian Dollar',
'JPY' => 'Japan Yen',
'NZD' => 'New Zealand Dollar',
'CHF' => 'Swiss Franc',
'HKD' => 'Hong Kong Dollar',
'SGD' => 'Singapore Dollar',
'SEK' => 'Sweden Krona',
'DKK' => 'Danish Krone',
'PLN' => 'New Zloty',
'NOK' => 'Norwegian Krone',
'HUF' => 'Forint',
'CZK' => 'Czech Koruna',
'BRL' => 'Brazilian Real',
'TWD' => 'Taiwan New Dollar',
'TRY' => 'Turkish Lira',
'THB' => 'Thai Baht');
	$lc = array(
'' => '-- Optional --',
'AU' => 'Australia',
'AT' => 'Austria',
'BE' => 'Belgium',
'BR' => 'Brazil',
'CA' => 'Canada',
'CH' => 'Switzerland',
'CN' => 'China',
'DE' => 'Germany',
'ES' => 'Spain',
'GB' => 'United Kingdom',
'FR' => 'France',
'IT' => 'Italy',
'NL' => 'Netherlands',
'PL' => 'Poland',
'PT' => 'Portugal',
'RU' => 'Russia',
'US' => 'United States',
'da_DK' => 'Danish',
'he_IL' => 'Hebrew',
'id_ID' => 'Indonesian',
'jp_JP' => 'Japanese',
'no_NO' => 'Norwegian',
'pt_BR' => 'Brazilian Portuguese',
'ru_RU' => 'Russian',
'sv_SE' => 'Swedish',
'th_TH' => 'Thai',
'tr_TR' => 'Turkish',
'zh_CN' => 'Chinese (China)',
'zh_HK' => 'Chinese (Hong Kong)',
'zh_TW' => 'Chinese (Taiwan)'
		);
	?>
<div class="wrap">
<h2>Music Seller - Settings</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'music_seller-settings-group' ); ?>
    <?php do_settings_sections( 'music_seller-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">PayPal account</th>
        <td><input type="text" name="music_seller_paypal_account" value="<?php echo get_option('music_seller_paypal_account'); ?>" placeholder="Your@PayPal" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">PayPal currency</th>
        <td>
        <select name="music_seller_paypal_currency">
        <?php 
        //echo get_option('music_seller_paypal_account');
        foreach ($ppcurencies as $currency => $name) {
			$selected = '';
			if ($currency == get_option('music_seller_paypal_currency',$op->paypal_currency)) {
				$selected = ' selected';
			}
			echo "<option value=\"$currency\"$selected>$name</option>";
		}
        ?>
		</select> 
        </td>
        </tr>
        
        <tr valign="top">
        <th scope="row">PayPal language</th>
        <td>
        <select name="music_seller_paypal_language">
        <?php 
        //echo get_option('music_seller_paypal_account');
        foreach ($lc as $lang => $name) {
			$selected = '';
			if ($lang == get_option('music_seller_paypal_language')) {
				$selected = ' selected';
			}
			echo "<option value=\"$lang\"$selected>$name</option>";
		}
        ?>
		</select> 
        </td>
        </tr>
        
        <tr valign="top">
        <th scope="row">PayPal Return to site button text</th>
        <td><input type="text" name="music_seller_paypal_return_button_text" value="<?php echo get_option('music_seller_paypal_return_button_text',$op->paypal_return_button_text); ?>" placeholder="Click here to go to download page" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">PayPal sandbox <span class="description">(test mode)</span></span></th>
        <td><input type="checkbox" name="music_seller_paypal_sandbox" value="1" <?php echo (get_option('music_seller_paypal_sandbox') != 0 ? 'checked="checked"' : ''); ?> /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">PayPal Transaction Verification <span class="description">(turn off if experiencing problems)</span></span></th>
        <td><input type="checkbox" name="music_seller_paypal_verify_transactions" value="1" <?php echo (get_option('music_seller_paypal_verify_transactions',$op->paypal_verify_transactions) != 0 ? 'checked="checked"' : ''); ?> /></td>
        </tr>
                
        <tr valign="top">
        <th scope="row">Link Expiration</th>
        <td><input type="text" name="music_seller_link_expiration" value="<?php echo get_option('music_seller_link_expiration',$op->link_expiration); ?>" placeholder="1 month" /></td>
        </tr>
                
        <tr valign="top">
        <th scope="row">Title format <span class="description">Supported tags are: publisher, track_number, album, year, band, title, genre, composer, artist.</span></span></th>
        <td><input type="text" name="music_seller_file_title" value="<?php echo get_option('music_seller_file_title',$op->music_seller_file_title); ?>" placeholder="%%artist%% - %%title%%" /></td>
        </tr>
                
        <tr valign="top">
        <th scope="row">Downloads limit <span class="description">(after how many succesful downloads link becomes inactive)</span></span></th>
        <td><input type="text" name="music_seller_downloads_limit" value="<?php echo get_option('music_seller_downloads_limit',$op->downloads_limit); ?>" placeholder="3" /></td>
        </tr>
        
        <tr valign="top" class="<?php echo $music_seller_premiumFeature; ?> music_seller_upgrade_warning">
        <th scope="row"><h3>Premium Features</h3></th>
        <td><a href="https://www.shopfiles.com/index.php/products/music-seller-plugin-for-wordpress" target="_blank">Click here to upgrade</a></td>
        </tr>
      
        <tr valign="top" class="<?php echo $music_seller_premiumFeature; ?>">
        <th scope="row">Button Gradient Color 1 <span class="description">Starting color of the gradient.</span></span></th>
        <td><input type="text" class="my-color-field" name="music_seller_button_color_1" value="<?php echo get_option('music_seller_button_color_1',$op->button_color_1); ?>" placeholder="3" /></td>
        </tr>

        <tr valign="top" class="<?php echo $music_seller_premiumFeature; ?>">
        <th scope="row">Button Gradient Color 2 <span class="description">Ending color of the gradient.</span></span></th>
        <td><input type="text" class="my-color-field" name="music_seller_button_color_2" value="<?php echo get_option('music_seller_button_color_2',$op->button_color_2); ?>" placeholder="3" /></td>
        </tr>

        <tr valign="top" class="<?php echo $music_seller_premiumFeature; ?>">
        <th scope="row">Button Border Color <span class="description">Border of the button adding for adding the item to cart.</span></span></th>
        <td><input type="text" class="my-color-field" name="music_seller_border_color" value="<?php echo get_option('music_seller_border_color',$op->border_color); ?>" placeholder="3" /></td>
        </tr>


        <tr valign="top" class="<?php echo $music_seller_premiumFeature; ?>">
        <th scope="row">Button Gradient Color 1 (on Mouse Over)<span class="description">Starting color of the gradient.</span></span></th>
        <td><input type="text" class="my-color-field" name="music_seller_button_color_1_hover" value="<?php echo get_option('music_seller_button_color_1_hover',$op->button_color_1_hover); ?>" placeholder="3" /></td>
        </tr>

        <tr valign="top" class="<?php echo $music_seller_premiumFeature; ?>">
        <th scope="row">Button Gradient Color 2 (on Mouse Over)<span class="description">Ending color of the gradient.</span></span></th>
        <td><input type="text" class="my-color-field" name="music_seller_button_color_2_hover" value="<?php echo get_option('music_seller_button_color_2_hover',$op->button_color_2_hover); ?>" placeholder="3" /></td>
        </tr>

        <tr valign="top" class="<?php echo $music_seller_premiumFeature; ?>">
        <th scope="row">Button Border Color  (on Mouse Over)<span class="description">Border of the button adding for adding the item to cart.</span></span></th>
        <td><input type="text" class="my-color-field" name="music_seller_border_color_hover" value="<?php echo get_option('music_seller_border_color_hover',$op->border_color_hover); ?>" placeholder="3" /></td>
        </tr>



        <tr valign="top" class="<?php echo $music_seller_premiumFeature; ?>">
        <th scope="row">Show Preview Player</th>
        <td><input type="checkbox" name="music_seller_show_preview"  value="1" <?php echo (get_option('music_seller_show_preview',$op->music_seller_show_preview) != 0 ? 'checked="checked"' : ''); ?> /></td>
        </tr>
  
        <tr valign="top">
        <th scope="row">Email Delivery</th>
        <td><input type="checkbox" name="music_seller_email_delivery"  value="1" <?php echo (get_option('music_seller_email_delivery') != 0 ? 'checked="checked"' : ''); ?> /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Attach Files</th>
        <td><input type="checkbox" name="music_seller_attach_files"  value="1" <?php echo (get_option('music_seller_attach_files') != 0 ? 'checked="checked"' : ''); ?> /></td>
        </tr>
        
        
<!--         <tr valign="top">
        <th scope="row">VAT Percent</th>
        <td><input type="text" name="music_seller_vat_percent" value="<?php echo get_option('music_seller_vat_percent'); ?>" placeholder="20" /></td>
        </tr> -->
        
        <tr valign="top">
        <th scope="row">Thank you page</th>
        <td><?php 
        $editor_id = 'music_seller_thankyou_page';
        wp_editor( get_option('music_seller_thankyou_page',$op->thankyou_page), $editor_id );
        ?></td>
        </tr>
        <tr valign="top">
        <th scope="row">Email delivery content</th>
        <td>Subject:<br /><input type="text" name="music_seller_email_delivery_subject" value="<?php echo get_option('music_seller_email_delivery_subject',$op->email_delivery_subject); ?>" /><?php 
        $editor_id = 'music_seller_email_delivery_text';
        wp_editor( get_option('music_seller_email_delivery_text',$op->email_delivery_text), $editor_id );
        ?></td>
        </tr>
        
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php } ?>