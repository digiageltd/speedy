<?php


namespace Digiageltd\Speedy;
use Digiageltd\Speedy\Configs as config;


class Connect
{
    public function sendRequest($apiURL, $jsonInputData)
    {
        $jsonData = ['userName' => config::$apiUser, 'password' => config::$apiPass, 'language' => config::$language];
        if ($jsonInputData != null) {
            foreach ($jsonInputData as $key => $value) {
                $jsonData[$key] = $value;
            }
        }
        $curl = curl_init(config::$apiBaseURL . $apiURL);
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
}