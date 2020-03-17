<?php
/**
 * API Library for CTOS Version 2.
 * User: Mohd Nazrul Bin Mustaffa
 * Date: 26/04/2018
 * Time: 11:16 AM
 */

namespace MohdNazrul\CTOSV2Laravel;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class CTOSeKYCApi
{
    private $CTOS_EKYC_URL;
    private $CTOS_EKYC_CIPHER;
    private $CTOS_EKYC_API_KEY;
    private $CTOS_EKYC_CIPHER_TEXT;
    private $CTOS_EKYC_PACKAGE_NAME;


    public function __construct($url, $cipher, $api_key, $cipher_text, $package_name)
    {
        $this->CTOS_EKYC_URL = $url;
        $this->CTOS_EKYC_CIPHER = $cipher;
        $this->CTOS_EKYC_API_KEY = $api_key;
        $this->CTOS_EKYC_CIPHER_TEXT = $cipher_text;
        $this->CTOS_EKYC_PACKAGE_NAME = $package_name;
    }



}