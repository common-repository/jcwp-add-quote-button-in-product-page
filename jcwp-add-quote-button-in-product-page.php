<?php
/**
* Plugin Name: JCWP Add Quote button in product page
* Plugin URI: https://jcwpplugins.com/jcwp-add-quote-button-in-product-page/
* Description: A plugin that adds a WhatsApp quote button on the WooCommerce product and shop page.
* Version: 1.0.2
* Author: JcwpPlugins
* Author URI: https://jcwpplugins.com
* Text Domain: jcwp-add-quote-button-in-product-page
* Domain Path: /languages
*/
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
class JCWP_Add_Quote_Button_In_Product_Page {

	private $phone_number;
    private $button_text;
    private $message_text;
    private $img_src;

	public function __construct() {

		$this->phone_number = get_option( 'jcwp_quote_option_field_phone_number', '' );
        $this->button_text = get_option( 'jcwp_quote_option_field_button_text', __( 'Cotizar...', 'jcwp-add-quote-button-in-product-page' ) );
        $this->message_text = get_option( 'jcwp_quote_option_field_message_text', '' );
        $this->img_src = plugins_url( 'img/jcwp-quote-icon.png', __FILE__ );

		add_action( 'admin_menu', array( $this, 'jcwp_quote_button_in_product_page_settings' ) );
		add_action( 'admin_init', array($this, 'jcwp_quote_option_field_register_settings') );
		add_action( 'woocommerce_after_add_to_cart_form', array($this, 'jcwp_quote_option_field_add_button') );
		//add_action( 'woocommerce_product_meta_end', array($this, 'jcwp_quote_option_field_add_button') );
		add_action( 'woocommerce_after_shop_loop_item', array($this, 'jcwp_quote_option_field_add_button_shop') );
		add_action( 'wp_enqueue_scripts', array($this, 'jcwp_quote_option_field_enqueue_styles') );

		add_filter("plugin_action_links_".plugin_basename(__FILE__), array($this,'custom_plugin_settings_link'));


	}

    // This function adds the plugin options page
	public function jcwp_quote_button_in_product_page_settings() {
		add_options_page(
        'Quote button Configuration', // Page Title
        'Quote button Configuration', // Menu Title
        'manage_options', // capability
        'jcwp-quote-button-in-product-page', // menu slug
        array($this ,'jcwp_quote_button_in_product_page_settings_html') // callback function

    );
	}

	public function custom_plugin_settings_link($links) {
		$settings_link = '<a href="options-general.php?page=jcwp-quote-button-in-product-page">'.__('Settings','jcwp-add-quote-button-in-product-page').'</a>';
		array_push($links, $settings_link);
		return $links;
	}

	// This function displays the plugin options page HTML
	public function jcwp_quote_button_in_product_page_settings_html() {
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo __( 'Plugin Settings', 'jcwp-add-quote-button-in-product-page' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'jcwp-quote-plugin-settings-group' ); ?>
				<?php do_settings_sections( 'jcwp-quote-plugin-settings-group' ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php echo __( 'Phone number', 'jcwp-add-quote-button-in-product-page' ); ?></th>
						<td><input type="text" name="jcwp_quote_option_field_phone_number" value="<?php echo esc_attr( get_option('jcwp_quote_option_field_phone_number') ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo __( 'Button text', 'jcwp-add-quote-button-in-product-page' ); ?></th>
						<td><input type="text" name="jcwp_quote_option_field_button_text" value="<?php echo esc_attr( get_option('jcwp_quote_option_field_button_text') ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo __( 'Message text', 'jcwp-add-quote-button-in-product-page' ); ?></th>
						<td><input type="text" name="jcwp_quote_option_field_message_text" value="<?php echo esc_attr( get_option('jcwp_quote_option_field_message_text') ); ?>" /></td>
					</tr>
				</table>
				<h1><?php echo __( 'Display Options', 'jcwp-add-quote-button-in-product-page' ); ?></h1>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php echo __( 'Show button on product page', 'jcwp-add-quote-button-in-product-page' ); ?></th>
						<td><input type="checkbox" name="jcwp_quote_option_field_show_on_product_page" value="1" <?php checked(1, get_option('jcwp_quote_option_field_show_on_product_page'), true); ?>/></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo __( 'Show button on shop page', 'jcwp-add-quote-button-in-product-page' ); ?></th>
						<td><input type="checkbox" name="jcwp_quote_option_field_show_on_shop_page" value="1" <?php checked(1, get_option('jcwp_quote_option_field_show_on_shop_page'), true); ?>/></td>
					</tr>
				</table>
				<table class="form-table" style="border: solid 1px #3313;padding: 10px;display: block;width: auto;">
					<tr valign="top">
						<th scope="row"><?php echo __( 'Show only in these categories:', 'jcwp-add-quote-button-in-product-page' ); ?></th>
						<td><input type="text" placeholder="music,tshirts" name="jcwp_quote_option_field_show_on_categories" value="<?php echo esc_attr( get_option('jcwp_quote_option_field_show_on_categories') ); ?>" /></td>
					</tr>
				</table>
				<pre style="margin: 0;padding:0;"><?php echo __( 'Add the slugs of the categories separated by comma', 'jcwp-add-quote-button-in-product-page' ); ?></pre>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	// Register plugin settings
	public function jcwp_quote_option_field_register_settings() {
		register_setting( 'jcwp-quote-plugin-settings-group', 'jcwp_quote_option_field_phone_number' );
		register_setting( 'jcwp-quote-plugin-settings-group', 'jcwp_quote_option_field_button_text' );
		register_setting( 'jcwp-quote-plugin-settings-group', 'jcwp_quote_option_field_message_text' );
		register_setting( 'jcwp-quote-plugin-settings-group', 'jcwp_quote_option_field_show_on_product_page' );
		register_setting( 'jcwp-quote-plugin-settings-group', 'jcwp_quote_option_field_show_on_shop_page' );
		register_setting( 'jcwp-quote-plugin-settings-group', 'jcwp_quote_option_field_show_on_categories' );
	}

	public function jcwp_quote_option_field_add_button() {
        global $product;
        if ( ! get_option( 'jcwp_quote_option_field_show_on_product_page' ) ) {
            return;
        }
        $quote_link = "https://wa.me/{$this->phone_number}?text=" . urlencode( "{$this->message_text} {$product->get_title()}: {$product->get_permalink()}" );
        $category_slugs = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'slugs'));
        $show_categories = get_option('jcwp_quote_option_field_show_on_categories');
		$show_categories = str_replace(' ', '', $show_categories);
        if(!empty($show_categories)){
        	$show_categories = explode(',', $show_categories);
        	$common_elements = array_intersect($category_slugs, $show_categories);
        	if(!empty($common_elements)){
        		?>
        		<a href="<?php echo esc_attr($quote_link); ?>" class="button jcwp-quote-button" target="_blank">
        			<img src="<?php echo esc_attr($this->img_src); ?>">
        			<?php echo esc_attr($this->button_text); ?>
        		</a>
        		<?php
        	}
        }else{
        	?>
        	<a href="<?php echo esc_attr($quote_link); ?>" class="button jcwp-quote-button" target="_blank">
        		<img src="<?php echo esc_attr($this->img_src); ?>">
        		<?php echo esc_attr($this->button_text); ?>
        	</a>
        	<?php
        }
    }

    public function jcwp_quote_option_field_add_button_shop() {
        global $product;
        if ( ! get_option( 'jcwp_quote_option_field_show_on_shop_page' ) ) {
            return;
        }
        $quote_link = "https://wa.me/{$this->phone_number}?text=" . urlencode( "{$this->message_text} {$product->get_title()}: {$product->get_permalink()}" );
        $category_slugs = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'slugs'));
        $show_categories = get_option( 'jcwp_quote_option_field_show_on_categories' );
        $show_categories = str_replace(' ', '', $show_categories);
        if(!empty($show_categories)){
        	$show_categories = explode(',', $show_categories);
        	$common_elements = array_intersect($category_slugs, $show_categories);
        	if(!empty($common_elements)){
        		?>
        		<a href="<?php echo esc_attr($quote_link); ?>" class="button jcwp-quote-button" target="_blank">
        			<img src="<?php echo esc_attr($this->img_src); ?>">
        			<?php echo esc_attr($this->button_text); ?>
        		</a>
        		<?php
        	}
        }else{
        	?>
        	<a href="<?php echo esc_attr($quote_link); ?>" class="button jcwp-quote-button" target="_blank">
        		<img src="<?php echo esc_attr($this->img_src); ?>">
        		<?php echo esc_attr($this->button_text); ?>
        	</a>
        	<?php
        }
    }

	public function jcwp_quote_option_field_enqueue_styles() {
		wp_enqueue_style( 'my-plugin-styles', plugin_dir_url( __FILE__ ) . 'css/my-plugin-styles.css' );
	}

}
new JCWP_Add_Quote_Button_In_Product_Page();