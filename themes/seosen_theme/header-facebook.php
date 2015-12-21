<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
        <title><?php wp_title(''); ?></title>
        <?php wp_head(); ?>
        <!--<script src='https://www.google.com/recaptcha/api.js'></script>-->
    </head>
    <body <?php body_class('facebook-comp'); ?>>
<?php global $seosen_options; echo $seosen_options['custom_html']; ?>
