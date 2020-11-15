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

class Readability {
    const VERY_EASY = 'Very Easy';
    const EASY = 'Easy';
    const FAIRLY_EASY = 'Fairly Easy';
    const STANDARD = 'Standard';
    const FAIRLY_DIFFICULT = 'Fairly Difficult';
    const DIFFICULT = 'Difficult';
    const VERY_DIFFICULT = 'Very Difficult';
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
    public function language ($text) {
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
        $language = $this->language($text);
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
    public function sentiment ($text) {
        $text = $this->_normalizeText($text);

        $response = $this->instance->request('sentiment-analysis', [
            'text' => $text
        ]);

        if (!isset($response['sentiment'])) {
            throw new InvalidRequestException('Unknown error');
        }

        return $response['sentiment'];
    }


    /**
     * Returns true if the text sentiment is included in `$sentiments`
     *
     * @param string $text
     * @param string|array $sentiments
     * @return boolean
     */
    public function sentimentIs ($text, $sentiments) {
        $sentiment = mb_strtolower($this->sentiment($text));

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
     * Translates $text into $targetLang, optionally specifying $sourceLang
     *
     * @param string $text
     * @param string $targetLang
     * @param string $sourceLang
     * @return string
     */
    public function translate ($text, $targetLang, $sourceLang = null) {
        $text = $this->_normalizeText($text);
        $data = [
            'text' => $text,
            'target_lang' => $targetLang
        ];
        if (isset($sourceLang)) {
            $data['source_lang'] = $sourceLang;
        }

        $response = $this->instance->request('translation', $data);
        if (!isset($response['translation'])) {
            throw new InvalidRequestException('Unknown error');
        }

        return $response['translation'];
    }

    /**
     * Returns the readability and readability scores of the provided $text written in $lang
     *
     * @param string $text
     * @param string $lang
     * @return Array
     */
    public function readability ($text, $lang = 'en') {
        $text = $this->_normalizeText($text);
        $data = [
            'text' => $text,
            'lang' => $lang
        ];

        $response = $this->instance->request('text-readability', $data);
        if (!isset($response['score']) || !isset($response['readability'])) {
            throw new InvalidRequestException('Unknown error');
        }

        return $response;
    }

    /**
     * Returns a `Readability` value for the provided $text written in $lang
     *
     * @param string $text
     * @param string $lang
     * @return string
     */
    public function readabilityCategory ($text, $lang = 'en') {
        $readability = $this->readability($text, $lang);
        return $readability['readability'];
    }

    /**
     * Returns the readability score for the provided $text written in $lang
     *
     * @param string $text
     * @param string $lang
     * @return float
     */
    public function readabilityScore ($text, $lang = 'en') {
        $readability = $this->readability($text, $lang);
        return $readability['score'];
    }

    /**
     * Returns true if the readability of the provided $text written in $lang is in $readabilities
     *
     * @param [type] $text
     * @param string $lang
     * @param array $readabilities
     * @return boolean
     */
    public function readabilityIs ($text, $lang = 'en', $readabilities = []) {
        $readability = $this->readabilityCategory($text, $lang);
        return in_array($readability, $readabilities);
    }

    /**
     * Returns true if the given $text written in $lang is considered as readable
     *
     * @param string $text
     * @param string $lang
     * @return boolean
     */
    public function isReadable ($text, $lang = 'en') {
        return $this->readabilityIs($text, $lang, [
            Readability::VERY_EASY,
            Readability::EASY,
            Readability::FAIRLY_EASY,
            Readability::STANDARD
        ]);
    }

    /**
     * Returns true if the given $text written in $lang is considered as not readable
     *
     * @param string $text
     * @param string $lang
     * @return boolean
     */
    public function isNotReadable ($text, $lang = 'en') {
        return $this->readabilityIs($text, $lang, [
            Readability::VERY_DIFFICULT,
            Readability::DIFFICULT,
            Readability::FAIRLY_DIFFICULT
        ]);
    }

    /**
     * Generates an alt description for the given `$imageUrl` written in `$lang`
     *
     * @param string $imageUrl
     * @param string $lang
     * @return array
     */
    public function describeImage ($imageUrl, $lang = 'en') {
        $data = [
            'image_url' => $imageUrl,
            'lang' => $lang
        ];

        $response = $this->instance->request('image-alt-detection', $data);
        if (!isset($response['alt']) || !isset($response['confidence'])) {
            throw new InvalidRequestException('Unknown error');
        }

        return $response;
    }

    /**
     * Returns the BERT Score for a given URL and Keyword.
     * 
     * BERT Score represents, from 0 to 100, how well the content of your URL
     * answers the user's search intent.
     *
     * @param string $url
     * @param string $keyword
     * @return int
     */
    public function bertScore ($url, $keyword) {
        $data = [
            'url' => $url,
            'keyword' => $keyword
        ];

        $response = $this->instance->request('bert-score', $data);
        if (!isset($response['bert_score'])) {
            throw new InvalidRequestException('Unknown error');
        }

        return $response['bert_score'];
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
