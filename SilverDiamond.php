<?php
namespace SilverDiamond;

use \Curl\Curl;

class InvalidRequestException extends \Exception {}

class Language {
    const SPANISH = 'es';
    const ENGLISH = 'en';
    const GERMAN = 'de';
    const FRENCH = 'fr';
    const PORTUGUESE = 'pt';
    const ITALIAN = 'it';
    const DUTCH = 'nl';
    const POLISH = 'pl';
    const RUSSIAN = 'ru';
}

class Sentiment {
    const VERY_POSITIVE = 'Very positive';
    const POSITIVE = 'Positive';
    const NEUTRAL = 'Neutral';
    const NEGATIVE = 'Negative';
    const VERY_NEGATIVE = 'Very negative';
}

class Gender {
    const MALE = 'male';
    const FEMALE = 'female';
    const UNKNOWN = 'unknown';
}

class Api {
    private static $ENDPOINT = 'https://api.silverdiamond.io/v1/service/';
    private $key;
    private $curl;

    public function __construct ($key) {
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
            throw new InvalidRequestException($this->curl->error);
        }

        if (!is_string($this->curl->response)) {
            $response = json_decode(json_encode($this->curl->response), true);
        } else {
            $response = json_decode($this->curl->response, true);
        }

        if (!$response) {
            throw new InvalidRequestException('Unknown error');
        }

        if (isset($response['message'])) {
            throw new InvalidRequestException($response['message']);
        }

        if (isset($response['error'])) {
            throw new InvalidRequestException($response['error']);
        }

        return $response;
    }
}

class SilverDiamond {
    private $instance = null;

    public function __construct ($key) {
        $this->instance = new Api($key);
    }

    /**
     * Returns the iso code of the detected `$text` language
     *
     * @param string $text
     * @return Language
     */
    public function detectLanguage ($text) {
        $text = $this->_normalizeText($text);

        $response = $this->instance->request('language-detection', [
            'text' => $text
        ]);

        if (!isset($response['language'])) {
            throw new InvalidRequestException('Unknown error');
        }

        return mb_strtolower($response['language']);
    }

    /**
     * Returns true if the discovered language is included in `$isoCodes`
     *
     * @param string $text
     * @param string|array $isoCodes
     * @return boolean
     */
    public function languageIs ($text, $isoCodes) {
        $language = $this->detectLanguage($text);
        if (!is_array($isoCodes)) {
            $isoCodes = [$isoCodes];
        }

        $isoCodes = array_map('mb_strtolower', $isoCodes);
        return in_array($language, $isoCodes);
    }

    /**
     * Returns true if the discovered language is Spanish
     *
     * @param string $text
     * @return boolean
     */
    public function languageIsSpanish ($text) {
        return $this->languageIs($text, [Language::SPANISH]);
    }

    /**
     * Returns true if the discovered language is English
     *
     * @param string $text
     * @return boolean
     */
    public function languageIsEnglish ($text) {
        return $this->languageIs($text, [Language::ENGLISH]);
    }

    /**
     * Returns true if the discovered language is German
     *
     * @param string $text
     * @return boolean
     */
    public function languageIsGerman ($text) {
        return $this->languageIs($text, [Language::GERMAN]);
    }

    /**
     * Returns true if the discovered language is French
     *
     * @param string $text
     * @return boolean
     */
    public function languageIsFrench ($text) {
        return $this->languageIs($text, [Language::FRENCH]);
    }

    /**
     * Returns true if the discovered language is Portuguese
     *
     * @param string $text
     * @return boolean
     */
    public function languageIsPortuguese ($text) {
        return $this->languageIs($text, [Language::PORTUGUESE]);
    }

    /**
     * Returns true if the discovered language is Italian
     *
     * @param string $text
     * @return boolean
     */
    public function languageIsItalian ($text) {
        return $this->languageIs($text, [Language::ITALIAN]);
    }

    /**
     * Returns true if the discovered language is Dutch
     *
     * @param string $text
     * @return boolean
     */
    public function languageIsDutch ($text) {
        return $this->languageIs($text, [Language::DUTCH]);
    }

    /**
     * Returns true if the discovered language is Polish
     *
     * @param string $text
     * @return boolean
     */
    public function languageIsPolish ($text) {
        return $this->languageIs($text, [Language::POLISH]);
    }

    /**
     * Returns true if the discovered language is Russian
     *
     * @param string $text
     * @return boolean
     */
    public function languageIsRussian ($text) {
        return $this->languageIs($text, [Language::RUSSIAN]);
    }

    /**
     * Returns the overall sentiment detected in `$text`
     *
     * @param string $text
     * @return Sentiment
     */
    public function detectSentiment ($text) {
        $text = $this->_normalizeText($text);

        $response = $this->instance->request('sentiment-analysis', [
            'text' => $text
        ]);

        if (!isset($response['overall'])) {
            throw new InvalidRequestException('Unknown error');
        }

        return $response['overall'];
    }


    /**
     * Returns true if the text sentiment is included in `$sentiments`
     *
     * @param string $text
     * @param string|array $sentiments
     * @return boolean
     */
    public function sentimentIs ($text, $sentiments) {
        $sentiment = mb_strtolower($this->detectSentiment($text));

        if (!is_array($sentiments)) {
            $sentiments = [$sentiments];
        }

        $sentiments = array_map('mb_strtolower', $sentiments);
        return in_array($sentiment, $sentiments);
    }

    /**
     * Returns true if the text sentiment is classified as *Positive* or *Very positive*
     *
     * @param string $text
     * @return boolean
     */
    public function sentimentIsPositive ($text) {
        return $this->sentimentIs($text, [Sentiment::POSITIVE, Sentiment::VERY_POSITIVE]);
    }

    /**
     * Returns true if the text sentiment is classified as *Neutral*
     *
     * @param string $text
     * @return boolean
     */
    public function sentimentIsNeutral ($text) {
        return $this->sentimentIs($text, [Sentiment::NEUTRAL]);
    }

    /**
     * Returns true if the text sentiment is classified as *Negative* or *Very negative*
     *
     * @param string $text
     * @return boolean
     */
    public function sentimentIsNegative ($text) {
        return $this->sentimentIs($text, [Sentiment::NEGATIVE, Sentiment::VERY_NEGATIVE]);
    }

    /**
     * Performs a spam detection API call and returns the response
     *
     * @param string $text
     * @param string $ip
     * @return array
     */
    public function spam ($text, $ip = null) {
        $data = [
            'text' => $text
        ];
        if ($ip) {
            if (!is_string($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
                throw new InvalidRequestException('Invalid IP Address');
            }

            $data['ip'] = $ip;
        }

        $response = $this->instance->request('spam-detection', $data);

        if (!isset($response['spam']) || !isset($response['ham'])) {
            throw new InvalidRequestException('Unknown error');
        }

        return $response;
    }

    /**
     * Returns true if the provided text [and the IP Address] are classified as SPAM
     *
     * @param string $text
     * @param string $ip
     * @return boolean
     */
    public function isSpam ($text, $ip = null) {
        $text = $this->_normalizeText($text);
        $spam = $this->spam($text, $ip);
        return $spam['spam'] === true;
    }

    /**
     * Returns true if the provided text [and the IP Address] are classified as HAM
     *
     * @param string $text
     * @param string $ip
     * @return boolean
     */
    public function isHam ($text, $ip = null) {
        $text = $this->_normalizeText($text);
        $spam = $this->spam($text, $ip);
        return $spam['ham'] === true;
    }



    /**
     * Returns the spam score of a certain text [and IP Address] between 0 and 10. Higher means more spam probability
     *
     * @param string $text
     * @param string $ip
     * @return integer
     */
    public function spamScore ($text, $ip = null) {
        $text = $this->_normalizeText($text);
        $spam = $this->spam($text, $ip);
        return $spam['spamScore'];
    }

    /**
     * Returns the similarity of two texts between 0 and 1. Higher means more similar
     *
     * @param string $text1
     * @param string $text2
     * @return float
     */
    public function similarity ($text1, $text2) {
        $text1 = $this->_normalizeText($text1);
        $text2 = $this->_normalizeText($text2);
        $data = [
            'texts' => [$text1, $text2]
        ];

        $response = $this->instance->request('short-text-similarity', $data);

        if (!isset($response['similarity'])) {
            throw new InvalidRequestException('Unknown error');
        }

        return $response['similarity'];
    }

    /**
     * Returns the most likely gender from a name and, optionally, a country code
     *
     * @param string $name
     * @param string $countryCode
     * @return string
     */
    public function gender ($name, $countryCode = null) {
        $name = $this->_normalizeText($name);
        $data = [
            'name' => $name
        ];
        if (isset($countryCode)) {
            $data['country'] = $countryCode;
        }

        $response = $this->instance->request('gender-detection', $data);
        if (!isset($response['gender'])) {
            throw new InvalidRequestException('Unknown error');
        }

        if (mb_strtolower($response['gender']) === Gender::MALE) {
            return Gender::MALE;
        }

        if (mb_strtolower($response['gender']) === Gender::MALE) {
            return Gender::FEMALE;
        }

        return Gender::UNKNOWN;
    }

    /**
     * Returns true if the predicted gender is Male
     *
     * @param string $name
     * @param string $countryCode
     * @return boolean
     */
    public function genderIsMale ($name, $countryCode = null) {
        $gender = $this->gender($name, $countryCode);
        return $gender === Gender::Male;
    }

    /**
     * Returns true if the predicted gender is Female
     *
     * @param string $name
     * @param string $countryCode
     * @return boolean
     */
    public function genderIsFemale ($name, $countryCode = null) {
        $gender = $this->gender($name, $countryCode);
        return $gender === Gender::Female;
    }

    /**
     * Returns a list of keywords extracted from the text
     *
     * @param string $text
     * @return array
     */
    public function textRankKeywords ($text) {
        $text = $this->_normalizeText($text);
        $data = [
            'text' => $text
        ];
        $response = $this->instance->request('text-rank-keywords', $data);
        if (!isset($response['keywords']) || !is_array($response['keywords'])) {
            throw new InvalidRequestException('Unknown error');
        }

        return $response['keywords'];
    }

    /**
     * Returns a list of keywords extracted from the text
     *
     * @param string $text
     * @return array
     */
    public function textRankSummary ($text) {
        $text = $this->_normalizeText($text);
        $data = [
            'text' => $text
        ];
        $response = $this->instance->request('text-rank-summary', $data);
        if (!isset($response['summary'])) {
            throw new InvalidRequestException('Unknown error');
        }

        return $response['summary'];
    }


    /**
     * Checks if the text is not empty and removes the trailing and leading spaces
     *
     * @param string $text
     * @return string
     */
    private function _normalizeText ($text) {
        if (!is_string($text)) {
            throw new InvalidRequestException('Text must be a string');
        }

        $text = trim($text);
        if (empty($text)) {
            throw new InvalidRequestException('Text must not be empty');
        }
        return $text;
    }
}
