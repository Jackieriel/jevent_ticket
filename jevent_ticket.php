<?php
/*
Plugin Name: J-Event-Ticket
Description: A plugin to sell tickets for Akwa Ibom Tech Week.
Version: 1.0
Author: Your Name
*/

// Register Custom Post Type for Tickets
function jevent_ticket_post_type() {
    register_post_type('ticket', array(
        'labels' => array(
            'name' => 'Tickets',
            'singular_name' => 'Ticket',
            'add_new' => 'Add New Ticket',
            'add_new_item' => 'Add New Ticket',
            'edit_item' => 'Edit Ticket',
            'new_item' => 'New Ticket',
            'view_item' => 'View Ticket',
            'search_items' => 'Search Tickets',
            'not_found' => 'No tickets found',
            'not_found_in_trash' => 'No tickets found in trash',
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'menu_icon' => 'dashicons-tickets-alt',
    ));
}
add_action('init', 'jevent_ticket_post_type');

// Add Custom Meta Box for Ticket Price
function jevent_add_meta_boxes() {
    add_meta_box('jevent_ticket_price', 'Ticket Price', 'jevent_ticket_price_callback', 'ticket', 'side', 'default');
}
add_action('add_meta_boxes', 'jevent_add_meta_boxes');

function jevent_ticket_price_callback($post) {
    wp_nonce_field('jevent_save_ticket_price', 'jevent_ticket_price_nonce');
    $value = get_post_meta($post->ID, '_ticket_price', true);
    echo '<label for="jevent_ticket_price_field">Price</label>';
    echo '<input type="text" id="jevent_ticket_price_field" name="jevent_ticket_price_field" value="' . esc_attr($value) . '" size="25" />';
}

function jevent_save_ticket_price($post_id) {
    if (!isset($_POST['jevent_ticket_price_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['jevent_ticket_price_nonce'], 'jevent_save_ticket_price')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (!isset($_POST['jevent_ticket_price_field'])) {
        return;
    }
    $price = sanitize_text_field($_POST['jevent_ticket_price_field']);
    update_post_meta($post_id, '_ticket_price', $price);
}
add_action('save_post', 'jevent_save_ticket_price');

// Add Admin Menu
function jevent_admin_menu() {
    add_menu_page('J-Event-Ticket', 'J-Event-Ticket', 'manage_options', 'jevent-ticket', 'jevent_dashboard_page', 'dashicons-tickets-alt', 6);
    add_submenu_page('jevent-ticket', 'Create Ticket', 'Create Ticket', 'manage_options', 'post-new.php?post_type=ticket');
    add_submenu_page('jevent-ticket', 'Manage Tickets', 'Manage Tickets', 'manage_options', 'edit.php?post_type=ticket');
    add_submenu_page('jevent-ticket', 'View Purchased', 'View Purchased', 'manage_options', 'jevent-view-purchased', 'jevent_view_purchased_page');
}
add_action('admin_menu', 'jevent_admin_menu');

function jevent_dashboard_page() {
    echo '<h1>J-Event-Ticket Dashboard</h1>';
    echo '<p>Welcome to the J-Event-Ticket plugin dashboard. Use the menu on the left to manage your tickets.</p>';
}

function jevent_view_purchased_page() {
    echo '<h1>Purchased Tickets</h1>';
    // Code to display purchased tickets will go here.
}

// Add Shortcode to Display Tickets
function jevent_ticket_shortcode($atts) {
    $atts = shortcode_atts(array('id' => ''), $atts, 'jevent_ticket');
    $ticket_id = $atts['id'];

    if (empty($ticket_id)) {
        return '<p>No ticket ID provided.</p>';
    }

    $ticket = get_post($ticket_id);
    if (!$ticket || $ticket->post_type != 'ticket') {
        return '<p>Invalid ticket ID.</p>';
    }

    $price = get_post_meta($ticket_id, '_ticket_price', true);
    ob_start();
    ?>
    <div class="jevent-ticket">
        <h2><?php echo esc_html($ticket->post_title); ?></h2>
        <?php if (has_post_thumbnail($ticket_id)) : ?>
            <div class="ticket-thumbnail">
                <?php echo get_the_post_thumbnail($ticket_id, 'medium'); ?>
            </div>
        <?php endif; ?>
        <p>Price: <?php echo esc_html($price); ?></p>
        <p><?php echo esc_html($ticket->post_content); ?></p>
        <div id="ticketForm<?php echo esc_attr($ticket_id); ?>" class="ticket-form">
            <form id="ticketPurchaseForm<?php echo esc_attr($ticket_id); ?>" class="ticket-purchase-form">
                <input type="hidden" name="ticket_id" value="<?php echo esc_attr($ticket_id); ?>">
                <label for="first_name<?php echo esc_attr($ticket_id); ?>">First Name:</label>
                <input type="text" id="first_name<?php echo esc_attr($ticket_id); ?>" name="first_name"><br>
                <label for="last_name<?php echo esc_attr($ticket_id); ?>">Last Name:</label>
                <input type="text" id="last_name<?php echo esc_attr($ticket_id); ?>" name="last_name"><br>
                <label for="email<?php echo esc_attr($ticket_id); ?>">Email:</label>
                <input type="email" id="email<?php echo esc_attr($ticket_id); ?>" name="email"><br>
                <label for="confirm_email<?php echo esc_attr($ticket_id); ?>">Confirm Email:</label>
                <input type="email" id="confirm_email<?php echo esc_attr($ticket_id); ?>" name="confirm_email"><br>
                <label for="contact_phone<?php echo esc_attr($ticket_id); ?>">Contact Phone:</label>
                <input type="text" id="contact_phone<?php echo esc_attr($ticket_id); ?>" name="contact_phone"><br>
                <label for="source<?php echo esc_attr($ticket_id); ?>">How did you hear about Akwa Ibom Tech Week?</label>
                <input type="text" id="source<?php echo esc_attr($ticket_id); ?>" name="source"><br>
                <label for="company<?php echo esc_attr($ticket_id); ?>">Company:</label>
                <input type="text" id="company<?php echo esc_attr($ticket_id); ?>" name="company"><br>
                <label for="designation<?php echo esc_attr($ticket_id); ?>">Designation:</label>
                <input type="text" id="designation<?php echo esc_attr($ticket_id); ?>" name="designation"><br>
                <label for="accommodation<?php echo esc_attr($ticket_id); ?>">Do you need discounted accommodation through the organizers?</label>
                <select id="accommodation<?php echo esc_attr($ticket_id); ?>" name="accommodation">
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select><br>
                <input type="submit" class="pay-button" value="Make Payment">
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('jevent_ticket', 'jevent_ticket_shortcode');

// Enqueue Scripts
function jevent_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('vpay-sdk', 'https://api.vpay.africa/v1/vpay-sdk.js', array(), null, true);
    wp_enqueue_script('jevent-script', plugin_dir_url(__FILE__) . 'jevent.js', array('jquery', 'vpay-sdk'), null, true);

    // Localize the script with new data
    $script_data_array = array(
        'ajax_url' => admin_url('admin-ajax.php'),
    );
    wp_localize_script('jevent-script', 'jevent_ajax_object', $script_data_array);
}
add_action('wp_enqueue_scripts', 'jevent_enqueue_scripts');

// Handle Ticket Purchase
function jevent_handle_purchase() {
    if (!isset($_POST['ticket_id']) || !isset($_POST['first_name']) || !isset($_POST['last_name']) || !isset($_POST['email']) || !isset($_POST['confirm_email']) || !isset($_POST['contact_phone']) || !isset($_POST['source']) || !isset($_POST['company']) || !isset($_POST['designation']) || !isset($_POST['accommodation'])) {
        wp_send_json_error('Please fill all required fields.');
        return;
    }

    // Process your payment logic here
    // Example: Simulating success
    wp_send_json_success('Payment successful!');
}
add_action('wp_ajax_jevent_handle_purchase', 'jevent_handle_purchase');
add_action('wp_ajax_nopriv_jevent_handle_purchase', 'jevent_handle_purchase');

// JavaScript for frontend interactions
add_action('wp_footer', 'jevent_ticket_js');
function jevent_ticket_js() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('.ticket-purchase-form').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();
            // Add VPay SDK integration here
            var paymentData = {
                first_name: $('#first_name<?php echo esc_attr($ticket_id); ?>').val(),
                last_name: $('#last_name<?php echo esc_attr($ticket_id); ?>').val(),
                email: $('#email<?php echo esc_attr($ticket_id); ?>').val(),
                amount: '<?php echo esc_attr($price); ?>', // Replace with dynamic amount if needed
                // Add other necessary data
            };

            VPay.createToken({
                amount: paymentData.amount,
                currency: 'NGN', // Replace with your currency
                email: paymentData.email,
                name: paymentData.first_name + ' ' + paymentData.last_name,
                onApproved: function(response) {
                    // Handle successful payment here
                    // You can send the payment details to your server
                    paymentData.token = response.token; // Example assuming VPay returns a token
                    paymentData.ticket_id = <?php echo esc_js($ticket_id); ?>;
                    paymentData.accommodation = $('#accommodation<?php echo esc_attr($ticket_id); ?>').val();

                    $.ajax({
                        url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
                        method: 'POST',
                        data: {
                            action: 'jevent_handle_purchase',
                            ticket_id: paymentData.ticket_id,
                            first_name: paymentData.first_name,
                            last_name: paymentData.last_name,
                            email: paymentData.email,
                            confirm_email: paymentData.email,
                            contact_phone: $('#contact_phone<?php echo esc_attr($ticket_id); ?>').val(),
                            source: $('#source<?php echo esc_attr($ticket_id); ?>').val(),
                            company: $('#company<?php echo esc_attr($ticket_id); ?>').val(),
                            designation: $('#designation<?php echo esc_attr($ticket_id); ?>').val(),
                            accommodation: paymentData.accommodation,
                            vpay_token: paymentData.token,
                        },
                        success: function(response) {
                            alert(response.data);
                            $('.ticket-purchase-form').trigger("reset");
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                            alert('Error: ' + xhr.responseText);
                        }
                    });
                },
                onDeclined: function(response) {
                    alert('Payment declined: ' + response.message);
                },
                onError: function(error) {
                    alert('Error processing payment: ' + error.message);
                }
            });
        });
    });
    </script>
    <?php
}

// Add Shortcode Column to Ticket List Table
function jevent_add_shortcode_column($columns) {
    $columns['shortcode'] = 'Shortcode';
    return $columns;
}
add_filter('manage_ticket_posts_columns', 'jevent_add_shortcode_column');

function jevent_shortcode_column_content($column, $post_id) {
    if ($column === 'shortcode') {
        echo '[jevent_ticket id="' . $post_id . '"]';
    }
}
add_action('manage_ticket_posts_custom_column', 'jevent_shortcode_column_content', 10, 2);

// Uninstall script to clean up data
register_uninstall_hook(__FILE__, 'jevent_uninstall');

function jevent_uninstall() {
    // Delete all custom post type entries
    $tickets = get_posts(array('post_type' => 'ticket', 'numberposts' => -1));
    foreach ($tickets as $ticket) {
        wp_delete_post($ticket->ID, true);
    }

    // Optionally, delete custom post type data
    // This part is optional if you've stored any custom options or settings
    // delete_option('your_custom_option_name');
}
?>
