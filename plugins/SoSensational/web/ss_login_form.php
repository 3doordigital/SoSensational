<?php
$args = array(
        'echo'           => true,
        'redirect'       => ABSPATH .'edit-advertiser/', 
        'form_id'        => 'loginform',
        'label_username' => __( 'Username' ),
        'label_password' => __( 'Password' ),
        'label_remember' => __( 'Remember Me' ),
        'label_log_in'   => __( 'Log In' ),
        'id_username'    => 'user_login',
        'id_password'    => 'user_pass',
        'id_remember'    => 'rememberme',
        'id_submit'      => 'wp-submit',
        'remember'       => true,
        'value_username' => NULL,
        'value_remember' => false
);  ?>
 
<div class="login-form">  

<div class="ss_login_form">
<?php echo wp_login_form( $args ); ?>
</div>