<?php
require_once 'vendor/autoload.php';
require_once 'SilverDiamond.php';

use SilverDiamond\SilverDiamond;
use SilverDiamond\InvalidRequestException;
use SilverDiamond\Sentiment;
use SilverDiamond\Language;

$silver = new SilverDiamond('YzIrAQ3id7UW318T3q4anY3xcOypfPhplzvDtBKyVvMREUwBGgIISEKAh3ro');

$saludo = 'Hola, qué tal?';
$greeting = 'Hello! I hope everything is going great!';
$enfadado = 'Hola, Jordán! Llevo tiempo queriendo escribirte ya que la comida de tu bar era una auténtica mierda';
$angry = 'Hi, Jordan! I\'ve been wanting to talk to you because your restaurant was absolute trash';
$spam = 'look here <a href=https://pornbot.pro/>porn bot</a>';
$ham = 'Me ha encantado este post. Felicidades!';

try {
    var_dump($silver->detectLanguage($saludo));
    var_dump($silver->languageIsSpanish($saludo));
    var_dump($silver->languageIs($greeting, ['es', 'en']));
    var_dump($silver->detectSentiment($enfadado));
    var_dump($silver->detectSentiment($angry));
    
    var_dump($silver->sentimentIs($enfadado, [Sentiment::NEGATIVE, Sentiment::VERY_NEGATIVE]));
    var_dump($silver->sentimentIsPositive($greeting));
    var_dump($silver->sentimentIsPositive($enfadado));
    var_dump($silver->sentimentIsNegative($enfadado));

    var_dump($silver->isSpam($spam));
    var_dump($silver->isHam($ham));
    var_dump($silver->spamScore($spam));

    var_dump($silver->similarity($enfadado, $angry));

} catch (InvalidRequestException $e) {
    echo $e->getMessage();
}