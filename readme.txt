=== Multiple Carts, Persistent Carts, Abandoned Carts, MultiVendors for Woo - Free by WP Masters ===
Contributors: wpmasterscom
Tags: save cart, multi cart, remind cart, demand order, add products user cart, supplier product, vendor product, save address, different address cart
Requires at least: 4.7
Tested up to: 6.2
Stable tag: 1.0.2
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
MultiCart gives customers a feature to save different cart items and shipping address. Reminders for not finished order.

== Description ==

A powerful, completely free multicart, multivendor and multiaddress extension with abandoned carts feature for your WooCommerce. Please see screenshots to have a quick overview of what this plugin can do.

You can also have a look at more detailed video demo and comment it (or make a feature request) [here](https://www.awesomescreenshot.com/video/9966691?key=c77694255856b1841174380fc179557f "https://www.awesomescreenshot.com/video/9966691?key=c77694255856b1841174380fc179557f").

= This plugin is designed to: =
* Allow users to have multiple carts, multiple addresses and use/pay them separately;
* Store pending user orders and convert them into an order in the future;
* Automatically split carts by vendors;
* Attach vendors to products;
* Work with Carts and Vendors (Suppliers) like with custom post types;
* Allow admins to create Carts for users manually (by phone for instance) and then convert Carts to orders;
* Remind your clients about Abandoned Carts with Email notifications.

= Popular use cases =
* Client calls you by phone and ask to create order with some products by quantity with particular address
* You need to create order manually for existing customer
* Remind customer about items in the saved cart for finish order and raise conversion (Abandoned Carts)
* Divide products in cart by Suppliers or Vendors
* Create custom cart content for Customers
* Fast creating custom orders for Customers
* Your clients want to pay orders separately
* Your clients want to ship orders to separate addresses

= How to Split Carts =
In addition, it is possible to set a supplier for each product in order to automatically divide baskets by them, let's say that baskets are divided by brands, or if you have a store with many sellers, where each has different delivery conditions and time intervals, it is much more convenient to create individual orders in WooCommerce for each supplier than creating an order containing products from different suppliers and hiring a manager to supervise the process.

= Carts Split Types =
Carts have 3 types of division - goods without a supplier, division by supplier, as well as individual baskets that are added for an individual user, for example, when you need to manually create an order for a user over the phone. Also in the saved baskets, it is possible to create an order based on the contents of the basket and the address data that has been saved.

= Creating Orders From Carts =
Carts for which an order was placed are transferred to the Finished status. If the user has saved items in the cart that he has not checked out in the last 3 days, he will be sent an email with a notification. It is important to consider that baskets are created automatically without any additional user action based on division types.

= How to use it =
* Install and activate plugin
* After activation you will see two new sections in sidebar "Carts" and "Suppliers"
* Carts is show all customers carts and their status. If order is created - cart show as finished and don't appear in the Cart page.
* In the Cart section you can also create custom Cart for customers or edit their carts content.
* Supplier section is give you feature to divide products by Supplier, Vendor or Brand, or by Tag. Just like group for products which is set in Edit product.
* If customer not finished cart - he will get remind mail every 3 days. He can delete cart, or finish order.

= Important =
* You need to have WooCommerce installed before using this plugin
* This plugin changes checkout behavior. We recommend testing plugin on staging before moving to live

= Free WordPress Plugins by WP Masters =
* [Clone Woo Orders](https://wordpress.org/plugins/clone-woo-orders-free-by-wp-masters/ "https://wordpress.org/plugins/clone-woo-orders-free-by-wp-masters/")
* [Import & Update Products, Variations and Attributes from XLSX](https://wordpress.org/plugins/import-products-variations-and-attributes-free-by-wp-masters/ "https://wordpress.org/plugins/import-products-variations-and-attributes-free-by-wp-masters/")
* [Multiple Carts, Persistent Carts, Abandoned Carts, MultiVendors for Woo](https://wordpress.org/plugins/multiple-carts-for-woo-free-by-wp-masters/ "https://wordpress.org/plugins/multiple-carts-for-woo-free-by-wp-masters/")
* [Custom & One-Page Checkout for Woo](https://wordpress.org/plugins/custom-one-page-checkout-for-woo-free-by-wp-masters/)
* [Promo & Referral URLs Generator, Coupons Auto Apply for Woo](https://wordpress.org/plugins/promo-referral-urls-generator-coupons-auto-apply-for-woo-free-by-wp-masters/)
* [One-Time Products Purchases for Woo](https://wordpress.org/plugins/wpm-only-one-buy-by-all-time-free-by-wp-masters/)
* [Posts Navigation Links for Sections and Headings](https://wordpress.org/plugins/posts-navigation-links-for-sections-and-headings-free-by-wp-masters/)

= Tags =
save cart, multi cart, multi vendor, multiple addresses, multiple carts, remind cart, abandoned cart, order on demand, add products user cart, supplier product, vendor product, save address, different address cart

== Screenshots ==
1. How saved Carts will be shown in the Cart page. We can select content for Cart.
2. Cart content is saved to Custom post type where we can change cart items and address details, also create custom order.
3. On list Cart posts we can see status about carts. Here will be shown every users cart and their status. Also registered users will get remind mail if Cart is not ordered.
4. Suppliers section just for group carts by products. One cart is can't have products from different suppliers - it will be divided.
5. Select Supplier in Product edit section for prevent buy this product with No supplier items. Must have for Vendors.
6. Created custom order from Carts post. You can easily create orders for customers with custom content and address

== Changelog ==

= 1.0.2 =
* Bugfix

= 1.0.1 =
* Clear all carts button added on frontend
* Fix session ID

= 1.0.0 =
* Initial version