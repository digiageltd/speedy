<?php

namespace Digiageltd\Speedy;

use Illuminate\Support\Facades\Log;

class SpeedyController
{
    public static $sender;
    public static $apiUser;
    public static $apiPass;
    public static $apiBaseURL = 'https://api.speedy.bg/v1/';
    public static $language = 'BG';

    public function writeCredentials($apiCredentials, $sender) {
        self::$sender = $sender;
        self::$apiUser = $apiCredentials['apiUser'];
        self::$apiPass = $apiCredentials['apiPass'];

        return $apiCredentials['apiUser'];
    }

    public static function apiCredentials() {
        return ['apiUser'=>self::$apiUser, 'apiPass'=>self::$apiPass, 'apiBaseURL'=>self::$apiBaseURL, 'apiLang' => self::$language];
    }

    public function sendRequest($apiURL, $jsonInputData)
    {
        $jsonData = ['userName' => self::$apiUser, 'password' => self::$apiPass, 'language' => self::$language];
        if ($jsonInputData != null) {
            foreach ($jsonInputData as $key => $value) {
                $jsonData[$key] = $value;
            }
        }
        $curl = curl_init(self::$apiBaseURL . $apiURL);
        $jsonDataEncoded = json_encode($jsonData);
        #-> Set curl options
        curl_setopt($curl, CURLOPT_POST, 1); // Tell cURL that we want to send a POST request.
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Verify the peer's SSL certificate.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Stop showing results on the screen.
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5); // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Set the content type to application/json
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonDataEncoded); // Attach our encoded JSON string to the POST fields.

        #-> Get the response
        $jsonResponse = curl_exec($curl);

        if ($jsonResponse === FALSE) {
            exit("cURL Error: " . curl_error($curl));
        }
        return ($jsonResponse);
    }

    public function getCity($string, $criteria = null) {
        $jsonData['countryId'] = 100;
        switch ($criteria) {
            case 'postal_code':
                $jsonData['postCode'] = $string;
                break;
            case 'region':
                $jsonData['region'] = $string;
                break;
            default:
                $jsonData['name'] = $string;
        }
        $sendRequest = $this->sendRequest('location/site/', $jsonData);
        $result = json_decode($sendRequest, true);
        $result = $result['sites'];
        $data = [];
        for($i=0;$i<=count($result);$i++) {
            if (isset($result[$i])) {
                $data[] = [
                    'id'=>$result[$i]['id'],
                    'name' => $result[$i]['type'] . ' ' .$result[$i]['name'] . ', ' .$result[$i]['region']. ' - П.К. ' .$result[$i]['postCode']
                ];
            }
        }
        return json_encode($data);
    }

    public function getStreet($cityID, $string) {
        $jsonData = [
            'siteId' => $cityID,
            'name' => $string
        ];
        $sendRequest = $this->sendRequest('location/street/', $jsonData);
        $result = json_decode($sendRequest, true);
        $result = $result["streets"];
        $data = [];
        for($i=0;$i<=count($result);$i++) {
            if (isset($result[$i])) {
                $data[] = [
                    'id'=>$result[$i]['id'],
                    'name' => $result[$i]['type'] . ' ' .$result[$i]['name']
                ];
            }
        }
        return json_encode($data);
    }

    public function getDistrict($cityID, $string) {
        $jsonData = [
            'siteId' => $cityID,
            'name' => $string
        ];
        $sendRequest = $this->sendRequest('location/complex/', $jsonData);
        $result = json_decode($sendRequest, true);
        $result = $result['complexes'];
        $data = [];
        for($i=0;$i<=count($result);$i++) {
            if (isset($result[$i])) {
                $data[] = [
                    'id'=>$result[$i]['id'],
                    'name' => $result[$i]['type'] . ' ' .$result[$i]['name']
                ];
            }
        }

        return json_encode($data);
    }

    public function getOffice($cityID, $string = null) {
        $jsonData['siteId'] = $cityID;
        if($string != null) {
            $jsonData['name'] = $string;
        }
        $sendRequest = $this->sendRequest('location/office/', $jsonData);
        $result = json_decode($sendRequest, true);
        $result = $result['offices'];
        $data = [];
        for($i=0;$i<=count($result);$i++) {
            if (isset($result[$i])) {
                $streetName = (isset($result[$i]['address']['streetName']) ? ' - ' .$result[$i]['address']['streetName'] : '');
                $data[] = [
                    'id'=>$result[$i]['id'],
                    'name' => $result[$i]['type'] . ' ' .$result[$i]['name'] . $streetName
                ];
            }
        }
        return json_encode($data);
    }

    public function destinationService($sender, $recipient) {
        $jsonData = array(
            'date' => date('Y-m-d'),
            'sender' => $sender, // You can skip the sender data. In this case the sender will be the default one for the username with all the address and contact information.
            'recipient' => $recipient
        );
        $sendRequest = $this->sendRequest('services/destination', $jsonData);
        return json_decode($sendRequest, true);
    }

    public function getClientRequest() {
        $sendRequest = $this->sendRequest('client/contract/', null);
        $sendRequest = json_decode($sendRequest, true);

        return $sendRequest;
    }

    public function createShipmentRequest($recipient, $order_id, $additionalServices = null) {
        //1
        $sender = self::serviceDetails();
        Log::info($sender);
        //3
        $serviceDetails = self::serviceDetails();
        //Cash on delivery
        if($additionalServices != null) {
            /*$cashOnDelivery = [
                'amount' => $cashOnDelivery['amount'],
                'processingType' => 'CASH'
            ];
            $additionalServices = ['cod'=>$cashOnDelivery];*/
            $serviceDetails['additionalServices'] = $additionalServices;
        }
        //4
        $content = [
            'parcelsCount' => 1,
            'contents' => 'Козметика',
            'package' => 'BOX',
            'totalWeight' => 0.6
        ];
        //5
        $payment = [
            'courierServicePayer' => 'RECIPIENT',
            'declaredValuePayer' => 'RECIPIENT'
        ];

        $jsonData = array(
            'sender' => $sender, // You can skip the sender data. In this case the sender will be the default one for the username with all the address and contact information.
            'recipient' => $recipient,
            'service' => $serviceDetails,
            'content' => $content,
            'payment' => $payment,
            'ref1' => $order_id
        );

        $jsonResponse = $this->sendRequest('shipment/', $jsonData);
        $jsonResponse = json_decode($jsonResponse, true);

        return($jsonResponse);
    }

    public function printRequest($parcelID) {
        $parcelsArray = array(
            array('parcel' => array('id' => $parcelID)),
        );

        $jsonData = [
            'paperSize' => 'A4', // A4, A6, A4_4xA6
            'parcels' => $parcelsArray
        ];

        return $this->sendRequest('print/', $jsonData);
    }

    public static function serviceDetails() {
        return [
            'pickupDate' => date('Y-m-d'),
            'autoAdjustPickupDate' => true,
            'serviceId' => 505,
            'saturdayDelivery' => true
        ];
    }

}
