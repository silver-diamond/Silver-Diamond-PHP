<?php
namespace SilverDiamond;

use \Curl\Curl;

class Api {
    private static $ENDPOINT = 'http://silverdiamond.us-east-2.elasticbeanstalk.com/api/service/';
    private $key;
    private $curl;

    public function __construct (string $key) {
        $this->key = $key;
        $this->curl = new Curl();
        $this->curl->setHeader('Authorization', 'Bearer ' . $this->key);
        $this->curl->setHeader('Content-Type', 'application/json');
        $this->curl->setHeader('Accept', 'application/json');
        $this->curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
    }

    public function request ($endpoint, $data) {
        $endpoint = trim($endpoint, '/');
        $this->curl->post(self::$ENDPOINT . $endpoint, $data);
        if ($this->curl->error) {
            throw new Exception($this->curl->error);
        }

        $response = json_decode($this->curl->response, true);
        if (!$response) {
            throw new Exception('Unknown error');
        }

        if (isset($response['message'])) {
            throw new Exception($response['message']);
        }

        if (isset($response['error'])) {
            throw new Exception($response['error']);
        }

        return $response;
    }
}

class Exception extends \Exception {}

class SilverDiamond {
    private static $instance = null;

    public static function init (string $key) {
        die('hola');
        self::$instance = new Api($key);
    }

    /**
     * Returns true if the discovered language is equals to $isoCode
     *
     * @param string $isoCode
     * @return void
     */
    public static function detectLanguage (string $text) {
        $text = trim($text);
        if (empty($text)) {
            throw new Exception('Text must not be empty');
        }

        $response = self::$instance->request('language-detection', [
            'text' => $text
        ]);

        if (!isset($response['language'])) {
            throw new Exception('Unknown error');
        }

        return mb_strtolower($response['language']);
    }

    /**
     * Returns true if the discovered language is equals to $isoCode
     *
     * @param string $isoCode
     * @return void
     */
    public static function languageIs (string $text, string $isoCode) {
        $language = self::detectLanguage($text);
        $isoCode = mb_strtolower($isoCode);
        return $language === $isoCode;
    }
}
