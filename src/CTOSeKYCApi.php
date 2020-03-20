<?php

namespace MohdNazrul\CTOSEKYCLaravel;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;

class CTOSeKYCApi
{
    private $CTOS_EKYC_URL;
    private $CTOS_EKYC_CIPHER;
    private $CTOS_EKYC_API_KEY;
    private $CTOS_EKYC_CIPHER_TEXT;
    private $CTOS_EKYC_PACKAGE_NAME;
    private $MD5_KEY ;

    public function __construct($url, $cipher, $api_key, $cipher_text, $package_name, $md5_key)
    {
        $this->CTOS_EKYC_URL = $url;
        $this->CTOS_EKYC_CIPHER = $cipher;
        $this->CTOS_EKYC_API_KEY = $api_key;
        $this->CTOS_EKYC_CIPHER_TEXT = $cipher_text;
        $this->CTOS_EKYC_PACKAGE_NAME = $package_name;
        $this->MD5_KEY = $md5_key;
    }

    public function getToken($device_model = 'NA', $device_brand = 'NA', $device_imei = 'NA', $device_mac = 'NA')
    {

        $body = [
            "device_model" => $device_model,
            "device_brand" => $device_brand,
            "device_imei" => $device_imei,
            "api_key" => $this->API_KEY,
            "device_mac" => $device_mac,
            "package_name" => $this->PACKAGE_NAME,
        ];

        $bodyJSON = json_encode($body, true);
        $encrypted = openssl_encrypt($bodyJSON, $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);

        $dataBody = [
            'data' => base64_encode($encrypted),
            'api_key' => $this->API_KEY
        ];

        $dataBodyJSON = json_encode($dataBody);

        $httpClient = new Client();
        $response = $httpClient->post(
            $this->URL . 'v2/auth/get-token',
            [
                RequestOptions::BODY => $dataBodyJSON,
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        $resBody = $response->getBody()->getContents();
        $resArray = json_decode($resBody, true);
        if ($resArray['success']) {
            $output = openssl_decrypt(base64_decode($resArray['data']), $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);
            $outputArray = json_decode($output, true);
            $this->TOKEN = $outputArray['access_token'];
        } else {
            $output = openssl_decrypt(base64_decode($resArray['data']), $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);
            $outputArray = json_decode($output, true);
            $this->TOKEN = null;
        }

        return $outputArray;
    }

    public function step1_A_New_Applicant($ref_id, $ic_no, $dob, $nationality, $citizenship,
                                          $address, $fullName, $religion = 'NA', $gender, $place_of_birth,
                                          $product_name, $product_desc)
    {
        $this->REF_ID = $ref_id;

        if (!empty($this->TOKEN)) {
            $body = [
                "id_info" => [
                    "extra" => $this->security_signature_token($ref_id),
                    "documentNumber" => $ic_no,
                    "backDocumentNumber" => $ic_no,
                    "dateOfBirth" => $dob,
                    "nationality" => $nationality,
                    "citizenship" => $citizenship,
                    "address" => strtoupper($address),
                    "fullName" => strtoupper($fullName),
                    "religion" => $religion,
                    "gender" => $gender,
                    "placeOfBirth" => $place_of_birth
                ],
                "package_name" => $this->PACKAGE_NAME,
                "ref_id" => $ref_id,
                "extra" => $this->security_signature_token($ref_id),
                "date" => Carbon::now()->format('Y-m-d'),
                "request_time" => Carbon::now()->format('Y-m-d h:i:s'),
                "api_key" => $this->API_KEY,
                "product_info" => [
                    "product_name" => $product_name,
                    "product_desc" => $product_desc,
                    "applicant_registration_date" => Carbon::now()->format('Y-m-d h:i:s')
                ]
            ];

            $bodyJSON = json_encode($body, true);

            $encrypted = openssl_encrypt($bodyJSON, $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);

            $dataBody = [
                'data' => base64_encode($encrypted),
                'api_key' => $this->API_KEY
            ];

            $dataBodyJSON = json_encode($dataBody);

            $access_token = "access_token " . $this->TOKEN;


            $httpClient = new Client();
            $response = $httpClient->post(
                $this->URL . 'v2/bank/new-applicant',
                [
                    RequestOptions::BODY => $dataBodyJSON,
                    RequestOptions::HEADERS => [
                        'Authorization' => $access_token,
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            $resBody = $response->getBody()->getContents();

            $resArray = json_decode($resBody, true);

            if ($resArray['success']) {
                $output = openssl_decrypt(base64_decode($resArray['data']), $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);

                $outputArray = json_decode($output, true);
                $this->ONBOARDING_ID = $outputArray['onboarding_id'];
            } else {
                $output = openssl_decrypt(base64_decode($resArray['data']), $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);

                $outputArray = json_decode($output, true);
                $this->ONBOARDING_ID = null;
            }

            return $outputArray;
        } else {
            return [
                'status' => 'error',
                'message' => "please get the token first using call method getToken with params"
            ];

        }

    }

    public function step1_B_OCR_Scanner($card_type = 1, $image_type, $img_base_64)
    {
        if (!empty($this->ONBOARDING_ID)) {

            $body = ['ocr' =>
                [
                    "onboarding_id" => $this->ONBOARDING_ID,
                    "card_type" => $card_type,
                    "request_time" => Carbon::now()->format('Y-m-d h:i:s'),
                    "device_mac" => "NA",
                    "device_model" => "NA",
                    "platform" => "Web", // Android / Ios / Web
                    "api_key" => $this->API_KEY,
                    "id_image" => 'data:image/' . $image_type . ';base64,' . $img_base_64,
                    'api_key' => $this->API_KEY
                ]
            ];

            $bodyJSON = json_encode($body, true);

            $encrypted = openssl_encrypt($bodyJSON, $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);

            $dataBody = [
                'data' => base64_encode($encrypted),
                'api_key' => $this->API_KEY
            ];

            $dataBodyJSON = json_encode($dataBody, true);

            $access_token = "access_token " . $this->TOKEN;
            $httpClient = new Client();
            $response = $httpClient->post(
                $this->URL . 'v2/webservices/ocr-scanner',
                [
                    RequestOptions::BODY => $dataBodyJSON,
                    RequestOptions::HEADERS => [
                        'Authorization' => $access_token,
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            $resBody = $response->getBody()->getContents();

            $resArray = json_decode($resBody, true);

            if ($resArray['success']) {
                $output = openssl_decrypt(base64_decode($resArray['data']), $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);
                $outputArray = json_decode($output, true);
                $ocr_output = $outputArray['ocr_result'];
                if ($card_type == 1) {
                    $this->OCR_RESULT_1 = $ocr_output;
                } else {
                    $this->OCR_RESULT_2 = $ocr_output;
                }
            } else {
                $output = openssl_decrypt(base64_decode($resArray['data']), $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);
                $outputArray = json_decode($output, true);
                if ($card_type == 1) {
                    $this->OCR_RESULT_1 = null;
                } else {
                    $this->OCR_RESULT_2 = null;
                }
            }

            return $outputArray;

        } else {
            return [
                'status' => 'error',
                'message' => "please go to the step 1 - A - register new applicant with params"
            ];
        }

    }

    public function step1_C_Landmark($device_model, $device_brand, $device_mac, $device_imei, $platform, $billing_version = 1)
    {
        if (!empty($this->OCR_RESULT_1) && !empty($this->OCR_RESULT_2)) {
            $body = [
                "device_model" => $device_model,
                "device_brand" => $device_brand,
                "device_mac" => $device_mac,
                "package_name" => $this->PACKAGE_NAME,
                "device_imei" => $device_imei,
                "onboarding_id" => $this->ONBOARDING_ID,
                "extra" => $this->security_signature_token_landmark(),
                "api_key" => $this->API_KEY,
                "date" => Carbon::now()->format('Y-m-d'),
                "platform" => $platform,
                "request_time" => Carbon::now()->format('Y-m-d h:s:i'),
                "billing_version" => $billing_version,
            ];

            $bodyJSON = json_encode($body, true);
            $encrypted = openssl_encrypt($bodyJSON, $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);

            $dataBody = [
                'data' => base64_encode($encrypted),
                'api_key' => $this->API_KEY
            ];

            $dataBodyJSON = json_encode($dataBody, true);

            $access_token = "access_token " . $this->TOKEN;

            $httpClient = new Client();
            $response = $httpClient->post(
                $this->URL . 'v2/landmark/perform-landmark',
                [
                    RequestOptions::BODY => $dataBodyJSON,
                    RequestOptions::HEADERS => [
                        'Authorization' => $access_token,
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            $resBody = $response->getBody()->getContents();
            $resArray = json_decode($resBody, true);

            if ($resArray['success']) {
                $output = openssl_decrypt(base64_decode($resArray['data']), $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);
                $outputArray = json_decode($output, true);
                $overall_Score = $outputArray['overall_Score'];
                $this->OVERALL_SCORE = $overall_Score;
            } else {
                $output = openssl_decrypt(base64_decode($resArray['data']), $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);
                $outputArray = json_decode($output, true);
                $this->OVERALL_SCORE = null;
            }
            return $outputArray;

        } else {
            return [
                'status' => 'error',
                'message' => "please go to the step 1 - B - step1_B_OCR_Scanner with params"
            ];
        }
    }

    public function step1_D_Save_Data($ic_no, $dob, $nationality, $citizenship, $address, $full_name, $religion = 'NA', $gender, $place_of_birth,
                                      $device_imei, $device_mac, $product_name, $product_desc)
    {
        if (!empty($this->OVERALL_SCORE)) {
            $body = [
                "id_info" => [
                    "extra" => $this->security_signature_token_landmark(),
                    "documentNumber" => $ic_no,
                    "backDocumentNumber" => $ic_no,
                    "dateOfBirth" => $dob,
                    "nationality" => $nationality,
                    "citizenship" => $citizenship,
                    "address" => strtoupper($address),
                    "fullName" => strtoupper($full_name),
                    "religion" => $religion,
                    "gender" => $gender,
                    "placeOfBirth" => $place_of_birth
                ],
                "package_name" => $this->PACKAGE_NAME,
                "api_key" => $this->API_KEY,
                "onboarding_id" => $this->ONBOARDING_ID,
                "device_imei" => $device_imei,
                "device_mac" => $device_mac,
                "extra" => $this->security_signature_token_landmark(),
                "request_time" => Carbon::now()->format('Y-m-d h:i:s'),
                "date" => Carbon::now()->format('Y-m-d'),
                "product_info" => [
                    "product_name" => $product_name,
                    "product_desc" => $product_desc,
                    "applicant_registration_date" => Carbon::now()->format('Y-m-d h:i:s')
                ]
            ];

            $bodyJSON = json_encode($body, true);

            $encrypted = openssl_encrypt($bodyJSON, $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);

            $dataBody = [
                'data' => base64_encode($encrypted),
                'api_key' => $this->API_KEY
            ];

            $dataBodyJSON = json_encode($dataBody);
            $access_token = "access_token " . $this->TOKEN;
            $httpClient = new Client();
            $response = $httpClient->post(
                $this->URL . 'v2/webservices/save-data',
                [
                    RequestOptions::BODY => $dataBodyJSON,
                    RequestOptions::HEADERS => [
                        'Authorization' => $access_token,
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            $resBody = $response->getBody()->getContents();

            $resArray = json_decode($resBody, true);

            if ($resArray['success']) {
                $output = openssl_decrypt(base64_decode($resArray['data']), $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);
                $outputArray = json_decode($output, true);
                $this->TEXT_SIMILARITY_RESULT = $outputArray['text_similarity_result'];
            } else {
                $output = openssl_decrypt(base64_decode($resArray['data']), $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);
                $outputArray = json_decode($output, true);
                $this->TEXT_SIMILARITY_RESULT = null;
            }

            return $outputArray;

        } else {
            return [
                'status' => 'error',
                'message' => "please go to the step 1 - C - Do the landmark for scoring front and back ic with params"
            ];
        }
    }

    public function step2_A_Liveness($video_type, $video_base_64)
    {

        if (!empty($this->TEXT_SIMILARITY_RESULT)) {
            $body = ["data" =>
                [
                    "api_key" => $this->API_KEY,
                    "onboarding_id" => $this->ONBOARDING_ID,
                    "request_time" => Carbon::now()->format('Y-m-d h:i:s'),
                    "device_model" => "NA",
                    "device_brand" => "NA",
                    "device_mac" => "NA",
                    "platform" => "Web",
                    "video" => "data:video/" . $video_type . ";base64," . $video_base_64,
                ]
            ];

            $bodyJSON = json_encode($body, true);

            $encrypted = openssl_encrypt($bodyJSON, $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);

            $dataBody = [
                'data' => base64_encode($encrypted),
                'api_key' => $this->API_KEY
            ];

            $dataBodyJSON = json_encode($dataBody);

            $access_token = "access_token " . $this->TOKEN;

            $httpClient = new Client();
            $response = $httpClient->post(
                $this->URL . 'v2/webservices/liveness',
                [
                    RequestOptions::BODY => $dataBodyJSON,
                    RequestOptions::HEADERS => [
                        'Authorization' => $access_token,
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            $resBody = $response->getBody()->getContents();

            $resArray = json_decode($resBody, true);

            if ($resArray['success']) {
                $output = openssl_decrypt(base64_decode($resArray['data']), $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);
                $outputArray = json_decode($output, true);
            } else {
                $output = openssl_decrypt(base64_decode($resArray['data']), $this->CIPHER, $this->CIPHER_TEXT . $this->API_KEY, OPENSSL_RAW_DATA, $this->CIPHER_TEXT);
                $outputArray = json_decode($output, true);
            }
            return $outputArray;
        } else {
            return [
                'status' => 'error',
                'message' => "please go to the step 1 - D - save data with params"
            ];
        }
    }

    private function security_signature_token($ref_id)
    {
        $api_key = $this->API_KEY;
        $date = Carbon::now()->format('Y-m-d');
        $package_name = $this->PACKAGE_NAME;
        $md5Key = $this->MD5_KEY;
        $strSecurity = $api_key . $ref_id . $md5Key . $date . $package_name;
        $md5_sst = base64_encode(md5($strSecurity));
        return $md5_sst;
    }

    private function security_signature_token_landmark($device_mac = 'NA', $device_imei = 'NA')
    {
        $api_key = $this->API_KEY;
        $date = Carbon::now()->format('Y-m-d');
        $onboarding_id = $this->ONBOARDING_ID;
        $package_name = $this->PACKAGE_NAME;
        $md5Key = $this->MD5_KEY;
        $strSecurity = $api_key . $device_mac . $onboarding_id . $md5Key . $date . $device_imei . $package_name;
        $md5_sst = base64_encode(md5($strSecurity));
        return $md5_sst;

    }


}