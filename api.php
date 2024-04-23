<?php

/**
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
define ( "GF_BAYARCASH_ROOT_URL",  "https://console.bayar.cash");

class GFBayarcashAPI
{
    private static ?self $_instance = null;
    private string $postalKey;
    private string $pat;

    private function __construct(string $fpxPortalKey, string $patKey)
    {
        $this->postalKey = $fpxPortalKey;
        $this->pat = $patKey;
    }

    public static function get_instance(string $fpxPortalKey, string $patKey): self
    {
        if (self::$_instance === null) {
            self::$_instance = new self($fpxPortalKey, $patKey);
        }

        return self::$_instance;
    }

    public static function verifyToken(string $patKey): bool
    {
        $args = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $patKey
            ],
        ];

        $response = wp_remote_post(GF_BAYARCASH_ROOT_URL . '/api/transactions', $args);

        if (is_wp_error($response)) {
            return false;
        }

        $httpStatus = wp_remote_retrieve_response_code($response);

        return $httpStatus === 200;
    }

    public function create_payment(array $postData): void
    {
        $redirectUrl = GF_BAYARCASH_ROOT_URL . '/transactions/add';
        $this->submitForm($redirectUrl, $postData);
    }

    private function submitForm(string $url, array $postData): void
    {
        echo '<form id="redirectForm" action="' . htmlspecialchars($url) . '" method="post">';
        foreach ($postData as $key => $value) {
            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
        }
        echo '</form>';
        echo '<script>document.getElementById("redirectForm").submit();</script>';
        exit;
    }

    public function get_payment(string $patKey, string $targetReturnUrl): array
    {
        $args = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $patKey
            ],
        ];

        $response = wp_remote_post(GF_BAYARCASH_ROOT_URL . '/api/transactions', $args);

        if (is_wp_error($response)) {
            return [
                'exchangeOrderNo' => null,
                'fpxAmount' => null,
                'fpxStatus' => null,
                'transactionStatusDescription' => null
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $decodedResponse = json_decode($body, true);

        $exchangeOrderNo = null;
        $fpxAmount = null;
        $fpxStatus = null;
        $transactionStatusDescription = null;

        foreach ($decodedResponse['output']['transactionsList']['recordsListData'] as $record) {
            if ($record['return_url'] === $targetReturnUrl) {
                $exchangeOrderNo = $record['exchange_order_no'];
                $fpxAmount = $record['amount'];
                $fpxStatus = $record['status'];
                $transactionStatusDescription = $record['status_description'];
                break;
            }
        }

        return [
            'exchangeOrderNo' => $exchangeOrderNo,
            'fpxAmount' => $fpxAmount,
            'fpxStatus' => $fpxStatus,
            'transactionStatusDescription' => $transactionStatusDescription
        ];
    }
}

// Handle AJAX request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['patKey'])) {
        $patKey = $data['patKey'];
        $tokenValid = GFBayarcashAPI::verifyToken($patKey);

        header('Content-Type: application/json');
        echo json_encode(['success' => $tokenValid]);
        exit;
    }
}
