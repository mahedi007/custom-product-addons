<?php
/*
Plugin Name: Custom Product Addons
Description: Add extra pricing options to the WooCommerce product details page.
Version: 1.0
Author: Mahedi Hasan
*/

function custom_product_addons_enqueue_scripts() {
    wp_enqueue_style('custom-product-addons-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('custom-product-addons-script', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), '', true);
}
add_action('wp_enqueue_scripts', 'custom_product_addons_enqueue_scripts');

// Add addon options to the product details page
function custom_product_addons_options() {
    global $product;

    echo '<div id="custom-product-addons">';
    echo '<div class="accordion-wrapper">';
    echo '<div class="accordion">';
    echo '<input type="radio" name="radio-a" id="check1" checked>';
    echo '<label class="accordion-label" for="check1">Extra Items?</label>';
    echo '<div class="accordion-content">';

    
    $addon_options = get_option('custom_product_addons_options');

    if (is_array($addon_options) && isset($addon_options['addon1_title'], $addon_options['addon1_price'], $addon_options['addon2_title'], $addon_options['addon2_price'], $addon_options['addon3_title'], $addon_options['addon3_price'])) {
        
        $addon1_title = $addon_options['addon1_title'];
        $addon1_price = $addon_options['addon1_price'];
        $addon2_title = $addon_options['addon2_title'];
        $addon2_price = $addon_options['addon2_price'];
        $addon3_title = $addon_options['addon3_title'];
        $addon3_price = $addon_options['addon3_price'];

        echo '<label>';
        echo '<input type="checkbox" name="addon[]" value="addon1" data-price="' . esc_attr($addon1_price) . '"> ';
        echo esc_html($addon1_title) . ' - $' . esc_html($addon1_price);
        echo '</label><br>';

        echo '<label>';
        echo '<input type="checkbox" name="addon[]" value="addon2" data-price="' . esc_attr($addon2_price) . '"> ';
        echo esc_html($addon2_title) . ' - $' . esc_html($addon2_price);
        echo '</label><br>';

        echo '<label>';
        echo '<input type="checkbox" name="addon[]" value="addon3" data-price="' . esc_attr($addon3_price) . '"> ';
        echo esc_html($addon3_title) . ' - $' . esc_html($addon3_price);
        echo '</label><br>';
    } else {
        echo '<p>Addon options are not properly configured.</p>';
    }

    echo '</div>';


    echo '</div>';

   echo '<div class="accordion">';
    echo '<input type="radio" name="radio-a" id="check2">';
    echo '<label class="accordion-label" for="check2">Accessories</label>';
    echo '<div class="accordion-content">';
      echo '<p>Here goes another extra list</p>';
    echo '</div>';
  echo '</div>';
    echo '</div>';
    echo '</div>';

  echo '</div>';
}

add_action('woocommerce_before_add_to_cart_button', 'custom_product_addons_options');

// Adjust product price based on selected addon
function custom_adjust_product_price($cart_object) {
    if (!empty($_POST['addon'])) {
        $addons = $_POST['addon'];
        foreach ($addons as $addon_key) {
            $addon_price = floatval($addon_key);
            $cart_object->add_fee(__('Addon', 'woocommerce'), $addon_price);
        }
    }
}
add_action('woocommerce_cart_calculate_fees', 'custom_adjust_product_price');

// Add selected addons to the cart item data
function custom_add_selected_addons_to_cart_item_data($cart_item_data, $product_id, $variation_id) {
    if (!empty($_POST['addon'])) {
        $addons = $_POST['addon'];
        $cart_item_data['selected_addons'] = $addons;
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'custom_add_selected_addons_to_cart_item_data', 10, 3);


// Display selected addons in the cart
function custom_display_selected_addons_in_cart($item_data, $cart_item) {
    if (!empty($cart_item['selected_addons'])) {
        $addons = $cart_item['selected_addons'];
        foreach ($addons as $addon_key) {
            $addon = get_addon_by_key($addon_key);
            $item_data[] = array(
                'key'     => __('Addon', 'woocommerce'),
                'value'   => $addon['title'] . ' - $' . $addon['price'],
                'display' => '',
            );
        }
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'custom_display_selected_addons_in_cart', 10, 2);


function get_addon_by_key($addon_key) {
    $addon_options = get_option('custom_product_addons_options');
    $addons = array(
        'addon1' => array('title' => $addon_options['addon1_title'], 'price' => $addon_options['addon1_price']),
        'addon2' => array('title' => $addon_options['addon2_title'], 'price' => $addon_options['addon2_price']),
        'addon3' => array('title' => $addon_options['addon3_title'], 'price' => $addon_options['addon3_price']),
    );
    return isset($addons[$addon_key]) ? $addons[$addon_key] : array();
}

// Add admin menu
function custom_product_addons_menu() {
    add_menu_page(
        __('Extra Items', 'custom-product-addons'),
        __('Extra Items', 'custom-product-addons'),
        'manage_options',
        'custom-product-addons',
        'custom_product_addons_settings_page',
        'dashicons-admin-generic', 
        56 
    );
}
add_action('admin_menu', 'custom_product_addons_menu');



function custom_product_addons_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Extra Items', 'custom-product-addons'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('custom_product_addons_options');
            do_settings_sections('custom_product_addons_options');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Extra Item 1 Title', 'custom-product-addons'); ?></th>
                    <td><input type="text" name="custom_product_addons_options[addon1_title]" value="<?php echo esc_attr(get_option('custom_product_addons_options')['addon1_title'] ?? ''); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Extra Item 1 Price', 'custom-product-addons'); ?></th>
                    <td><input type="number" step="1" name="custom_product_addons_options[addon1_price]" value="<?php echo esc_attr(get_option('custom_product_addons_options')['addon1_price'] ?? ''); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Extra Item 2 Title', 'custom-product-addons'); ?></th>
                    <td><input type="text" name="custom_product_addons_options[addon2_title]" value="<?php echo esc_attr(get_option('custom_product_addons_options')['addon2_title'] ?? ''); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Extra Item 2 Price', 'custom-product-addons'); ?></th>
                    <td><input type="number" step="1" name="custom_product_addons_options[addon2_price]" value="<?php echo esc_attr(get_option('custom_product_addons_options')['addon2_price'] ?? ''); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Extra Item 3 Title', 'custom-product-addons'); ?></th>
                    <td><input type="text" name="custom_product_addons_options[addon3_title]" value="<?php echo esc_attr(get_option('custom_product_addons_options')['addon3_title'] ?? ''); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Extra Item 3 Price', 'custom-product-addons'); ?></th>
                    <td><input type="number" step="1" name="custom_product_addons_options[addon3_price]" value="<?php echo esc_attr(get_option('custom_product_addons_options')['addon3_price'] ?? ''); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(__('Save Changes', 'custom-product-addons')); ?>
        </form>
    </div>
    <?php
}



// Initialize addon settings
function custom_product_addons_initialize_settings() {
    add_settings_section(
        'custom_product_addons_section',
        __('Addons Settings', 'custom-product-addons'),
        'custom_product_addons_section_callback',
        'custom-product-addons' 
    );

    add_settings_field(
        'addon1_title',
        __('Addon 1 Title', 'custom-product-addons'),
        'custom_product_addons_addon1_title_callback',
        'custom-product-addons', 
        'custom_product_addons_section'
    );

    add_settings_field(
        'addon1_price',
        __('Addon 1 Price', 'custom-product-addons'),
        'custom_product_addons_addon1_price_callback',
        'custom-product-addons', 
        'custom_product_addons_section'
    );

    add_settings_field(
        'addon2_title',
        __('Addon 2 Title', 'custom-product-addons'),
        'custom_product_addons_addon2_title_callback',
        'custom-product-addons', 
        'custom_product_addons_section'
    );

    add_settings_field(
        'addon2_price',
        __('Addon 2 Price', 'custom-product-addons'),
        'custom_product_addons_addon2_price_callback',
        'custom-product-addons', 
        'custom_product_addons_section'
    );

    add_settings_field(
        'addon3_title',
        __('Addon 3 Title', 'custom-product-addons'),
        'custom_product_addons_addon3_title_callback',
        'custom-product-addons', 
        'custom_product_addons_section'
    );

    add_settings_field(
        'addon3_price',
        __('Addon 3 Price', 'custom-product-addons'),
        'custom_product_addons_addon3_price_callback',
        'custom-product-addons', 
        'custom_product_addons_section'
    );

    register_setting('custom_product_addons_options', 'custom_product_addons_options');
}
add_action('admin_init', 'custom_product_addons_initialize_settings');

// Section callback
function custom_product_addons_section_callback() {
    echo '<p>'.__('Enter the titles and prices for addons.', 'custom-product-addons').'</p>';
}


function custom_product_addons_addon1_title_callback() {
    $options = get_option('custom_product_addons_options');
    echo '<input type="text" id="addon1_title" name="custom_product_addons_options[addon1_title]" value="' . esc_attr($options['addon1_title']) . '" />';
}


function custom_product_addons_addon1_price_callback() {
    $options = get_option('custom_product_addons_options');
    echo '<input type="number" id="addon1_price" name="custom_product_addons_options[addon1_price]" step="1" value="' . esc_attr($options['addon1_price']) . '" />';
}


function custom_product_addons_addon2_title_callback() {
    $options = get_option('custom_product_addons_options');
    echo '<input type="text" id="addon2_title" name="custom_product_addons_options[addon2_title]" value="' . esc_attr($options['addon2_title']) . '" />';
}


function custom_product_addons_addon2_price_callback() {
    $options = get_option('custom_product_addons_options');
    echo '<input type="number" id="addon2_price" name="custom_product_addons_options[addon2_price]" step="1" value="' . esc_attr($options['addon2_price']) . '" />';
}


function custom_product_addons_addon3_title_callback() {
    $options = get_option('custom_product_addons_options');
    echo '<input type="text" id="addon3_title" name="custom_product_addons_options[addon3_title]" value="' . esc_attr($options['addon3_title']) . '" />';
}


function custom_product_addons_addon3_price_callback() {
    $options = get_option('custom_product_addons_options');
    echo '<input type="number" id="addon3_price" name="custom_product_addons_options[addon3_price]" step="1" value="' . esc_attr($options['addon3_price']) . '" />';
}
