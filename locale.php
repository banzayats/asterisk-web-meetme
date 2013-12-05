<?php

putenv("LANG=ru_RU");
$locale = 'ru_RU';

setlocale(LC_ALL, $locale);

// Specify location of translation tables
bindtextdomain("messages", "./locale");

// Choose domain
textdomain("messages");

bind_textdomain_codeset("messages", 'UTF-8');

?>
