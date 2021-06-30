<?php

namespace App\TDR\FOSAPI;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Client
{
    /**
     * @param $photo_id
     * @return Response
     */
    public function rotatePhoto($photo_id)
    {
        // Make request to FOS api to trigger the rotation
        $url = getenv('FOS_API_URL').'/api/mobile/photo/'.$photo_id.'/rotate';

        return Http::post($url, [
            'token' => getenv('FOS_API_TOKEN')
        ]);
    }

    /**
     * @param $download_id
     * @return Response
     */
    public function pushDownloadToQueue($download_id)
    {
        $url = getenv('FOS_API_URL').'/api/mobile/download/'.$download_id.'/queue';

        return Http::post($url, [
            'token' => getenv('FOS_API_TOKEN')
        ]);
    }

    /**
     * @param $photo_id
     * @param $operations
     * @return Response
     */
    public function saveEditorState($photo_id, $operations)
    {
        $url = getenv('FOS_API_URL').'/api/mobile/photo/'.$photo_id.'/save-editor';

        return Http::post($url, [
            'token' => getenv('FOS_API_TOKEN'),
            'operations' => $operations,
        ]);
    }

    /**
     * @param $customer_id
     * @return Response
     */
    public function getCustomerAddresses($customer_id)
    {
        $url = getenv('FOS_API_URL').'/api/mobile/customer/'.$customer_id.'/get-addresses';

        // TODO: This should be a get request
        // TODO: This token should be in the header, not as a post var
        return Http::post($url, [
            'token' => getenv('FOS_API_TOKEN')
        ]);
    }

    /**
     * @param $customer_id
     * @param array $addresses
     * @return Response
     */
    public function updateCustomerAddresses($customer_id, $addresses = [])
    {
        $url = getenv('FOS_API_URL').'/api/mobile/customer/'.$customer_id.'/update-addresses';

        return Http::post($url, [
            'token'     => getenv('FOS_API_TOKEN'),
            'addresses' => $addresses
        ]);
    }

    /**
     * @param $customer_id
     * @param $shipping_address
     * @param $billing_address
     * @param $return_shipping
     * @param $line_items
     * @return Response
     */
    public function createOrder($customer_id, $shipping_address, $billing_address, $return_shipping, $line_items)
    {
        $url = getenv('FOS_API_URL').'/api/mobile/order';

        return Http::post($url, [
            'token'             => getenv('FOS_API_TOKEN'),
            'customer_id'       => $customer_id,
            'shipping_address'  => $shipping_address,
            'billing_address'   => $billing_address,
            'return_shipping'   => $return_shipping,
            'line_items'        => $line_items
        ]);
    }

    /**
     * @return Response
     */
    public function getProducts()
    {
        $url = getenv('FOS_API_URL').'/api/mobile/woo-commerce-products';

        return Http::post($url, [
            'token' => getenv('FOS_API_TOKEN')
        ]);
    }
}
