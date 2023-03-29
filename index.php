<?php
include 'TinyUrl.class.php';

use MyLib\tinyurl\TinyURL;

try {

    $long_url = 'https://www.example.com/path/to/page/1';
    // $tiny_url = (new TinyURL)->create($long_url);
    $tiny_url = (new TinyURL)->short_create($long_url);
    echo $tiny_url;

} catch (\Throwable $e) {
    die($e->getMessage());
}

