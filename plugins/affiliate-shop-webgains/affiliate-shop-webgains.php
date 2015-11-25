<?php

/*
  Plugin Name: Affiliate Shop &raquo; Webgains API
  Plugin URI:
  Description: Webgains API for WP Affiliate Shop
  Version: 1.0.0
  Author: 3 Door Digital
  Author URI: http://www.3doordigital.com
  License: GPL V3
 */

/**
 * Adds Webgains API to Affiliate Shop
 *
 *
 * @copyright  2015 3 Door Digital
 * @license    GPL v3
 * @version    Release: 1.0.0
 * @since      Class available since Release 1.0.0
 */
class WordPress_Affiliate_Shop_Webgains {

    static $options;
    private $option_name = 'wp_aff_webgains_merchants';
    private $merchants;

    public function __construct() {
        global $wp_aff;
        $this->option = $wp_aff->get_option();
        self::$options = $wp_aff->get_option();
        //echo $option_name;
        $this->merchants = get_option($this->option_name);

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
            $array['apis']['webgains'] = array(
                'name' => 'webgains',
                'nicename' => 'Webgains',
                'class' => 'WordPress_Affiliate_Shop_Webgains'
            );
        } else {
            $array = $wp_aff->get_option();
            $array['apis']['webgains'] = array(
                'name' => 'webgains',
                'nicename' => 'Webgains',
                'class' => 'WordPress_Affiliate_Shop_Webgains'
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
        unset($array['apis']['webgains']);
        update_option($wp_aff->option_name, $array);
    }

    /**
     * Returns array of merchants
     *
     * @return array	$array
     */
    public function merchants() {
        $array = array();

        $user = 'cyndylessing';
        $password = 'sweedy';
        $campaign = 71942;

        $wsdlUrl = 'http://ws.webgains.com/aws.php';
        $soapClient = new SoapClient($wsdlUrl, array(
            'login' => $user,
            'encoding' => 'UTF-8',
            'password' => $password,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            'soap_version' => SOAP_1_1));

        $error = 0;

        try {
            $programs = $soapClient->getProgramsWithMembershipStatus($user, $password, $campaign);
        } catch (SoapFault $fault) {
            $error = 1;
        }
        if ($error == 0) {
            //print_var( $programs );
            foreach ($programs as $program) {
                if ($program->programMembershipStatusName == 'Live' || $program->programMembershipStatusName == 'Joined') {
                    $array['ID-' . $program->programID] = array(
                        'ID' => (string) $program->programID,
                        'name' => (string) $program->programName,
                        'aff' => 'webgains',
                    );
                }
            }
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
    public function update_feed($merchant, $merch) {

        $out['changedIds'] = [];
        $upload_dir = wp_upload_dir();
        $user_dirname = $upload_dir['basedir'] . '/feed-data';
        if (!file_exists($user_dirname))
            wp_mkdir_p($user_dirname);

        $uc_local_file = $user_dirname . '/webgainsproducts.csv';
        $url = 'http://www.webgains.com/affiliates/datafeed.html?action=download&campaign=71942&programs=' . $merchant . '&categories=all&fields=extended&fieldIds=category_id,category_name,category_path,deeplink,description,image_url,last_updated,merchant_category,price,product_id,product_name,program_id,program_name,best_sellers,brand,Colour,currency,delivery_cost,delivery_period,Fabric,Full_merchant_price,gender,image_large_url,image_thumbnail_url,image_url,in_stock,normal_price,promotion_details,recommended_retail_price,related_product_ids,short_description,size,type,volume&format=csv&separator=comma&zipformat=none&stripNewlines=0&apikey=f04b19e18a7c601da209cee4036e4608';

        //$url = 'http://www.webgains.com/affiliates/datafeed.html?action=download&campaign=71942&programs='.$merchant.'&categories=all&fields=extended&fieldIds=deeplink,description,image_url,price,product_id,product_name,program_id,program_name,recommended_retail_price,Full_merchant_price&format=csv&separator=comma&zipformat=none&stripNewlines=1&apikey=f04b19e18a7c601da209cee4036e4608';
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
        //$contents = file_get_contents( $url );
        //echo $contents;
        $data = array();

        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '4096M');
        }
        $out['status'] = 1;
        /*
          $fp = gzopen( $local_file, "r");
          while ($line = gzread($fp,1024)) {
          fwrite($fp1, $line, strlen($line));
          }
          fclose( $fp1 );
          gzclose($fp);
         */
        if (($handle = fopen($uc_local_file, 'r')) !== false) {
            global $wpdb;
            // get the first row, which contains the column-titles (if necessary)
            $header = fgetcsv($handle, 0, ',');
            //print_var( $header );
            $out['status'] = 1;
            $i = 0;
            // loop through the file line-by-line
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                if ($data[8] != $data[11] && $data[8] != 0) {
                    set_time_limit(0);
                    $i ++;

                    if ($data[27] == 0 || $data[27] == '0.00' || $data[27] == '' || $data[27] == $data[11]) {
                        $rrp = $data[8];
                    } else {
                        $rrp = $data[27];
                    }

                    $table_name = $wpdb->prefix . "feed_data";

                    $datainsert = array(
                        'product_id' => $data[11] . '_' . $data[9],
                        'product_aff' => 'webgains',
                        'product_merch' => sanitize_text_field($data[11]),
                        'product_title' => sanitize_text_field($data[10]),
                        'product_brand' => sanitize_text_field($data[12]),
                        'product_image' => esc_url($data[5]),
                        'product_desc' => sanitize_text_field($data[4]),
                        'product_price' => $data[8],
                        'product_rrp' => $rrp,
                        'product_link' => esc_url($data[3]),
                    );
                    $replace = $wpdb->replace($table_name, $datainsert);

                    switch ($replace) {
                        case false :
                            //die( $wpdb->last_query );
                            $out['message'][] = $wpdb->last_query;
                            $out['error'] ++;
                            break;
                        case 1 :
                            $out['message'][] = 'Inserted ' . $merchant . '_' . $data['ID'];
                            $out['success'] ++;
                            break;
                        default :
                            $out['message'][] = 'Replaced ' . $merchant . '_' . $data['ID'];
                            break;
                    }
                }
                $out['changedIds'][] = $data[11] . '_' . $data[9];
                unset($data);
            }
            fclose($handle);
        }
        return $out;
    }

}

register_activation_hook(__FILE__, array('WordPress_Affiliate_Shop_Webgains', 'activation'));
register_deactivation_hook(__FILE__, array('WordPress_Affiliate_Shop_Webgains', 'deactivation'));
