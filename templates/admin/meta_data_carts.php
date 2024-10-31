<?php if(is_plugin_active('woocommerce/woocommerce.php')) { ?>
<div class="meta-datas-fields">
	<h3>Main Information</h3>
	<div class="doubled-fields">
		<div class="form-data-field">
			<label for="cart_status">Cart Status</label>
			<select name="wpm_multicart[status]" id="cart_status">
                <option value="created" <?php if(isset($cart_settings['status']) && $cart_settings['status'] == 'created') { echo esc_attr('selected'); } ?>>Created</option>
                <option value="finished" <?php if(isset($cart_settings['status']) && $cart_settings['status'] == 'finished') { echo esc_attr('selected'); } ?>>Finished</option>
			</select>
		</div>
		<div class="form-data-field">
			<label for="created_date">Created Date</label>
			<input type="datetime-local" id="created_date" name="wpm_multicart[created_date]" value="<?php echo esc_attr(date('Y-m-d H:i:s', strtotime(isset($cart_settings['created_date']) ? $cart_settings['created_date'] : 'now'))); ?>">
		</div>
	</div>
	<h3 style="margin-bottom: 0;">Cart Products</h3>
    <div class="section_data">
        <div class="head_items" <?php if(isset($cart_settings['product']) && count($cart_settings['product']) > 0) {} else {echo "style='display: none;'";} ?>>
            <div class="number_element">#</div>
            <div class="item-table">Product</div>
            <div class="item-table">Variation</div>
            <div class="item-table">Quantity</div>
        </div>
        <div class="items-list">
            <?php if(isset($cart_settings['product']) && count($cart_settings['product']) > 0) { $i = 1; foreach ($cart_settings['product'] as $item => $product_id) { ?>
                <div class="item-content">
                    <div class="number_element"><?php echo esc_html($i); ?></div>
                    <div class="item-table">
                        <select name="wpm_multicart[product][]" class="select-product" data-search="true">
                            <option value="0">No Product</option>
                            <?php foreach($products as $product) { ?>
                                <option value="<?php echo esc_attr($product->ID); ?>" <?php if($product->ID == $product_id) {echo esc_attr('selected');} ?>><?php echo esc_html($product->post_title); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="item-table">
                        <select name="wpm_multicart[variation][]" class="variation-list">
                            <?php if(!isset($variations[$item])) { ?>
                                <option value='0'>No variation</option>
                            <?php } ?>
                            <?php foreach($variations[$item]['id'] as $number => $variation_id) { ?>
                                <option value="<?php echo esc_attr($variation_id); ?>" <?php if($variation_id == $cart_settings['variation'][$item]) {echo esc_attr('selected');} ?>><?php echo esc_html($variations[$item]['title'][$number]); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="item-table">
                        <input type="number" name="wpm_multicart[quantity][]" value="<?php echo esc_attr($cart_settings['quantity'][$item]); ?>">
                    </div>
                    <div class="delete_item"><i class="fas fa-trash"></i></div>
                </div>
                <?php $i++; }} else { ?>
                <div class="item-content" style="display: none">
                    <div class="number_element">1</div>
                    <div class="item-table">
                        <select name="wpm_multicart[product][]" class="select-product" data-search="true">
                            <option value="0">No Product</option>
                            <?php foreach($products as $product) { ?>
                                <option value="<?php echo esc_attr($product->ID) ?>"><?php echo esc_html($product->post_title) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="item-table">
                        <select name="wpm_multicart[variation][]" class="variation-list">
                            <option value='0'>No variation</option>
                        </select>
                    </div>
                    <div class="item-table">
                        <input type="number" name="wpm_multicart[quantity][]" value="1">
                    </div>
                    <div class="delete_item"><i class="fas fa-trash"></i></div>
                </div>
            <?php } ?>
            <button class="button button-primary button-large add-item" type="button"><i class="fas fa-plus-square"></i> Add new Item</button>
        </div>
    </div>
	<div class="doubled-fields">
		<div class="form-data-field">
			<h3>Billing fields</h3>
			<div class="billing-field">
				<label for="billing_first_name">First Name</label>
				<input type="text" id="billing_first_name" name="wpm_multicart[billing][first_name]" value="<?php if(isset($cart_settings['billing']['first_name'])) { echo esc_attr($cart_settings['billing']['first_name']); } ?>">
			</div>
            <div class="billing-field">
                <label for="billing_last_name">Last Name</label>
                <input type="text" id="billing_last_name" name="wpm_multicart[billing][last_name]" value="<?php if(isset($cart_settings['billing']['last_name'])) { echo esc_attr($cart_settings['billing']['last_name']); } ?>">
            </div>
            <div class="billing-field">
                <label for="billing_company">Company</label>
                <input type="text" id="billing_company" name="wpm_multicart[billing][company]" value="<?php if(isset($cart_settings['billing']['company'])) { echo esc_attr($cart_settings['billing']['company']); } ?>">
            </div>
            <div class="billing-field">
                <label for="billing_address_1">Street address</label>
                <input type="text" id="billing_address_1" name="wpm_multicart[billing][address_1]" value="<?php if(isset($cart_settings['billing']['address_1'])) { echo esc_attr($cart_settings['billing']['address_1']); } ?>">
            </div>
            <div class="billing-field">
                <label for="billing_address_2">Apartment, suite, unit, etc.</label>
                <input type="text" id="billing_address_2" name="wpm_multicart[billing][address_2]" value="<?php if(isset($cart_settings['billing']['address_2'])) { echo esc_attr($cart_settings['billing']['address_2']); } ?>">
            </div>
            <div class="billing-field">
                <label for="billing_city">City</label>
                <input type="text" id="billing_city" name="wpm_multicart[billing][city]" value="<?php if(isset($cart_settings['billing']['city'])) { echo esc_attr($cart_settings['billing']['city']); } ?>">
            </div>
            <div class="billing-field">
                <label for="billing_postcode">Postcode / ZIP</label>
                <input type="text" id="billing_postcode" name="wpm_multicart[billing][postcode]" value="<?php if(isset($cart_settings['billing']['postcode'])) { echo esc_attr($cart_settings['billing']['postcode']); } ?>">
            </div>
            <div class="billing-field">
                <label for="billing_country">Country</label>
                <select id="billing_country" name="wpm_multicart[billing][country]">
		            <?php foreach($countries as $code => $country) : ?>
                        <option value="<?php echo esc_attr($code); ?>" <?php if(isset($cart_settings['billing']['country']) && $cart_settings['billing']['country'] == $code) { echo esc_attr('selected'); } ?>><?php echo esc_html($country); ?></option>
		            <?php endforeach; ?>
                </select>
            </div>
            <div class="billing-field">
                <label for="billing_state">State</label>
                <input type="text" id="billing_state" name="wpm_multicart[billing][state]" value="<?php if(isset($cart_settings['billing']['state'])) { echo esc_attr($cart_settings['billing']['state']); } ?>">
            </div>
            <div class="billing-field">
                <label for="billing_email">Email</label>
                <input type="text" id="billing_email" name="wpm_multicart[billing][email]" value="<?php if(isset($cart_settings['billing']['email'])) { echo esc_attr($cart_settings['billing']['email']); } ?>">
            </div>
            <div class="billing-field">
                <label for="billing_phone">Phone</label>
                <input type="text" id="billing_phone" name="wpm_multicart[billing][phone]" value="<?php if(isset($cart_settings['billing']['phone'])) { echo esc_attr($cart_settings['billing']['phone']); } ?>">
            </div>
		</div>
		<div class="form-data-field">
			<h3>Shipping fields</h3>
			<div class="shipping-field">
				<label for="shipping_first_name">First Name</label>
				<input type="text" id="shipping_first_name" name="wpm_multicart[shipping][first_name]" value="<?php if(isset($cart_settings['shipping']['first_name'])) { echo esc_attr($cart_settings['shipping']['first_name']); } ?>">
			</div>
            <div class="shipping-field">
                <label for="shipping_last_name">Last Name</label>
                <input type="text" id="shipping_last_name" name="wpm_multicart[shipping][last_name]" value="<?php if(isset($cart_settings['shipping']['last_name'])) { echo esc_attr($cart_settings['shipping']['last_name']); } ?>">
            </div>
            <div class="shipping-field">
                <label for="shipping_company">First Name</label>
                <input type="text" id="shipping_company" name="wpm_multicart[shipping][company]" value="<?php if(isset($cart_settings['shipping']['company'])) { echo esc_attr($cart_settings['shipping']['company']); } ?>">
            </div>
            <div class="shipping-field">
                <label for="shipping_address_1">Street address</label>
                <input type="text" id="shipping_address_1" name="wpm_multicart[shipping][address_1]" value="<?php if(isset($cart_settings['shipping']['address_1'])) { echo esc_attr($cart_settings['shipping']['address_1']); } ?>">
            </div>
            <div class="shipping-field">
                <label for="shipping_address_2">Apartment, suite, unit, etc.</label>
                <input type="text" id="shipping_address_2" name="wpm_multicart[shipping][address_2]" value="<?php if(isset($cart_settings['shipping']['address_2'])) { echo esc_attr($cart_settings['shipping']['address_2']); } ?>">
            </div>
            <div class="shipping-field">
                <label for="shipping_city">City</label>
                <input type="text" id="shipping_city" name="wpm_multicart[shipping][city]" value="<?php if(isset($cart_settings['shipping']['city'])) { echo esc_attr($cart_settings['shipping']['city']); } ?>">
            </div>
            <div class="shipping-field">
                <label for="shipping_postcode">Postcode / ZIP</label>
                <input type="text" id="shipping_postcode" name="wpm_multicart[shipping][postcode]" value="<?php if(isset($cart_settings['shipping']['postcode'])) { echo esc_attr($cart_settings['shipping']['postcode']); } ?>">
            </div>
            <div class="shipping-field">
                <label for="shipping_country">Country</label>
                <select id="shipping_country" name="wpm_multicart[shipping][country]">
                    <?php foreach($countries as $code => $country) : ?>
                        <option value="<?php echo esc_attr($code); ?>" <?php if(isset($cart_settings['shipping']['country']) && $cart_settings['shipping']['country'] == $code) { echo esc_attr('selected'); } ?>><?php echo esc_html($country); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="shipping-field">
                <label for="shipping_state">State</label>
                <input type="text" id="shipping_state" name="wpm_multicart[shipping][state]" value="<?php if(isset($cart_settings['shipping']['state'])) { echo esc_attr($cart_settings['shipping']['state']); } ?>">
            </div>
		</div>
	</div>
</div>
<?php } else { ?>
    <div class="dependency-warning"><i class="fas fa-question-circle"></i> Install WooCommerce for open all functions</div>
<?php } ?>