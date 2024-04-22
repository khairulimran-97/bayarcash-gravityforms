<?php

/*
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

define("GF_BAYARCASH_ROOT_URL", "https://console.bayarcash.dev");

class GFBayarcashAPI
{
    private static $_instance;

    public static function get_instance($fpx_portal_key, $pat_key): GFBayarcashAPI
    {
        if (self::$_instance == null) {
            self::$_instance = new self($fpx_portal_key, $pat_key);
        }

        return self::$_instance;
    }

    public function __construct($fpx_portal_key, $pat_key)
    {
        $this->postal_key = $fpx_portal_key;
        $this->pat = $pat_key;
    }

    public function create_payment($postData): void
    {
    $redirectUrl = GF_BAYARCASH_ROOT_URL . '/transactions/add';

    $this->submitForm($redirectUrl, $postData);
    }

    private function submitForm($url, $postData)
    {
    echo '<form id="redirectForm" action="' . htmlspecialchars($url) . '" method="post">';
    foreach ($postData as $key => $value) {
        echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
    }
    echo '</form>';
    echo '<script>document.getElementById("redirectForm").submit();</script>';
    exit;
    }

    public function get_payment($pat_key, $target_return_url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://console.bayarcash.dev/api/transactions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                "Authorization: Bearer $pat_key"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response, true);

        $target_record_id = null;
        $exchange_order_no = null;
        $fpx_amount = null;
        $fpx_status = null;
        $transaction_status_description = null;

        foreach ($response['output']['transactionsList']['recordsListData'] as $record) {
            if ($record['return_url'] === $target_return_url) {
                $target_record_id = $record['id'];
                break;
            }
        }

        if ($target_record_id !== null) {
            foreach ( $response['output']['transactionsList']['recordsListData'] as $record ) {
                if ( $record['id'] === $target_record_id ) {
                    $exchange_order_no = $record['exchange_order_no'];
                    $fpx_amount = $record['amount'];
                    $fpx_status = $record['status'];
                    $transaction_status_description = $record['status_description'];
                    break;
                }
            }}

        // Returning relevant data
        return array(
            'exchange_order_no' => $exchange_order_no,
            'fpx_amount' => $fpx_amount,
            'fpx_status' => $fpx_status,
            'transaction_status_description' => $transaction_status_description
        );
    }

}
