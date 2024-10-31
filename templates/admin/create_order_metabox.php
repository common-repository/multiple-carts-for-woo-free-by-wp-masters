<style>
    #create_order_carts {
        padding: 15px 0 10px;
        display: flex;
    }

    #create_order_carts a {
        padding: 10px 15px;
        text-decoration: none;
        margin-top: 5px;
        opacity: 0.8;
        background: #007cba;
        color: #fff;
        border: 0;
        cursor: pointer;
    }

    #create_order_carts a:hover {
        opacity: 1;
    }
</style>
<div id="create_order_carts">
    <div class="components-base-control__field">
        <a href="<?php echo esc_attr( get_edit_post_link( sanitize_text_field($_GET['post']) ) . '&create_order_cart' ); ?>">Create Order</a>
    </div>
</div>