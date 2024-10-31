<style>
    #select_suppliers {
        padding: 15px 0 10px;
    }
    #select_suppliers select {
        width: 100%;
        background: #00b900;
        color: #fff;
    }
</style>
<div id="select_suppliers">
    <div class="components-base-control__field">
        <select name="product_supplier">
            <option value="0">No Supplier</option>
            <?php foreach($suppliers as $supplier) : ?>
                <option value="<?php echo esc_attr($supplier->ID); ?>" <?php if(isset($product_supplier) && $product_supplier == $supplier->ID) { echo esc_attr( 'selected' ); }?>><?php echo esc_html($supplier->post_title); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>