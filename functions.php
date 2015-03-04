<?php
global $music_seller_versionClass;
global $music_seller_premiumFeature;

if (ini_set('upload_max_filesize', '1024M')) {
	ini_set('post_max_size', '1024M');
}

if (function_exists('hex2bin') == false) {
	function hex2bin($hex_string) {
		$pos = 0;
		$result = '';
		while ($pos < strlen($hex_string)) {
			if (strpos(HEX2BIN_WS, $hex_string{$pos}) !== FALSE) {
				$pos++;
			} else {
				$code = hexdec(substr($hex_string, $pos, 2));
				$pos = $pos + 2;
				$result .= chr($code);
			}
		}
		return $result;
	}
}
define('MUSIC_SELLER_VERSION',hex2bin('46756c6c'));

class MUSIC_SELLERWPOptions {

	var $paypal_currency = 'USD';

	var $paypal_return_button_text = 'Click here to go to download page';

	var $paypal_verify_transactions = 0;

	var $link_expiration = '1 month';

	var $email_delivery = 1;

	var $downloads_limit = 3;
	
	var $thankyou_page = '<h1>Thank you for your order %%first_name%% %%last_name%%!</h1>
You have successfully completed the order process! Please use the link(s) below to download your copy:

<strong>Use the link below the start the download:</strong>
<a href="%%downloadlink%%" target="_blank">%%item_name%%</a> (%%filesize%%)
<h3>Details for your order:</h3>
Order #: %%ebook%%

Transaction: %%txn_id%%

Amount: %%mc_currency%% %%mc_gross%%

<strong>Your password is: %%payer_email%%</strong>

Thank you!';

	var $email_delivery_text = 'Thank you for your order %%first_name%% %%last_name%%!

You have successfully completed the order process! Please use the link(s) below to download your copy:

%%downloadlink%%

Details for your order:

Order #: %%order_id%%
Transaction: %%txn_id%%
Amount: %%mc_currency%% %%mc_gross%%

Your password is: %%password%%

Thank you!';

}

function hide_that_stuff() {
	if (1) {
		echo '<style type="text/css">
   			#menu-posts-music_seller_order .wp-submenu-wrap li:nth-child(3) {display:none;}
    		  </style>';
	}
	if('music_seller_order' == get_post_type())
		echo '<style type="text/css">
   			#menu-posts-music_seller_order .wp-submenu-wrap li:nth-child(3), .add-new-h2 {display:none;}
    		  </style>';
}

function music_seller_order_custom_columns($column)
{
	global $post;
	$meta = get_post_meta($post->ID);

	$c = new music_seller_Currencies();
	//$column = "music_seller_$column";
	switch ($column) {
		case "music_seller_title":
			echo get_post_meta($post->ID,'first_name',true) . ' ' . get_post_meta($post->ID,'last_name',true);
			break;
		case "music_seller_product":
			$meta = get_post_meta($post->ID);
			
			echo music_seller_download_link($meta);
			
			break;
		case "music_seller_amount":
			$mc_currency = get_post_meta($post->ID,'mc_currency',true);
			$mc_gross = get_post_meta($post->ID,'mc_gross',true);
			echo $c->getSymbol($mc_currency) . toMoney(floatval($mc_gross),null,2);
			break;
		case "music_seller_country":
			echo get_post_meta($post->ID,'residence_country',true);
			break;
		case "music_seller_paypal":
			echo get_post_meta($post->ID,'payer_email',true);
			break;
		case "music_seller_date":
			echo get_the_date() . ' ' . get_the_time();
			break;
		case "music_seller_downloads":
			echo (is_array(get_post_meta($post->ID,'downloads',true)) ? "Yes" : "No");
			break;
	}
}

function toMoney($val,$symbol='$',$r=2)
{


    $n = $val; 
    $c = is_float($n) ? 1 : number_format($n,$r);
    $d = '.';
    $t = ',';
    $sign = ($n < 0) ? '-' : '';
    $i = $n=number_format(abs($n),$r); 
    $j = (($j = $i.length) > 3) ? $j % 3 : 0; 

   return  $symbol.$sign .($j ? substr($i,0, $j) + $t : '').preg_replace('/(\d{3})(?=\d)/',"$1" + $t,substr($i,$j)) ;

}

function music_seller_order_columns($columns)
{
	$columns = array(
			'cb'	 	=> '<input type="checkbox" />',
	//		'thumbnail'	=>	'Thumbnail',
			'music_seller_title' 	=> 'Buyer',
			'music_seller_paypal'	=> 'PayPal',
			//		'featured' 	=> 'Featured',
			'music_seller_product'	=>	'Product(s) Download Links',
			'music_seller_amount'	=>	'Amount',
			'music_seller_country'	=> 	'Country',
			'music_seller_date'		=>	'Date',
			'music_seller_downloads'		=>	'Successfully Downloaded',
	);
	return $columns;
}
function music_seller_add_meta_box() {

	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'music_seller_sectionid',
			__( 'Music Seller', 'music_seller_textdomain' ),
			'music_seller_meta_box_callback',
			$screen
		);
	}
}
/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function music_seller_meta_box_callback( $post ) {

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'music_seller_meta_box', 'music_seller_meta_box_nonce' );
	wp_enqueue_script( 'music_seller_js', plugins_url( '/js/music_seller.js' , __FILE__ ), array(), '1.0.0', true );
	wp_enqueue_style('music_seller_style', plugins_url( '/css/music_seller.css' , __FILE__ ));

	/*
	 * Use get_post_meta() to retrieve an existing value
	 * from the database and use the value for the form.
	 */
	$value = get_post_meta( $post->ID, '_my_meta_value_key', true );
	if (get_the_title() == '') {
		echo '<h4>After saving the new post you will be able to upload music for sale.</h4>';
		return true;
	}
	echo '<label for="music_seller_code">';
	_e( 'Use this code to display the player and music shopping cart', 'music_seller_textdomain' );
	echo '</label> ';
	echo '<input type="text" id="music_seller_code" name="music_seller_code" value="[music_seller]" readonly size="25" />';
	echo '<h4>Upload files for sale</h4>';
	echo '<button id="music_seller_add_files">Add more files</button>';
	echo '<div id="music_seller_files"><input name="music_seller_file[]" type="file" /><br /></div>';
	
	$uploads = get_post_meta(get_the_ID(),'music_seller_file',true);
	$prices = get_post_meta(get_the_ID(),'music_seller_price',true);
	$titles = get_post_meta(get_the_ID(),'music_seller_title',true);
	//echo var_dump($uploads);
	if (count($uploads) > 0 && is_array($uploads)) {
		echo '<h4>Already uploaded files</h4>';
		$previews = get_post_meta(get_the_ID(),'music_seller_preview',true);
		foreach ($uploads as $id => $row) {
			$filename = basename($row['file']);
			?>
			<div class="music_seller_row"><i class="music_seller_icon"></i><span><b>File for sale: </b><?php echo $filename; ?><br />
				<?php
				if (MUSIC_SELLER_VERSION == 'Full') {
					//var_dump($previews);
					//error_reporting(E_ALL);
					//ini_set('display_errors',true);
					if (isset($previews[$id]['url'])) {
						echo '<b>Preview file: </b> <a class="music_seller_preview_uploaded" href="' . $previews[$id]['url'] . '">' . basename($previews[$id]['file']) . '</a><br />Reupload preview: ';
					} else {
						echo '<span class="music_seller_no_preview">Upload a preview:</span>';
					}
			?>
				<input type="file" name="music_seller_preview[<?php echo $id; ?>]" /></span>
<?php
}
?>
				<div class="music_seller_price">Title: <input size="20" name="music_seller_title[<?php echo $id; ?>]" placeholder="" value="<?php echo $titles[$id]; ?>" /> Price: <input size="6" name="music_seller_price[<?php echo $id; ?>]" placeholder="0.00" value="<?php echo (@$prices[$id] > 0 ? $prices[$id] : '0.00'); ?>" /> <label><input type="checkbox" class="music_seller_delete" title="Delete" name="music_seller_delete[]" value="<?php echo $id; ?>" /> <span class="music_seller_delete_icon" title="Delete this file"></span></label>
				</div>
			</div>
			<?php
		}
	}
	echo '<p>Your server has the following settings regarding file upload size: post_max_size: <strong>' . ini_get('post_max_size') . '</strong>, upload_max_filesize: <strong>' . ini_get('upload_max_filesize') . '</strong></p>';
}

function music_seller_save_custom_meta_data($id) {
	add_filter( 'upload_dir', 'music_store_set_upload_dir' );

	add_post_meta($id,'music_seller_price',$_REQUEST['music_seller_price']);
	update_post_meta($id,'music_seller_price',$_REQUEST['music_seller_price']);
	add_post_meta($id,'music_seller_title',$_REQUEST['music_seller_title']);
	update_post_meta($id,'music_seller_title',$_REQUEST['music_seller_title']);

	if ($_POST['music_seller_delete']) {
		foreach ($_POST['music_seller_delete'] as $delete) {
			$uploads = get_post_meta($id,'music_seller_file',true);
			$toDelete = $uploads[$delete];
			//wp_die(print_r($toDelete,true));
			unlink($toDelete['file']);
			unset($uploads[$delete]);
			update_post_meta($id,'music_seller_file',$uploads);
		}
	}
	if (count($_FILES) < 1) {
		return true;
	}
	$uploads = array();
	
	if ($_FILES['music_seller_file']['name']) {
		foreach ($_FILES['music_seller_file']['name'] as $fkey => $file) {
			if(!empty($file)) {
				 
				// Setup the array of supported file types. In this case, it's just PDF.
				$supported_types = array(
					'audio/mpeg',
					'audio/mp4',
					'audio/ogg',
					'audio/vorbis',
					'audio/vnd.wav',
					'audio/mp3',
				);
				
				// Get the file type of the upload
				$arr_file_type = wp_check_filetype(basename($_FILES['music_seller_file']['name'][$fkey]));
				$uploaded_type = $arr_file_type['type'];
	
				// Check if the type is supported. If not, throw an error.
				if(in_array($uploaded_type, $supported_types)) {
	
					// Use the WordPress API to upload the file
					$upload = wp_upload_bits($_FILES['music_seller_file']['name'][$fkey], null, file_get_contents($_FILES['music_seller_file']['tmp_name'][$fkey]));
	
					$uploads[md5(microtime() . rand(0,1000))] = $upload;
					if(isset($upload['error']) && $upload['error'] != 0) {
						wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
					} else {
						$uploadsMeta = get_post_meta($id,'music_seller_file',true);
						if (is_array($uploadsMeta)) {
							$merged = array_merge($uploads, $uploadsMeta);
						} else {
							$merged = $uploads;
						}
						//wp_die(print_r($uploads,true) . '<br /><br />' . print_r(get_post_meta($id,'music_seller_file',true),true) . print_r($merged,true));
						add_post_meta($id, 'music_seller_file', $merged);
						update_post_meta($id, 'music_seller_file', $merged);
					} // end if/else
	
				} else {
					wp_die("Uploaded file is not in audio format." . print_r($_FILES['music_seller_file']));
				} // end if/else
	
			} 
		}
	}
	//Handle file previews uploads now
	add_filter( 'upload_dir', 'music_store_set_upload_dir_previews' );
	$uploads = array();
	if (!$_FILES['music_seller_preview']['name']) {
		return true;
	}
	foreach ($_FILES['music_seller_preview']['name'] as $fkey => $file) {
		if(!empty($file)) {
			 
			// Setup the array of supported file types. In this case, it's just PDF.
			$supported_types = array(
				'audio/mpeg',
				'audio/mp4',
				'audio/ogg',
				'audio/vorbis',
				'audio/vnd.wav',
				'audio/mp3',
			);
			
			// Get the file type of the upload
			$arr_file_type = wp_check_filetype(basename($_FILES['music_seller_preview']['name'][$fkey]));
			$uploaded_type = $arr_file_type['type'];

			// Check if the type is supported. If not, throw an error.
			if(in_array($uploaded_type, $supported_types)) {

				// Use the WordPress API to upload the file
				if ($_FILES['music_seller_preview']['error'][$fkey] == 1) {
					wp_die('Uploaded file is larger than PHP upload limit, please fix it via php.ini or contact the system admin. Limit is: upload_max_filesize - ' . ini_get('upload_max_filesize') . ', suggested fix for php.ini file is:<br /><br />upload_max_filesize = 1024M<br />post_max_size = 1024M');
				}
				$upload = wp_upload_bits($_FILES['music_seller_preview']['name'][$fkey], null, file_get_contents($_FILES['music_seller_preview']['tmp_name'][$fkey]));
				$uploads[$fkey] = $upload;
				if(isset($upload['error']) && $upload['error'] != 0) {
					wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
				} else {
					$uploadsMeta = get_post_meta($id,'music_seller_preview',true);
					if (is_array($uploadsMeta)) {
						$merged = array_merge($uploadsMeta, $uploads);
					} else {
						$merged = $uploads;
					}
					//wp_die(print_r($uploads,true) . '<br /><br />' . print_r(get_post_meta($id,'music_seller_file',true),true) . print_r($merged,true));
					add_post_meta($id, 'music_seller_preview', $merged);
					update_post_meta($id, 'music_seller_preview', $merged);

				} // end if/else

			} else {
				wp_die("Uploaded file is not in audio format.");
			} // end if/else

		} 
	}

}


function music_store_set_upload_dir( $upload ) {
	// Override the year / month being based on the post publication date, if year/month organization is enabled
	if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
		// Generate the yearly and monthly dirs
		$time = current_time( 'mysql' );
		$y = substr( $time, 0, 4 );
		$m = substr( $time, 5, 2 );
		$upload['subdir'] = "/$y/$m";
	}

	$upload['subdir'] = '/music_seller' . $upload['subdir'];
	$upload['path']   = $upload['basedir'] . $upload['subdir'];
	$upload['url']    = $upload['baseurl'] . $upload['subdir'];
	$htaccess = "Options -Indexes
deny from all
";
	@file_put_contents($upload['basedir'] . '/music_seller/.htaccess', $htaccess);
	return $upload;
}
function music_store_set_upload_dir_previews( $upload ) {
	// Override the year / month being based on the post publication date, if year/month organization is enabled
	if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
		// Generate the yearly and monthly dirs
		$time = current_time( 'mysql' );
		$y = substr( $time, 0, 4 );
		$m = substr( $time, 5, 2 );
		$upload['subdir'] = "/$y/$m";
	}

	$upload['subdir'] = '/music_seller_previews' . $upload['subdir'];
	$upload['path']   = $upload['basedir'] . $upload['subdir'];
	$upload['url']    = $upload['baseurl'] . $upload['subdir'];
	
	return $upload;
}
function music_seller_update_edit_form() {
    echo ' enctype="multipart/form-data"';
} // end update_edit_form

add_shortcode( 'music_seller', 'music_seller' );
add_shortcode('music_seller_thank_you','music_seller');
function music_seller_get_order($key = 'key',$val) {
	$loop = new WP_Query( array ( 'post_type' => 'music_seller_order', 'meta_key' => $key, 'meta_value' => $val ) );
	while ( $loop->have_posts() ) : $loop->the_post();
	$meta = get_post_meta(get_the_ID(),null,true);
	$out = array();
	foreach ($meta as $k => $arr) {
		$out[$k] = $arr[0];
	}
	$out['order_id'] = get_the_ID();
	return $out;
	endwhile;
}

function music_seller( $atts ){
	global $music_seller_versionClass;
	if (get_option('music_seller_show_preview') != 1) {
		$playerVisible = 'music_seller_invisible';
	}
	wp_enqueue_script( 'ppcart', plugins_url( '/js/jPayPalCart.js' , __FILE__ ), array(), '1.0.0', true );
	wp_enqueue_script( 'jplayer', plugins_url( '/js/player/jquery.jplayer.js' , __FILE__ ), array(), '1.0.0', true );
	wp_enqueue_script( 'jplayerplaylist', plugins_url( '/js/player/add-on/jplayer.playlist.js' , __FILE__ ), array(), '1.0.0', true );
	wp_enqueue_style('ppcartstyle', plugins_url( '/css/jPayPalCart.css' , __FILE__ ));
	wp_enqueue_style('jplayerstyle', plugins_url( '/js/player/skin/blue.monday/jplayer.blue.monday.css' , __FILE__ ));
	wp_enqueue_script( 'music_seller_js', plugins_url( '/js/music_seller.js' , __FILE__ ), array(), '1.0.0', true );
	wp_enqueue_style('music_seller_style', plugins_url( '/css/music_seller.css' , __FILE__ ));

	if ($_REQUEST['task'] == 'thankyou') {
		$content = get_option('music_seller_thankyou_page',true);
		$music_seller_order = music_seller_get_order('key', $_REQUEST['key']);
		if (!$music_seller_order) {
			return 'Sorry, order not found, please wait the screen will refresh in 5 seconds!<script>   window.setTimeout(\'location.reload()\', 5000);
</script>';
		}

		$music_seller_order['downloadlink'] = music_seller_download_link($music_seller_order);
		foreach ($music_seller_order as $k => $arr) {
			$content = str_replace('%%' . $k . '%%', $arr, $content);
		}
		return apply_filters('the_content',$content);
	}
	
	$mp3files = '';
	$prices = get_post_meta(get_the_ID(),'music_seller_price',true);
	$c = new music_seller_Currencies;
	$currency = get_option('music_seller_paypal_currency');
	$symbol = $c->currencies[$currency]['ASCII'];
	foreach (get_post_meta(get_the_ID(),'music_seller_file',true) as $key => $value) {
		$title = basename($value['file']);
		$title = ucwords($title);
		if (!class_exists('getID3')) {
			require_once('includes/getid3/getid3.php');
		}
		if (get_option('music_seller_use_id3')) {
			$getID3 = new getID3;
			$filePath = str_replace('C:xampphtdocswp', 'c:\xampp\htdocs\wp', $value['file']);
			$ThisFileInfo = $getID3->analyze($filePath);
			$title = music_seller_title($ThisFileInfo,$filePath);
		} else {
			$title = get_post_meta(get_the_ID(),'music_seller_title',true);
			$title = $title[$key];
		}

		$previews = get_post_meta(get_the_ID(),'music_seller_preview',true);
		
		if (floatval($prices[$key]) == 0) {
			$downloadkey = md5($key . NONCE_KEY);
			$previews[$key]['url'] = add_query_arg( array('music_seller_free_download' => $key, 'download_key' => $downloadkey,  'postid' => get_the_ID()));
			$previews[$key]['url'] = remove_query_arg('p',$previews[$key]['url']);
		}
				
		//add image later image:\"data:image/jpg;base64," . base64_encode($ThisFileInfo['comments']['picture'][0]['data']) . "\",
		$mp3files .= "{
				title:\"" . $title . "\",
				" . ($prices[$key] > 0 ? '' : 'free:true,') . "
				mp3:\"" . $previews[$key]['url'] . "\",
				price: " . floatval($prices[$key]) . ",
				key: \"" . $key . "\",
				currency: \"" . $currency . "\",
				symbol: \"" . $symbol . "\",
			},";
	}
	$key = md5(microtime(true));
	if (MUSIC_SELLER_VERSION == 'Full') {
		$MUSIC_SELLER_ADD_ALL_TO_CART = '<input type="submit" class="music_seller_add_all_to_cart" onClick="music_seller_add_all_to_cart(event,this)" value="Add All To Cart" />';
	}
	$out = "
<style type=\"text/css\">
.music_seller_add_to_cart {
    background-image: -webkit-linear-gradient(" . get_option('music_seller_button_color_1','#52A8E8') . ", " . get_option('music_seller_button_color_2','#377AD0') . ");
    background-image: -moz-linear-gradient(0% 100% 90deg," . get_option('music_seller_button_color_2','#377AD0') . ", " . get_option('music_seller_button_color_1','#52A8E8') . ");
    background-color: " . get_option('music_seller_button_color_1','#52A8E8') . ";
    border: 1px solid " . get_option('music_seller_border_color','#20559A') . ";
}
.music_seller_add_to_cart:hover, .apl:focus {
    background-image: -webkit-linear-gradient(" . get_option('music_seller_button_color_1_hover','#54A1D8') . ", " . get_option('music_seller_button_color_2_hover','#1967CA') . ");
    background-image: -moz-linear-gradient(0% 100% 90deg," . get_option('music_seller_button_color_2_hover','#1967CA') . ", " . get_option('music_seller_button_color_1_hover','#54A1D8') . ");
    background-color: " . get_option('music_seller_button_color_1_hover','#52A8E8') . ";
    border: 1px solid " . get_option('music_seller_border_color_hover','#20559A') . ";
}
</style>
<script type=\"text/javascript\">
jQuery(document).ready(function(){
	new jPlayerPlaylist({
		jPlayer: \"#jquery_jplayer_" . $key . "\",
		cssSelectorAncestor: \"#jp_container_" . $key . "\"
	}, [
		$mp3files
	], {
		swfPath: \"" . plugins_url( '/js/player' , __FILE__ ) . "\",
		supplied: \"mp3\",
		wmode: \"window\",
		solution: \"html, flash\",
		smoothPlayBar: true,
		keyEnabled: true,
		ready: function () {
          jQuery('.music_seller_add_to_cart').click(function (e) {
          	el = jQuery(e.target);
			if (el.attr('data-key') == undefined) {
				el = jQuery(e.target).parent();
			}
          	var code = el.attr('data-key');
          	var description = el.attr('data-title');
          	var quantity = 1;
          	var value = el.attr('data-price');
          	var vat = 0;
          	parentDiv = jQuery(el).parent().parent().parent().parent().parent().parent().parent().get();
			theDiv = jQuery('.music_seller_cart_div',parentDiv);
          	if (jQuery(this).hasClass('music_seller_in_cart') == false) {
	          	theDiv.PayPalCart('add', code, description, quantity, value, vat);
	          	jQuery('.music_seller_cart_icon',this).animate({ 'width': '16px' }, 120 );
	          	jQuery(this).addClass('music_seller_in_cart');
				jQuery('.music_seller_buy_now',theDiv).val('Buy Now for ' + jQuery('.PayPalCartTotals td:nth-child(2)',theDiv).html());	          	
          	} else {
	          	theDiv.PayPalCart('remove', code);
	          	jQuery('.music_seller_cart_icon',this).animate({ 'width': '0px' }, 120 );
	          	jQuery(this).removeClass('music_seller_in_cart');
				jQuery('.music_seller_buy_now',theDiv).val('Buy Now for ' + jQuery('.PayPalCartTotals td:nth-child(2)',theDiv).html());	          	
				if (jQuery('.music_seller_in_cart',jQuery(this).parent().parent().parent().parent().parent().parent().parent()).length == 0) {
					jQuery('.music_seller_buy_now',theDiv).val('Buy Now');	          	
				}
          	}
          });
			//TODO: If $0.00 make text just Buy Now
		  	theDiv = jQuery('#myCart" . get_the_ID() . "').get()[0];
         	music_seller_update_icons(theDiv);
        },
	});
    // Create a basic cart
    jQuery(\"#myCart" . get_the_ID() . "\").PayPalCart({ business: '" . get_option('music_seller_paypal_account') . "',
            notifyURL: \"" . add_query_arg(array('task' => 'music_seller_ipn', 'postid' => get_the_ID(), 'key' => $key), home_url('/')) . "\",
            virtual: true,             //set to true where you are selling virtual items such as downloads
            quantityupdate: false,       //set to false if you want to disable quantity updates in the cart 
            currency: '" . $currency . "',            //set to your trading currency - see PayPal for valid options
			returnURL: '" . add_query_arg(array('task' => 'thankyou', 'key' => $key),get_permalink(get_option('music_seller_checkout_page'))) . "',
			cancelURL: '" . add_query_arg(array('task' => 'cancel', 'key' => $key),get_permalink(get_option('music_seller_cancel_page'))) . "',
            currencysign: '" . $symbol . "',          //set the currency symbol
			cbt: '" . get_option('music_seller_paypal_return_button_text') . "',          //set the currency symbol
            minicartid: 'minicart', " . (get_option('music_seller_paypal_sandbox') > 0 ? 'sandbox: true,' : '') . "
			persitdays: -1               //set to -1 for cookie-less cart for single page of products, 
                                        // 0 (default) persits for the session, 
                                        // x (number of days) the basket will persits between visits
    });
});
	var MUSIC_SELLER_ADD_ALL_TO_CART = '" . $MUSIC_SELLER_ADD_ALL_TO_CART . "';
    function music_seller_add_all_to_cart(event, obj) {
    			e = event;
    			e.preventDefault();
				pluginDiv = jQuery(obj).parent().parent().parent().get();
				jQuery('.music_seller_add_to_cart:not(.music_seller_in_cart)',pluginDiv).trigger('click');
	}
</script>
		<div class=\"music_seller_player_and_cart\">
		<div id=\"jquery_jplayer_" . $key . "\" class=\"jp-jplayer\"></div>
		<div id=\"jp_container_" . $key . "\" class=\"jp-audio\">
			<div class=\"jp-type-playlist\">
				<div class=\"jp-gui jp-interface " . $playerVisible . "\">
					<ul class=\"jp-controls\">
						<li><a href=\"javascript:;\" class=\"jp-previous\" tabindex=\"1\">previous</a></li>
						<li><a href=\"javascript:;\" class=\"jp-play\" tabindex=\"1\">play</a></li>
						<li><a href=\"javascript:;\" class=\"jp-pause\" tabindex=\"1\">pause</a></li>
						<li><a href=\"javascript:;\" class=\"jp-next\" tabindex=\"1\">next</a></li>
						<li><a href=\"javascript:;\" class=\"jp-stop\" tabindex=\"1\">stop</a></li>
						<li><a href=\"javascript:;\" class=\"jp-mute\" tabindex=\"1\" title=\"mute\">mute</a></li>
						<li><a href=\"javascript:;\" class=\"jp-unmute\" tabindex=\"1\" title=\"unmute\">unmute</a></li>
						<li><a href=\"javascript:;\" class=\"jp-volume-max\" tabindex=\"1\" title=\"max volume\">max volume</a></li>
					</ul>
					<div class=\"jp-progress\">
						<div class=\"jp-seek-bar\">
							<div class=\"jp-play-bar\"></div>
						</div>
					</div>
					<div class=\"jp-volume-bar\">
						<div class=\"jp-volume-bar-value\"></div>
					</div>
					<div class=\"jp-time-holder\">
						<div class=\"jp-current-time\"></div>
						<div class=\"jp-duration\"></div>
					</div>
					<ul class=\"jp-toggles\">
						<li><a href=\"javascript:;\" class=\"jp-shuffle\" tabindex=\"1\" title=\"shuffle\">shuffle</a></li>
						<li><a href=\"javascript:;\" class=\"jp-shuffle-off\" tabindex=\"1\" title=\"shuffle off\">shuffle off</a></li>
						<li><a href=\"javascript:;\" class=\"jp-repeat\" tabindex=\"1\" title=\"repeat\">repeat</a></li>
						<li><a href=\"javascript:;\" class=\"jp-repeat-off\" tabindex=\"1\" title=\"repeat off\">repeat off</a></li>
					</ul>
				</div>
				<div class=\"jp-playlist\">
					<ul>
						<li></li>
					</ul>
				</div>
				<div class=\"jp-no-solution\">
					<span>Update Required</span>
					To play the media you will need to either update your browser to a recent version or update your <a href=\"http://get.adobe.com/flashplayer/\" target=\"_blank\">Flash plugin</a>.
				</div>
			</div>
		</div>
		<div class=\"music_seller_cart_div\" id=\"myCart" . get_the_ID() . "\"></div>
		</div>
	";
	$post_id = get_the_ID();
	return $out;
}

class music_seller_Currencies {

    public $currencies = array(

        "AUD" => array("name" => "Australian Dollar", "symbol" => "A$", "ASCII" => "A&#36;"),

        "CAD" => array("name" => "Canadian Dollar", "symbol" => "$", "ASCII" => "&#36;"),

        "CZK" => array("name" => "Czech Koruna", "symbol" => "Kč", "ASCII" => ""),

        "DKK" => array("name" => "Danish Krone", "symbol" => "Kr", "ASCII" => ""),

        "EUR" => array("name" => "Euro", "symbol" => "€", "ASCII" => "&#128;"),

        "HKD" => array("name" => "Hong Kong Dollar", "symbol" => "$", "ASCII" => "&#36;"),

        "HUF" => array("name" => "Hungarian Forint", "symbol" => "Ft", "ASCII" => ""),

        "ILS" => array("name" => "Israeli New Sheqel", "symbol" => "₪", "ASCII" => "&#8361;"),

        "JPY" => array("name" => "Japanese Yen", "symbol" => "¥", "ASCII" => "&#165;"),

        "MXN" => array("name" => "Mexican Peso", "symbol" => "$", "ASCII" => "&#36;"),

        "NOK" => array("name" => "Norwegian Krone", "symbol" => "Kr", "ASCII" => ""),

        "NZD" => array("name" => "New Zealand Dollar", "symbol" => "$", "ASCII" => "&#36;"),

        "PHP" => array("name" => "Philippine Peso", "symbol" => "₱", "ASCII" => ""),

        "PLN" => array("name" => "Polish Zloty", "symbol" => "zł", "ASCII" => ""),

        "GBP" => array("name" => "Pound Sterling", "symbol" => "£", "ASCII" => "&#163;"),

        "SGD" => array("name" => "Singapore Dollar", "symbol" => "$", "ASCII" => "&#36;"),

        "SEK" => array("name" => "Swedish Krona", "symbol" => "kr", "ASCII" => ""),

        "CHF" => array("name" => "Swiss Franc", "symbol" => "CHF", "ASCII" => ""),

        "TWD" => array("name" => "Taiwan New Dollar", "symbol" => "NT$", "ASCII" => "NT&#36;"),

        "THB" => array("name" => "Thai Baht", "symbol" => "฿", "ASCII" => "&#3647;"),

        "USD" => array("name" => "U.S. Dollar", "symbol" => "$", "ASCII" => "&#36;")

    );

    public function getSymbol($code = "USD") {

        if (!empty($this->currencies[$code]["ASCII"])) {

            return (string) $this->currencies[$code]["ASCII"];

        }

        return (string) $this->currencies[$code]["symbol"];

    }

}



function music_seller_orders_post_type() {
		$labels = array(
			'name'               => 'Orders',
			'singular_name'      => 'Order',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Order',
			'edit_item'          => 'Edit Order',
			'new_item'           => 'New Order',
			'all_items'          => 'Orders',
			'view_item'          => 'View Order Details',
			'search_items'       => 'Search Order',
			'not_found'          => 'No orders found',
			'not_found_in_trash' => 'No orders found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Music Seller Orders'
	);

	$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'order' ),
			'capability_type'    => 'post',
			//'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'supports'           => array('title','excerpt')
	);

	register_post_type( 'music_seller_order', $args );
}
function music_seller_download_link($order) {
	if (is_array($order['num_cart_items'])) {
		foreach ($order as $k => $v) {
			$order_out[$k] = $v[0];
		}
	$order_orig = $order;
	$order = $order_out;
	$order['order_id'] = $order_orig['order_id'];
	}
	for ($i=0; $i < $order['num_cart_items'];$i++) {
		$link = get_permalink($order['postid']);
		$k = $i+1;
		@$item_number[] = $order['item_number' . $k];
		@$item_name[] = $order['item_name' . $k];
		if (get_option('music_seller_attach_files') == 1) {
			@$attachments[] = $meta[$order['item_number' . $k]]['file'];
		}
		$link = add_query_arg(array('music_seller_download' => $order['item_number' . $k], 'txn_id' => $order['txn_id'], 'order_id' => $order['order_id']),$link);
		$link = '<a href="' . $link . '">Download ' . $order['item_name' . $k] . '</a>';
		$links[] = $link;
		
	}
	return implode("<br />\n", $links);
}

function music_seller_email_delivery($post_id) {
	global $music_seller_email_delivery;
	
	$debug = print_r($music_seller_email_delivery,true);
	foreach ($music_seller_email_delivery['order'] as $k => $v) {
		$music_seller_email_delivery['text'] = str_replace('%%' . $k . '%%', $v, $music_seller_email_delivery['text']);
	}
	$music_seller_email_delivery['text'] = apply_filters('music_seller_filter',$music_seller_email_delivery['text']);
	add_filter( 'wp_mail_content_type', 'music_seller_set_content_type' );
	if (get_option('music_seller_email_delivery') == 1) {
		if (!wp_mail($music_seller_email_delivery['to'],$music_seller_email_delivery['subject'], $music_seller_email_delivery['text'],null, $music_seller_email_delivery['attachments'])) {
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
			mail($music_seller_email_delivery['to'],$music_seller_email_delivery['subject'], $music_seller_email_delivery['text'],$headers);
		}
	}

}
function music_seller_set_content_type( $content_type ){
	return 'text/html';
}


function music_seller_ipn() {
	$debug = print_r($_REQUEST,true);
	if ($_REQUEST['task'] == 'music_seller_ipn') {
		global $music_seller_email_delivery;

		include_once 'payment_gateways/paypal/ipnlistener.php';
		$listener = new IpnListener();
		if (get_option('music_seller_paypal_sandbox') > 0) {
			$listener->use_sandbox = true;
		}
		////////

		////////
		try {
			$listener->requirePostMethod();
			$verified = $listener->processIpn();
		} catch (Exception $e) {
			die($e->getMessage());
			exit(0);
		}
		if ($verified) {
			$my_post = array(
			  'post_title'    => $_REQUEST['first_name'] . ' ' . $_REQUEST['last_name'],
			  'post_type'	  => 'music_seller_order',
			  'post_status'   => 'publish',
			  'post_author'   => 1,
			  'post_category' => array(8,39));
			//$custom = explode("|",$_REQUEST['custom']);
			$mc_gross = number_format($_REQUEST['mc_gross'], 2, '.', ',');
			$vat = get_option('music_sller_vat_percent'); 
			if ($vat > 0) {
				$mc_gross = $_REQUEST['mc_gross'] - $_REQUEST['tax'];
			}
			$mc_gross = number_format($mc_gross, 2, '.', ',');
			if (1) { //md5(NONCE_KEY . $custom[0] . $mc_gross) == $custom[1]
				$post_id = wp_insert_post( $my_post, $wp_error );
				
				foreach ($_REQUEST as $k => $v) {
					update_post_meta($post_id, $k, $v);
					@$order[$k] = $v;
				}
				$order['order_id'] = $post_id;
				$order['password'] = $_REQUEST['payer_email'];
				$order['downloadlink'] = music_seller_download_link($order);
				update_post_meta($post_id,'music_seller_order_key',$order['txn_id']);
				update_post_meta($post_id,'downloads',0);
				update_post_meta($post_id,'order_id',$post_id);
				$order['attachments'] = music_seller_attachments($order);
				
				error_log(print_r($order,true)); //TODO: Fix the above
				
				global $music_seller_email_delivery; //was $music_seller_attachment look where is it
				mail(get_option( 'admin_email' ), 'Music Seller for WordPress - Verified Order Received', $listener->getTextReport());
				$music_seller_email_delivery = array('to' => $_REQUEST['payer_email'], 'subject' => get_option('music_seller_email_delivery_subject'), 'text' => get_option('music_seller_email_delivery_text'),'attachments' => $order['attachments'], 'order' => $order);
				error_log('delivery being added to init');
				add_action( 'init', 'music_seller_email_delivery', 100 );
				error_log('delivery added to init');
				//array('to' => $_REQUEST['payer_email'], 'subject' => get_option('email_delivery_subject'), 'text' => 'teeext', 'file' => $attachment[0]['file'])
			} else {
				mail(get_option( 'admin_email' ), 'Music Seller for WordPress - Possible fraud attempt ', $listener->getTextReport());
			}

		} else {
			mail(get_option( 'admin_email' ), 'Music Seller for WordPress - Possible fraud attempt', $listener->getTextReport() . "\n\n\n" . md5(NONCE_KEY . $custom[0] . $mc_gross) == $custom[1]);
		}
		
	}
}

function music_seller_attachments($order) {
	$meta = get_post_meta($order['postid'],'music_seller_file',true);
	for ($i=0; $i < $order['num_cart_items'];$i++) {
		$k = $i+1;
		@$mc_gross += $order['mc_gross_' . $k];
		@$item_number[] = $order['item_number' . $k];
		@$item_name[] = $order['item_name' . $k];
		@$attachments[] = $meta[$order['item_number' . $k]]['file'];
	}
	return (array)$attachments;
}

function music_seller_get_file_ctype( $extension ) {
	switch( $extension ):
	case 'ac'       : $ctype = "application/pkix-attr-cert"; break;
	case 'adp'      : $ctype = "audio/adpcm"; break;
	case 'ai'       : $ctype = "application/postscript"; break;
	case 'aif'      : $ctype = "audio/x-aiff"; break;
	case 'aifc'     : $ctype = "audio/x-aiff"; break;
	case 'aiff'     : $ctype = "audio/x-aiff"; break;
	case 'air'      : $ctype = "application/vnd.adobe.air-application-installer-package+zip"; break;
	case 'apk'      : $ctype = "application/vnd.android.package-archive"; break;
	case 'asc'      : $ctype = "application/pgp-signature"; break;
	case 'atom'     : $ctype = "application/atom+xml"; break;
	case 'atomcat'  : $ctype = "application/atomcat+xml"; break;
	case 'atomsvc'  : $ctype = "application/atomsvc+xml"; break;
	case 'au'       : $ctype = "audio/basic"; break;
	case 'aw'       : $ctype = "application/applixware"; break;
	case 'avi'      : $ctype = "video/x-msvideo"; break;
	case 'bcpio'    : $ctype = "application/x-bcpio"; break;
	case 'bin'      : $ctype = "application/octet-stream"; break;
	case 'bmp'      : $ctype = "image/bmp"; break;
	case 'boz'      : $ctype = "application/x-bzip2"; break;
	case 'bpk'      : $ctype = "application/octet-stream"; break;
	case 'bz'       : $ctype = "application/x-bzip"; break;
	case 'bz2'      : $ctype = "application/x-bzip2"; break;
	case 'ccxml'    : $ctype = "application/ccxml+xml"; break;
	case 'cdmia'    : $ctype = "application/cdmi-capability"; break;
	case 'cdmic'    : $ctype = "application/cdmi-container"; break;
	case 'cdmid'    : $ctype = "application/cdmi-domain"; break;
	case 'cdmio'    : $ctype = "application/cdmi-object"; break;
	case 'cdmiq'    : $ctype = "application/cdmi-queue"; break;
	case 'cdf'      : $ctype = "application/x-netcdf"; break;
	case 'cer'      : $ctype = "application/pkix-cert"; break;
	case 'cgm'      : $ctype = "image/cgm"; break;
	case 'class'    : $ctype = "application/octet-stream"; break;
	case 'cpio'     : $ctype = "application/x-cpio"; break;
	case 'cpt'      : $ctype = "application/mac-compactpro"; break;
	case 'crl'      : $ctype = "application/pkix-crl"; break;
	case 'csh'      : $ctype = "application/x-csh"; break;
	case 'css'      : $ctype = "text/css"; break;
	case 'cu'       : $ctype = "application/cu-seeme"; break;
	case 'davmount' : $ctype = "application/davmount+xml"; break;
	case 'dbk'      : $ctype = "application/docbook+xml"; break;
	case 'dcr'      : $ctype = "application/x-director"; break;
	case 'deploy'   : $ctype = "application/octet-stream"; break;
	case 'dif'      : $ctype = "video/x-dv"; break;
	case 'dir'      : $ctype = "application/x-director"; break;
	case 'dist'     : $ctype = "application/octet-stream"; break;
	case 'distz'    : $ctype = "application/octet-stream"; break;
	case 'djv'      : $ctype = "image/vnd.djvu"; break;
	case 'djvu'     : $ctype = "image/vnd.djvu"; break;
	case 'dll'      : $ctype = "application/octet-stream"; break;
	case 'dmg'      : $ctype = "application/octet-stream"; break;
	case 'dms'      : $ctype = "application/octet-stream"; break;
	case 'doc'      : $ctype = "application/msword"; break;
	case 'docx'     : $ctype = "application/vnd.openxmlformats-officedocument.wordprocessingml.document"; break;
	case 'dotx'     : $ctype = "application/vnd.openxmlformats-officedocument.wordprocessingml.template"; break;
	case 'dssc'     : $ctype = "application/dssc+der"; break;
	case 'dtd'      : $ctype = "application/xml-dtd"; break;
	case 'dump'     : $ctype = "application/octet-stream"; break;
	case 'dv'       : $ctype = "video/x-dv"; break;
	case 'dvi'      : $ctype = "application/x-dvi"; break;
	case 'dxr'      : $ctype = "application/x-director"; break;
	case 'ecma'     : $ctype = "application/ecmascript"; break;
	case 'elc'      : $ctype = "application/octet-stream"; break;
	case 'emma'     : $ctype = "application/emma+xml"; break;
	case 'eps'      : $ctype = "application/postscript"; break;
	case 'epub'     : $ctype = "application/epub+zip"; break;
	case 'etx'      : $ctype = "text/x-setext"; break;
	case 'exe'      : $ctype = "application/octet-stream"; break;
	case 'exi'      : $ctype = "application/exi"; break;
	case 'ez'       : $ctype = "application/andrew-inset"; break;
	case 'f4v'      : $ctype = "video/x-f4v"; break;
	case 'fli'      : $ctype = "video/x-fli"; break;
	case 'flv'      : $ctype = "video/x-flv"; break;
	case 'gif'      : $ctype = "image/gif"; break;
	case 'gml'      : $ctype = "application/srgs"; break;
	case 'gpx'      : $ctype = "application/gml+xml"; break;
	case 'gram'     : $ctype = "application/gpx+xml"; break;
	case 'grxml'    : $ctype = "application/srgs+xml"; break;
	case 'gtar'     : $ctype = "application/x-gtar"; break;
	case 'gxf'      : $ctype = "application/gxf"; break;
	case 'hdf'      : $ctype = "application/x-hdf"; break;
	case 'hqx'      : $ctype = "application/mac-binhex40"; break;
	case 'htm'      : $ctype = "text/html"; break;
	case 'html'     : $ctype = "text/html"; break;
	case 'ice'      : $ctype = "x-conference/x-cooltalk"; break;
	case 'ico'      : $ctype = "image/x-icon"; break;
	case 'ics'      : $ctype = "text/calendar"; break;
	case 'ief'      : $ctype = "image/ief"; break;
	case 'ifb'      : $ctype = "text/calendar"; break;
	case 'iges'     : $ctype = "model/iges"; break;
	case 'igs'      : $ctype = "model/iges"; break;
	case 'ink'      : $ctype = "application/inkml+xml"; break;
	case 'inkml'    : $ctype = "application/inkml+xml"; break;
	case 'ipfix'    : $ctype = "application/ipfix"; break;
	case 'jar'      : $ctype = "application/java-archive"; break;
	case 'jnlp'     : $ctype = "application/x-java-jnlp-file"; break;
	case 'jp2'      : $ctype = "image/jp2"; break;
	case 'jpe'      : $ctype = "image/jpeg"; break;
	case 'jpeg'     : $ctype = "image/jpeg"; break;
	case 'jpg'      : $ctype = "image/jpeg"; break;
	case 'js'       : $ctype = "application/javascript"; break;
	case 'json'     : $ctype = "application/json"; break;
	case 'jsonml'   : $ctype = "application/jsonml+json"; break;
	case 'kar'      : $ctype = "audio/midi"; break;
	case 'latex'    : $ctype = "application/x-latex"; break;
	case 'lha'      : $ctype = "application/octet-stream"; break;
	case 'lrf'      : $ctype = "application/octet-stream"; break;
	case 'lzh'      : $ctype = "application/octet-stream"; break;
	case 'lostxml'  : $ctype = "application/lost+xml"; break;
	case 'm3u'      : $ctype = "audio/x-mpegurl"; break;
	case 'm4a'      : $ctype = "audio/mp4a-latm"; break;
	case 'm4b'      : $ctype = "audio/mp4a-latm"; break;
	case 'm4p'      : $ctype = "audio/mp4a-latm"; break;
	case 'm4u'      : $ctype = "video/vnd.mpegurl"; break;
	case 'm4v'      : $ctype = "video/x-m4v"; break;
	case 'm21'      : $ctype = "application/mp21"; break;
	case 'ma'       : $ctype = "application/mathematica"; break;
	case 'mac'      : $ctype = "image/x-macpaint"; break;
	case 'mads'     : $ctype = "application/mads+xml"; break;
	case 'man'      : $ctype = "application/x-troff-man"; break;
	case 'mar'      : $ctype = "application/octet-stream"; break;
	case 'mathml'   : $ctype = "application/mathml+xml"; break;
	case 'mbox'     : $ctype = "application/mbox"; break;
	case 'me'       : $ctype = "application/x-troff-me"; break;
	case 'mesh'     : $ctype = "model/mesh"; break;
	case 'metalink' : $ctype = "application/metalink+xml"; break;
	case 'meta4'    : $ctype = "application/metalink4+xml"; break;
	case 'mets'     : $ctype = "application/mets+xml"; break;
	case 'mid'      : $ctype = "audio/midi"; break;
	case 'midi'     : $ctype = "audio/midi"; break;
	case 'mif'      : $ctype = "application/vnd.mif"; break;
	case 'mods'     : $ctype = "application/mods+xml"; break;
	case 'mov'      : $ctype = "video/quicktime"; break;
	case 'movie'    : $ctype = "video/x-sgi-movie"; break;
	case 'm1v'      : $ctype = "video/mpeg"; break;
	case 'm2v'      : $ctype = "video/mpeg"; break;
	case 'mp2'      : $ctype = "audio/mpeg"; break;
	case 'mp2a'     : $ctype = "audio/mpeg"; break;
	case 'mp21'     : $ctype = "application/mp21"; break;
	case 'mp3'      : $ctype = "audio/mpeg"; break;
	case 'mp3a'     : $ctype = "audio/mpeg"; break;
	case 'mp4'      : $ctype = "video/mp4"; break;
	case 'mp4s'     : $ctype = "application/mp4"; break;
	case 'mpe'      : $ctype = "video/mpeg"; break;
	case 'mpeg'     : $ctype = "video/mpeg"; break;
	case 'mpg'      : $ctype = "video/mpeg"; break;
	case 'mpg4'     : $ctype = "video/mpeg"; break;
	case 'mpga'     : $ctype = "audio/mpeg"; break;
	case 'mrc'      : $ctype = "application/marc"; break;
	case 'mrcx'     : $ctype = "application/marcxml+xml"; break;
	case 'ms'       : $ctype = "application/x-troff-ms"; break;
	case 'mscml'    : $ctype = "application/mediaservercontrol+xml"; break;
	case 'msh'      : $ctype = "model/mesh"; break;
	case 'mxf'      : $ctype = "application/mxf"; break;
	case 'mxu'      : $ctype = "video/vnd.mpegurl"; break;
	case 'nc'       : $ctype = "application/x-netcdf"; break;
	case 'oda'      : $ctype = "application/oda"; break;
	case 'oga'      : $ctype = "application/ogg"; break;
	case 'ogg'      : $ctype = "application/ogg"; break;
	case 'ogx'      : $ctype = "application/ogg"; break;
	case 'omdoc'    : $ctype = "application/omdoc+xml"; break;
	case 'onetoc'   : $ctype = "application/onenote"; break;
	case 'onetoc2'  : $ctype = "application/onenote"; break;
	case 'onetmp'   : $ctype = "application/onenote"; break;
	case 'onepkg'   : $ctype = "application/onenote"; break;
	case 'opf'      : $ctype = "application/oebps-package+xml"; break;
	case 'oxps'     : $ctype = "application/oxps"; break;
	case 'p7c'      : $ctype = "application/pkcs7-mime"; break;
	case 'p7m'      : $ctype = "application/pkcs7-mime"; break;
	case 'p7s'      : $ctype = "application/pkcs7-signature"; break;
	case 'p8'       : $ctype = "application/pkcs8"; break;
	case 'p10'      : $ctype = "application/pkcs10"; break;
	case 'pbm'      : $ctype = "image/x-portable-bitmap"; break;
	case 'pct'      : $ctype = "image/pict"; break;
	case 'pdb'      : $ctype = "chemical/x-pdb"; break;
	case 'pdf'      : $ctype = "application/pdf"; break;
	case 'pki'      : $ctype = "application/pkixcmp"; break;
	case 'pkipath'  : $ctype = "application/pkix-pkipath"; break;
	case 'pfr'      : $ctype = "application/font-tdpfr"; break;
	case 'pgm'      : $ctype = "image/x-portable-graymap"; break;
	case 'pgn'      : $ctype = "application/x-chess-pgn"; break;
	case 'pgp'      : $ctype = "application/pgp-encrypted"; break;
	case 'pic'      : $ctype = "image/pict"; break;
	case 'pict'     : $ctype = "image/pict"; break;
	case 'pkg'      : $ctype = "application/octet-stream"; break;
	case 'png'      : $ctype = "image/png"; break;
	case 'pnm'      : $ctype = "image/x-portable-anymap"; break;
	case 'pnt'      : $ctype = "image/x-macpaint"; break;
	case 'pntg'     : $ctype = "image/x-macpaint"; break;
	case 'pot'      : $ctype = "application/vnd.ms-powerpoint"; break;
	case 'potx'     : $ctype = "application/vnd.openxmlformats-officedocument.presentationml.template"; break;
	case 'ppm'      : $ctype = "image/x-portable-pixmap"; break;
	case 'pps'      : $ctype = "application/vnd.ms-powerpoint"; break;
	case 'ppsx'     : $ctype = "application/vnd.openxmlformats-officedocument.presentationml.slideshow"; break;
	case 'ppt'      : $ctype = "application/vnd.ms-powerpoint"; break;
	case 'pptx'     : $ctype = "application/vnd.openxmlformats-officedocument.presentationml.presentation"; break;
	case 'prf'      : $ctype = "application/pics-rules"; break;
	case 'ps'       : $ctype = "application/postscript"; break;
	case 'psd'      : $ctype = "image/photoshop"; break;
	case 'qt'       : $ctype = "video/quicktime"; break;
	case 'qti'      : $ctype = "image/x-quicktime"; break;
	case 'qtif'     : $ctype = "image/x-quicktime"; break;
	case 'ra'       : $ctype = "audio/x-pn-realaudio"; break;
	case 'ram'      : $ctype = "audio/x-pn-realaudio"; break;
	case 'ras'      : $ctype = "image/x-cmu-raster"; break;
	case 'rdf'      : $ctype = "application/rdf+xml"; break;
	case 'rgb'      : $ctype = "image/x-rgb"; break;
	case 'rm'       : $ctype = "application/vnd.rn-realmedia"; break;
	case 'rmi'      : $ctype = "audio/midi"; break;
	case 'roff'     : $ctype = "application/x-troff"; break;
	case 'rss'      : $ctype = "application/rss+xml"; break;
	case 'rtf'      : $ctype = "text/rtf"; break;
	case 'rtx'      : $ctype = "text/richtext"; break;
	case 'sgm'      : $ctype = "text/sgml"; break;
	case 'sgml'     : $ctype = "text/sgml"; break;
	case 'sh'       : $ctype = "application/x-sh"; break;
	case 'shar'     : $ctype = "application/x-shar"; break;
	case 'sig'      : $ctype = "application/pgp-signature"; break;
	case 'silo'     : $ctype = "model/mesh"; break;
	case 'sit'      : $ctype = "application/x-stuffit"; break;
	case 'skd'      : $ctype = "application/x-koan"; break;
	case 'skm'      : $ctype = "application/x-koan"; break;
	case 'skp'      : $ctype = "application/x-koan"; break;
	case 'skt'      : $ctype = "application/x-koan"; break;
	case 'sldx'     : $ctype = "application/vnd.openxmlformats-officedocument.presentationml.slide"; break;
	case 'smi'      : $ctype = "application/smil"; break;
	case 'smil'     : $ctype = "application/smil"; break;
	case 'snd'      : $ctype = "audio/basic"; break;
	case 'so'       : $ctype = "application/octet-stream"; break;
	case 'spl'      : $ctype = "application/x-futuresplash"; break;
	case 'spx'      : $ctype = "audio/ogg"; break;
	case 'src'      : $ctype = "application/x-wais-source"; break;
	case 'stk'      : $ctype = "application/hyperstudio"; break;
	case 'sv4cpio'  : $ctype = "application/x-sv4cpio"; break;
	case 'sv4crc'   : $ctype = "application/x-sv4crc"; break;
	case 'svg'      : $ctype = "image/svg+xml"; break;
	case 'swf'      : $ctype = "application/x-shockwave-flash"; break;
	case 't'        : $ctype = "application/x-troff"; break;
	case 'tar'      : $ctype = "application/x-tar"; break;
	case 'tcl'      : $ctype = "application/x-tcl"; break;
	case 'tex'      : $ctype = "application/x-tex"; break;
	case 'texi'     : $ctype = "application/x-texinfo"; break;
	case 'texinfo'  : $ctype = "application/x-texinfo"; break;
	case 'tif'      : $ctype = "image/tiff"; break;
	case 'tiff'     : $ctype = "image/tiff"; break;
	case 'torrent'  : $ctype = "application/x-bittorrent"; break;
	case 'tr'       : $ctype = "application/x-troff"; break;
	case 'tsv'      : $ctype = "text/tab-separated-values"; break;
	case 'txt'      : $ctype = "text/plain"; break;
	case 'ustar'    : $ctype = "application/x-ustar"; break;
	case 'vcd'      : $ctype = "application/x-cdlink"; break;
	case 'vrml'     : $ctype = "model/vrml"; break;
	case 'vsd'      : $ctype = "application/vnd.visio"; break;
	case 'vss'      : $ctype = "application/vnd.visio"; break;
	case 'vst'      : $ctype = "application/vnd.visio"; break;
	case 'vsw'      : $ctype = "application/vnd.visio"; break;
	case 'vxml'     : $ctype = "application/voicexml+xml"; break;
	case 'wav'      : $ctype = "audio/x-wav"; break;
	case 'wbmp'     : $ctype = "image/vnd.wap.wbmp"; break;
	case 'wbmxl'    : $ctype = "application/vnd.wap.wbxml"; break;
	case 'wm'       : $ctype = "video/x-ms-wm"; break;
	case 'wml'      : $ctype = "text/vnd.wap.wml"; break;
	case 'wmlc'     : $ctype = "application/vnd.wap.wmlc"; break;
	case 'wmls'     : $ctype = "text/vnd.wap.wmlscript"; break;
	case 'wmlsc'    : $ctype = "application/vnd.wap.wmlscriptc"; break;
	case 'wmv'      : $ctype = "video/x-ms-wmv"; break;
	case 'wmx'      : $ctype = "video/x-ms-wmx"; break;
	case 'wrl'      : $ctype = "model/vrml"; break;
	case 'xbm'      : $ctype = "image/x-xbitmap"; break;
	case 'xdssc'    : $ctype = "application/dssc+xml"; break;
	case 'xer'      : $ctype = "application/patch-ops-error+xml"; break;
	case 'xht'      : $ctype = "application/xhtml+xml"; break;
	case 'xhtml'    : $ctype = "application/xhtml+xml"; break;
	case 'xla'      : $ctype = "application/vnd.ms-excel"; break;
	case 'xlam'     : $ctype = "application/vnd.ms-excel.addin.macroEnabled.12"; break;
	case 'xlc'      : $ctype = "application/vnd.ms-excel"; break;
	case 'xlm'      : $ctype = "application/vnd.ms-excel"; break;
	case 'xls'      : $ctype = "application/vnd.ms-excel"; break;
	case 'xlsx'     : $ctype = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"; break;
	case 'xlsb'     : $ctype = "application/vnd.ms-excel.sheet.binary.macroEnabled.12"; break;
	case 'xlt'      : $ctype = "application/vnd.ms-excel"; break;
	case 'xltx'     : $ctype = "application/vnd.openxmlformats-officedocument.spreadsheetml.template"; break;
	case 'xlw'      : $ctype = "application/vnd.ms-excel"; break;
	case 'xml'      : $ctype = "application/xml"; break;
	case 'xpm'      : $ctype = "image/x-xpixmap"; break;
	case 'xsl'      : $ctype = "application/xml"; break;
	case 'xslt'     : $ctype = "application/xslt+xml"; break;
	case 'xul'      : $ctype = "application/vnd.mozilla.xul+xml"; break;
	case 'xwd'      : $ctype = "image/x-xwindowdump"; break;
	case 'xyz'      : $ctype = "chemical/x-xyz"; break;
	case 'zip'      : $ctype = "application/zip"; break;
	default         : $ctype = "application/force-download";
	endswitch;

	return apply_filters( 'music_seller_file_ctype', $ctype );
}
function music_seller_readfile_chunked( $file, $retbytes = TRUE ) {

	$chunksize = 1 * (1024 * 1024);
	$buffer    = '';
	$cnt       = 0;
	$handle    = fopen( $file, 'r' );

	if( $size = @filesize( $file ) ) header("Content-Length: " . $size );

	if ( $handle === FALSE ) return FALSE;

	while ( ! feof( $handle ) ) :
	$buffer = fread( $handle, $chunksize );
	echo $buffer;
	//ob_flush();
	//flush();

	if ( $retbytes ) $cnt += strlen( $buffer );
	endwhile;

	$status = fclose( $handle );

	if ( $retbytes AND $status ) return $cnt;

	return $status;
}
function music_seller_count_download($order_id,$file) {
	$downloads = get_post_meta($order_id,'downloads',true);
	if (!is_array($downloads)) {
		unset($downloads);
		$downloads[$file] = 0;
	}
	error_log(print_r($downloads,true));
	$downloads[$file]++;
	return update_post_meta($order_id,'downloads',$downloads);
}
function music_seller_process_download() {
	error_reporting(0);
	if ($_REQUEST['music_seller_download'] != '') {
		if( function_exists( 'apache_setenv' ) ) @apache_setenv('no-gzip', 1);
		@ini_set( 'zlib.output_compression', 'Off' );
		nocache_headers();
		$loop = new WP_Query( array ( 'post_type' => 'music_seller_order', 'meta_key' => 'txn_id', 'meta_value' => $_REQUEST['txn_id'] ) );

		$order_id = $_REQUEST['order_id'];
		$order = get_post_meta($order_id, null,true);
		$files = get_post_meta($order['postid'][0],'music_seller_file',true);
		
		$file = $_REQUEST['music_seller_download'];
		$requested_file = $files[$file]['file'];
		$ctype = music_seller_get_file_ctype(pathinfo($requested_file,PATHINFO_EXTENSION));
		$downloads = get_post_meta($order_id,'downloads',true);
		
 		if ($downloads[$file] >= get_option('music_seller_downloads_limit')) {
 			wp_die('Oops, you have reached the maximum amount of downloads for this order, you need to order again.');
 		}
		//$gmt_timestamp = get_post_time('U');
		$gmt_timestamp = get_the_time('U',$order_id);

		if (strtotime("+" . get_option('music_seller_link_expiration'),$gmt_timestamp) < time()) {
			wp_die('Oops, the link you are using has expired, you need to order again.');
		}
		header("Robots: none");
		header("Content-Type: " . $ctype . "");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=\"" . apply_filters( 'music_seller_requested_file_name', basename( $requested_file ) ) . "\";");
		header("Content-Transfer-Encoding: binary");
		music_seller_readfile_chunked($requested_file);
		music_seller_count_download($order_id,$file);
		die();
	} else if ($_REQUEST['music_seller_free_download']) {
		$key = $_REQUEST['music_seller_free_download'];
		$post_id = $_REQUEST['postid'];
		$files = get_post_meta($_REQUEST['postid'],'music_seller_file',true);
		$requested_file = $files[$key]['file'];
		if (md5($key . NONCE_KEY) != $_REQUEST['download_key']) {
			wp_die('Wrong download key');
		}
		//all is good, do the free download
		$ctype = music_seller_get_file_ctype(pathinfo($requested_file,PATHINFO_EXTENSION));
		header("Robots: none");
		header("Content-Type: " . $ctype . "");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=\"" . apply_filters( 'music_seller_requested_file_name', basename( $requested_file ) ) . "\";");
		header("Content-Transfer-Encoding: binary");
		music_seller_readfile_chunked($requested_file);
	}
}
function music_seller_title($array,$file) {
	$title = get_option('music_seller_file_title');
	if (!$title) {
		$title = '%%artist%% - %%title%% (%%year%%)';
	}
	foreach ($array['tags']['id3v2'] as $key => $arr) {
		$title = str_replace('%%' . $key . '%%', implode(", ", $arr), $title);
	}
	return $title;
}

if (MUSIC_SELLER_VERSION == 'Full') {
	$music_seller_versionClass = 'music_seller_visible';
} else {
	$music_seller_versionClass = 'music_seller_invisible';
	$music_seller_premiumFeature = 'music_seller_premiumFeature';
}

?>