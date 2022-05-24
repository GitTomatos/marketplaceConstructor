<?php

require "./vendor/autoload.php";

use App\Application\ConstructorEngine\OwnTemplate;
use Symfony\Component\HttpClient\HttpClient;

$pathToConfigs = __DIR__ . '/config/marketplaceEngine/engineConfigs.php';

$client = HttpClient::create();
(new OwnTemplate(
    $pathToConfigs,
    $client)
)->own();