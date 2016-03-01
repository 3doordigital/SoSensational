<?php

/*
  Plugin Name: Affiliate Shop &raquo; Affilinet API
  Plugin URI:
  Description: Affilinet API for WP Affiliate Shop
  Version: 1.0.0
  Author: 3 Door Digital
  Author URI: http://www.3doordigital.com
  License: GPL V3
 */

/**
 * Adds Affilinet API to Affiliate Shop
 *
 *
 * @copyright  2015 3 Door Digital
 * @license    GPL v3
 * @version    Release: 1.0.0
 * @since      Class available since Release 1.0.0
 */
class WordPress_Affiliate_Shop_Affilinet {

    static $options;

    public function __construct() {
        global $wp_aff;
        $this->option = $wp_aff->get_option();
        self::$options = $wp_aff->get_option();

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
            $array['apis']['affilinet'] = array(
                'name' => 'affilinet',
                'nicename' => 'Affilinet',
                'class' => 'WordPress_Affiliate_Shop_Affilinet'
            );
        } else {
            $array = $wp_aff->get_option();
            $array['apis']['affilinet'] = array(
                'name' => 'affilinet',
                'nicename' => 'Affilinet',
                'class' => 'WordPress_Affiliate_Shop_Affilinet'
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
        unset($array['apis']['affilinet']);
        update_option($wp_aff->option_name, $array);
    }

    /**
     * Returns array of merchants
     *
     * @return array	$array
     */
    public function merchants() {
        $array = array();
        $data = file_get_contents('https://product-api.affili.net/V3/productservice.svc/JSON/GetShopList?PublisherId=705085&password=B6dU3k5cnxe3NMq86MdM', FILE_TEXT);
        $data2 = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data);
        $data2 = json_decode($data2);


        foreach ($data2->Shops as $item) {

            $array['ID-' . $item->ShopId] = array(
                'ID' => (string) $item->ShopId,
                'name' => (string) $item->ShopTitle,
                'aff' => 'affilinet',
            );
        }
        return $array;
    }

    /**
     * Retrieves feed from affilaite for a merchant ($merchant) and replaces the entry in the database.
     *
     * @param  string 	$merchant 	The ID of the merchant
     *
     * @return array	$out
     */
    public function update_feed($merchant, $merch = NULL) {
        $out['success'] = 0;
        $out['error'] = 0;
        $out = array();
        $upload_dir = wp_upload_dir();
        $user_dirname = $upload_dir['basedir'] . '/feed-data';
        if (!file_exists($user_dirname))
            wp_mkdir_p($user_dirname);

        $uc_local_file = $user_dirname . '/affilinet.csv';

        $data = array();
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '4096M');
        }
        set_time_limit(0);
        $url = 'http://productdata-download.affili.net/affilinet_products_' . $merchant . '_705085.CSV?auth=I40KPXuRs0dGogcfI09H&type=CSV';
        $fp = fopen($uc_local_file, "w+");
        $ch = curl_init($url);
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_FILE => $fp
        );
        curl_setopt_array($ch, $options);
        curl_exec($ch);
        if (!curl_errno($ch)) {
            $out['status'] = 1;
        }
        curl_close($ch);
        fclose($fp);

        if (($handle = fopen($uc_local_file, 'r')) !== false) {
            global $wpdb;
            // get the first row, which contains the column-titles (if necessary)
            $header = fgetcsv($handle, 0, ';');
            //print_var( $header );
            $out['status'] = 1;
            $i = 0;
            // loop through the file line-by-line
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                set_time_limit(0);
                $i ++;
                $data = array_combine($header, $data);
                //print_var( $data );

                $table_name = $wpdb->prefix . "feed_data";

                $datainsert = array(
                    'product_id' => $merchant . '_' . $data['ArtNumber'],
                    'product_aff' => 'affilinet',
                    'product_merch' => sanitize_text_field($merchant),
                    'product_title' => sanitize_text_field($data['Title']),
                    'product_brand' => sanitize_text_field($merch),
                    'product_image' => esc_url($data['Img_url']),
                    'product_desc' => sanitize_text_field($data['Description_Short']),
                    'product_price' => sanitize_text_field(str_replace('GBP', '', $data['DisplayPrice'])),
                    'product_rrp' => sanitize_text_field(str_replace('GBP', '', $data['DisplayPrice'])),
                    'product_link' => esc_url($data['Deeplink1']),
                );
                $replace = $wpdb->insert($table_name, $datainsert);

                switch ($replace) {
                    case false :
                        //die( $wpdb->last_query );
                        $out['message'][] = $wpdb->last_query;
                        $out['error'] ++;
                        break;
                    case 1 :
                        $out['message'][] = 'Inserted ' . $merchant . '_' . $data['ArtNumber'];
                        $out['success'] ++;
                        break;
                    default :
                        $out['message'][] = 'Replaced ' . $merchant . '_' . $data['ArtNumber'];
                        break;
                }
                //print_var( $datainsert );
                unset($data);
                unset($datainsert);
            }
            fclose($handle);
        }

        return $out;
    }

}

register_activation_hook(__FILE__, array('WordPress_Affiliate_Shop_Affilinet', 'activation'));
register_deactivation_hook(__FILE__, array('WordPress_Affiliate_Shop_Affilinet', 'deactivation'));
