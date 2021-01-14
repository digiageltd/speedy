<?php


namespace Digiageltd\Speedy;


class Configs
{
    public static $sender;
    public static $apiUser;
    public static $apiPass;
    public static $apiBaseURL = 'https://api.speedy.bg/v1/';
    public static $language = 'BG';

    public function __construct($apiCredentials, $sender) {
        self::$sender = $sender;
        self::$apiUser = $apiCredentials['apiUser'];
        self::$apiPass = $apiCredentials['apiPass'];
    }

    public static function apiCredentials() {
        return ['apiUser'=>self::$apiUser, 'apiPass'=>self::$apiPass, 'apiBaseURL'=>self::$apiBaseURL, 'apiLang' => self::$language];
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