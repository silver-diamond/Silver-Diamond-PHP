<?php
require_once 'SilverDiamond.php';

use SilverDiamond\SilverDiamond;
die('hoal');
SilverDiamond::init('YzIrAQ3id7UW318T3q4anY3xcOypfPhplzvDtBKyVvMREUwBGgIISEKAh3ro');

var_dump(SilverDiamond::detectLanguage('Hola, qué tal?'));

var_dump(SilverDiamond::languageIs('Hola, qué tal?', 'es'));