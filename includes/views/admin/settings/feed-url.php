<?php
defined('ABSPATH') or die;

$isProtected = get_option('altoshift_feed_password_protected', 'no');
$password = get_option('altoshift_feed_password', '');
$feedUrl = get_feed_link('altoshift');

if ($isProtected === 'yes' && strlen($password) > 0) {
    $feedUrl = add_query_arg('secret', $password, $feedUrl);
}

?>
<div style="margin-top: 10px;">
    <span class="description"><?php _e('Post URL', 'woocommerce-altoshift'); ?></span>
    <input type="text" readonly="readonly" value="<?php echo $feedUrl; ?>" style="width: 100%;">
</div>
