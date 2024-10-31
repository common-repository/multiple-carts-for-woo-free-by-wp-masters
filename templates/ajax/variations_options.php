<?php
if($product->is_type('variable')) {
	$variations['id'] = $product->get_children();
	foreach($variations['id'] as $variation_id) {
		$product = wc_get_product($variation_id); ?>
		<option value="<?php echo esc_attr($variation_id); ?>"><?php echo esc_html($product->name); ?></option>'
<?php
	}
} else { ?>
	<option value="0">No variation</option>
<?php
}