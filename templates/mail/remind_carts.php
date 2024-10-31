<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="" content="text/html; charset=utf-8">
    <title>Validate Email</title>
    <style type="text/css">
        @media only screen and (min-device-width: 601px) {.content {width: 100%;max-width: 600px;}}
        body[yahoo] .class {}
        .button {text-align: center; font-size: 18px; font-family: sans-serif; font-weight: bold; padding: 0 30px 0 30px;}
        .button a {color: #ffffff!important; text-decoration: none;}
        .button a:hover {text-decoration: underline;}
        @media only screen and (max-width: 550px), screen and (max-device-width: 550px) {}
        body[yahoo] .buttonwrapper {background-color: transparent!important;}
        body[yahoo] .button a {background-color: #e05443; padding: 15px 15px 13px!important; display: block!important;}
        .center-content {
            width: 100%;
            max-width: 600px;
            padding: 20px 30px;
            box-shadow: 0 5px 10px rgba(0,0,0,.1);
            border: 2px solid #ddd;
            border-top: 1px solid #ddd;
            border-bottom: 0;
        }
        .footer {
            padding: 25px 30px 25px 30px;
            box-shadow: 0 5px 10px rgba(0,0,0,.1);
            border-radius: 0 0 20px 20px;
            background: #ffffff;
            border: 2px solid #ddd;
            border-top: 1px solid #ddd;
        }
        .footer-btn {
            background: #2271b1;
            color: #fff !important;
            padding: 15px 20px;
            margin-right: 15px;
            text-decoration: none;
            border-radius: 3px;
            transition: 0.2s;
            cursor: pointer;
        }
        .header-logo {
            padding: 50px 30px 40px 30px;
        }
        .head-title {
            color: #fff;
            font-family: sans-serif;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
        }
        .head-content {
            width: 100%;
            max-width: 600px;
            padding: 30px;
            box-shadow: 0 5px 10px rgba(0,0,0,.1);
            border-radius: 10px 10px 0 0;
            border: 2px solid #ddd;
            border-bottom: 0;
            background: #404040;
        }
    </style>
</head>
<body bgcolor="#f6f8f1" style="margin: 0;padding: 0;min-width: 100%;background-color: #f8f9fa;">
<table class="content" align="center" cellpadding="0" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td class="content head-content" bgcolor="#ffffff">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td class="head-title">Cart Order Reminder</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td class="content center-content" bgcolor="#ffffff">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td style="color: #153643;font-family: sans-serif;font-size: 16px;">
                        <h2>Your Cart items</h2>
                        <ul>
		                    <?php
		                    $total = 0;
                            foreach(unserialize($cart->cart_items) as $product_item) {
		                        $total += $product_item['total'];
		                        ?>
                                <li><?php echo esc_html($product_item['name'].' - '.get_woocommerce_currency_symbol().$product_item['total']." ({$product_item['quantity']}x) "); ?></li>
		                    <?php } ?>
                        </ul>
                        <p><b>Total</b>: <?php echo esc_html(get_woocommerce_currency_symbol().$total)?></p>
                        <p><a href="<?php echo esc_attr(wc_get_cart_url().'?cart_session_set='.$cart->id); ?>" style=" display: block; background: #0072ff; color: #fff; text-decoration: none; text-align: center; padding: 15px; ">Go to Cart!</a></p>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td class="footer" bgcolor="#44525f">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td align="center" style="font-family: sans-serif;font-size: 14px;color: #000;line-height: 25px;">
                        Finish your order or delete cart by the link
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>