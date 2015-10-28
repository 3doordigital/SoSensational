<?php

/*
  Plugin Name: Affiliate Shop &raquo; TradeDoubler (Hugo Boss) API
  Plugin URI:
  Description: TradeDoubler API for WP Affiliate Shop
  Version: 1.0.0
  Author: 3 Door Digital
  Author URI: http://www.3doordigital.com
  License: GPL V3
 */

/**
 * Adds TradeDoubler API to Affiliate Shop
 *
 *
 * @copyright  2015 3 Door Digital
 * @license    GPL v3
 * @version    Release: 1.0.0
 * @since      Class available since Release 1.0.0
 */
class WordPress_Affiliate_Shop_TradeDoubler_HB {

    static $options;

    public function __construct() {
        global $wp_aff;
        $this->option = $wp_aff->get_option();
        self::$options = $wp_aff->get_option();
        $this->token = '8EFD196167B1A341C02BC3052CE293876673C7C6';
        register_activation_hook(__FILE__, array($this, 'activation'));
        register_deactivation_hook(__FILE__, array($this, 'deactivation'));
    }

    /**
     * Fires on plugin activation. Sets inital options
     *
     * @return nothing
     */
    static function activation() {
        global $wp_aff;
        if (!isset(self::$options['apis'])) {
            $array = $wp_aff->get_option();
            $array['apis']['tradedoubler-hb'] = array(
                'name' => 'tradedoubler-hb',
                'nicename' => 'Hugo Boss',
                'class' => 'WordPress_Affiliate_Shop_TradeDoubler_HB'
            );
        } else {
            $array = $wp_aff->get_option();
            $array['apis']['tradedoubler-hb'] = array(
                'name' => 'tradedoubler-hb',
                'nicename' => 'Hugo Boss',
                'class' => 'WordPress_Affiliate_Shop_TradeDoubler_HB'
            );
        }
        update_option($wp_aff->option_name, $array);
    }

    /**
     * Fires on plugin deactivation.
     *
     * @return nothing
     */
    static function deactivation() {
        global $wp_aff;
        $array = $wp_aff->get_option();
        unset($array['apis']['tradedoubler-hb']);
        update_option($wp_aff->option_name, $array);
    }

    /**
     * Returns array of merchants
     *
     * @return array	$array
     */
    public function merchants() {

        $array = array();
        $array['ID-tdhb'] = array(
            'ID' => 'tdhb',
            'name' => 'Hugo Boss',
            'aff' => 'tradedoubler-hb',
        );
        return $array;
    }

    public function update_feed($merchant, $merch) {

        $data = array();
        $out = array();
        $out['success'] = 0;
        $out['error'] = 0;
        $out['status'] = 0;

        $url = 'http://pf.tradedoubler.com/export/export?myFeed=14387877802501130&myFormat=14387877802501130';
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '3072M');
        }
        set_time_limit(0);
        $upload_dir = wp_upload_dir();
        $user_dirname = $upload_dir['basedir'] . '/feed-data';
        if (!file_exists($user_dirname))
            wp_mkdir_p($user_dirname);

        $destination = $user_dirname . '/td_' . date('d-Y-m-H-i-s') . '.csv';
        $fp = fopen($destination, "w+");
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
        curl_exec($ch);
        if (!curl_errno($ch)) {
            $out['status'] = 1;
        }
        curl_close($ch);
        fclose($fp);

        if ($out['status'] == 1) {
            if (($handle = fopen($destination, 'r')) !== false) {
                global $wpdb;

                // get the first row, which contains the column-titles (if necessary)
                $header = fgetcsv($handle);
                $out['status'] = 1;
                // loop through the file line-by-line
                while ($product = fgetcsv($handle)) {
                    $table_name = $wpdb->prefix . "feed_data";
                    $replace = $wpdb->insert($table_name, array(
                        'product_id' => 'tdhb_' . $product[6],
                        'product_aff' => 'tradedoubler-hb',
                        'product_merch' => 'tdhb_',
                        'product_title' => $product[0],
                        'product_brand' => 'Hugo Boss',
                        'product_image' => $product[2],
                        'product_desc' => $product[3],
                        'product_price' => $product[4],
                        'product_rrp' => $product[13],
                        'product_link' => $product[1],
                            )
                    );
                    $error = $wpdb->last_error;
                    //echo $replace;
                    switch ($replace) {
                        case false :
                            //die( $wpdb->last_query );
                            $out['message'][] = $wpdb->last_query;
                            $out['error'] ++;
                            break;
                        case 1 :
                            $out['message'][] = 'Inserted td-hb_' . $product[6];
                            $out['success'] ++;
                            break;
                        default :
                            $out['message'][] = 'Replaced td-hb_' . $product[6];
                            break;
                    }

                    unset($data);
                    unset($product);
                }
            } else {
                $out = 'Failed';
            }
            fclose($handle);
        }

        return $out;
    }

}

register_activation_hook(__FILE__, array('WordPress_Affiliate_Shop_TradeDoubler_HB', 'activation'));
register_deactivation_hook(__FILE__, array('WordPress_Affiliate_Shop_TradeDoubler_HB', 'deactivation'));
