<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! empty( $_POST ) && check_admin_referer( 'insert_publisher' ) ) {
  if (! empty($_POST['publisherId'])) {
      $publisherId = sanitize_text_field($_POST['publisherId']);
      $publisherId = intval($publisherId);
      delete_option('cal_publisherId');
      add_option('cal_publisherId', $publisherId);
  } else {
    echo "<div class='error'>FAILED! Insert publisher ID</div>";
  }
}
?>
<div class="wrap" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html"
     xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
    <h2>Configure your Convert-a-link settings </h2>
    <table>
        <tr>
            <td>
                <b>Step 1: Turn on Convert-a-link option in your Affiliate Window setting page</b>
                <br/>
                <i>under 'Links and Tools' menu</i>
            </td>
            <td><a target="_blank" href="http://wiki.affiliatewindow.com/index.php/Convert-a-link"><img src=<?php echo plugins_url("convert-a-link/one.png") ?>></a></td>
        </tr>
        <tr>
            <td><b>Step 2: Enter your publisherId and save.</b></td>
            <td>
                <form enctype="multipart/form-data" name="convert-a-link" method="post" action="">
                  <?php echo wp_nonce_field( 'insert_publisher'); ?>
                <input type="number" value="<?php echo esc_attr(get_option('cal_publisherId'))?>" name="publisherId"/>
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
                </form>
            </td>
        </tr>
    </table>
</div>
