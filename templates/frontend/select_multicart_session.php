<?php if(!empty($session_carts)) : ?>
<div class="select-sessions-table">
        <h3>Select Saved Cart</h3>
        <?php foreach($session_carts as $id => $session) {
            $address = unserialize($session->address);
            $billing = $address['billing'];
            $shipping = $address['shipping'];

            $cart_post = get_post($session->post_id);
            $cart_data = get_post_meta($session->post_id, 'cart_settings', true);

            if(!is_array($cart_data)) {
                continue;
            }
        ?>
        <div class="session-item">
            <div class="head-session-item">
                <div class="date-session"><?php echo esc_html(date('d.m.Y H:i', strtotime($cart_data['created_date']))); ?></div>
                <div class="name-session"><?php echo esc_html($cart_post->post_title); ?></div>
                <div class="action-session"><a href="<?php if(isset($session->security_id)) { echo esc_html(wc_get_cart_url().'?cart_session_set='.$session->security_id); } ?>">Select Cart</a> <a href="<?php if(isset($session->security_id)) { echo esc_html(wc_get_cart_url().'?cart_session_delete='.$session->security_id); } ?>">Delete</a></div>
            </div>
            <div class="body-session-content">
                <div class="session-items">
                    <ul>
                        <?php foreach(unserialize($session->cart_items) as $product_item) { ?>
                            <li><?php echo esc_html($product_item['name'].' - '.get_woocommerce_currency_symbol().$product_item['total']." ({$product_item['quantity']}x) "); ?></li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="session-address">
                    <div class="billing-session"><b>Billing address</b>: <?php echo esc_html(implode(' ', $billing)); ?></div>
                    <div class="shipping-session"><b>Shipping address</b>: <?php echo esc_html(implode(' ', $shipping)); ?></div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
<a class="clear-all-multi-carts" href="<?php echo esc_attr(wc_get_cart_url().'?clear_all_carts'); ?>">Clear all Carts</a>
<?php endif; ?>