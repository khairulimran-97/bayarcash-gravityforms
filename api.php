<?php

/*
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

define("GF_BAYARCASH_ROOT_URL", "https://console.bayarcash.dev");

class GFBayarcashAPI
{
    private static $_instance;

    public static function get_instance($fpx_portal_key, $pat_key)
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

    public function create_payment($postData)
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




    
}
