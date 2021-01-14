<?php


namespace Digiageltd\Speedy;
use Digiageltd\Speedy\Configs as configs;

class Speedy extends Connect
{
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

    public function createShipmentRequest($recipient, $order_id, $cashOnDelivery = null) {
        //1
        $sender = configs::serviceDetails();
        //3
        $serviceDetails = configs::serviceDetails();
        //Cash on delivery
        if($cashOnDelivery != null) {
            $cashOnDelivery = [
                'amount' => $cashOnDelivery['amount'],
                'processingType' => 'CASH'
            ];
            $additionalServices = ['cod'=>$cashOnDelivery];
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

}