<?php

namespace Tamaranga\Tools;

class GeoIP
{

    private static $instance = null;

    private $_SxGeo = null;

    private $_getDataIP = null;

    protected function __construct() {
        include_once("geoip/sypexgeo/SxGeo.php");
        $this->_SxGeo = new \SxGeo(dirname(__FILE__) . '/geoip/sypexgeo/SxGeoCity.dat');
    }

    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
    
    public static function getInstance(): GeoIP
    {
        if ( ! isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public static function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    private function getSypex($ip)
    {
        $this->_getDataIP = null;
        $data = $this->_SxGeo->getCityFull($ip);
        if ( ! empty($data)) {
            $this->_getDataIP = [
                'country_iso' => mb_strtolower($data['country']['iso']),
                'country_title' => $data['country']['name_en'],
                'lat' => $data['city']['lat'],
                'lon' => $data['city']['lon'],
            ];
        }
        unset($data);
        return $this;
    }

    private function getIPGeolocationAPI($ip)
    {
        if ( ! empty($this->_getDataIP)) {
            return $this;
        }
 
        $url = "https://api.ipgeolocationapi.com/geolocate/$ip";
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        $response = curl_exec($ch);
        $data = json_decode($response, TRUE);
        if ( ! empty($data)) {
            $this->_getDataIP = [
                'country_iso' => mb_strtolower($data['alpha2']),
                'country_title' => $data['name'],
                'lat' => $data['geo']['latitude'],
                'lon' => $data['geo']['longitude'],
            ];
            unset($data);
            return $this;
        }

        return null;
    }

    public function getDataIP(string $ip = '')
    {
        if (empty($ip)) {
            $ip = static::getClientIp();
        }

        if ( ! empty($this->getSypex($ip)->getIPGeolocationAPI($ip))) {
            return $this->_getDataIP;
        }

        return null;
    }
}
