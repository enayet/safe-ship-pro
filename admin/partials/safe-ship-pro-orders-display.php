<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo '<div class="wrap">';
echo '<h1>' . esc_html__( 'Protected Orders', 'safe-ship-pro' ) . '</h1>';

// Capture filters
$from_date = isset($_GET['from_date']) ? sanitize_text_field($_GET['from_date']) : '';
$to_date   = isset($_GET['to_date']) ? sanitize_text_field($_GET['to_date']) : '';
$status    = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$search    = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// Filter form
echo '<form method="get">';
echo '<input type="hidden" name="page" value="safe-ship-pro-orders" />';
echo '<input type="text" name="s" value="' . esc_attr($search) . '" placeholder="Order ID, customer name, or email" />';
echo ' From: <input type="date" name="from_date" value="' . esc_attr($from_date) . '" />';
echo ' To: <input type="date" name="to_date" value="' . esc_attr($to_date) . '" />';

echo ' Status: <select name="status">';
echo '<option value="">' . esc_html__('All', 'safe-ship-pro') . '</option>';
foreach (wc_get_order_statuses() as $slug => $label) {
    echo '<option value="' . esc_attr($slug) . '" ' . selected($status, $slug, false) . '>' . esc_html($label) . '</option>';
}
echo '</select> ';

submit_button(__('Filter'), 'primary', '', false);
echo '</form>';


// Build order query
$args = array(
    'limit' => -1,
    'meta_query' => array(
        array(
            'key'     => '_safe_ship_pro_protection_added',
            'value'   => 'yes',
            'compare' => '='
        )
    )
);

if ($status) {
    $args['status'] = $status;
}

// Proper date handling



if ($from_date || $to_date) {

    $args['date_created'] = $from_date . '...' . $to_date;
}


$orders = wc_get_orders($args);



// Filter by search manually
if ($search && !empty($orders)) {
    $orders = array_filter($orders, function($order) use ($search) {
        $id = $order->get_id();
        $name = $order->get_formatted_billing_full_name();
        $email = $order->get_billing_email();
        return (
            stripos((string) $id, $search) !== false ||
            stripos($name, $search) !== false ||
            stripos($email, $search) !== false
        );
    });
}

if (!empty($orders)) {
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Order</th><th>Date</th><th>Customer</th><th>Email</th><th>Total</th><th>Status</th></tr></thead>';
    echo '<tbody>';

    foreach ($orders as $order) {
        echo '<tr>';
        echo '<td><a href="' . esc_url(admin_url('post.php?post=' . $order->get_id() . '&action=edit')) . '">#' . $order->get_id() . '</a></td>';
        echo '<td>' . esc_html($order->get_date_created()->date('Y-m-d H:i')) . '</td>';
        echo '<td>' . esc_html($order->get_formatted_billing_full_name()) . '</td>';
        echo '<td>' . esc_html($order->get_billing_email()) . '</td>';
        echo '<td>' . wc_price($order->get_total()) . '</td>';
        echo '<td>' . esc_html(wc_get_order_status_name($order->get_status())) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
} else {
    echo '<p>' . esc_html__('No protected orders found.', 'safe-ship-pro') . '</p>';
}

echo '</div>';
