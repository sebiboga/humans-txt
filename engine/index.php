<?php

declare(strict_types=1);

const DEFAULT_DOMAIN = 'https://humanstxt.org';
const SERVER_ID = '{{SERVER_ID}}'; // Placeholder for the SERVER_ID
const CHANNEL_ID = '{{CHANNEL_ID}}'; // Placeholder for the CHANNEL_ID

function setCorsHeaders(): void
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Referrer-Policy");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 3600");
}

function sendToDiscordWebhook(string $msg): void
{
    $url = 'https://discord.com/api/webhooks/' . SERVER_ID . '/' . CHANNEL_ID;
    $data = ['content' => $msg];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function processUrl(string $domain): string
{
    if (!preg_match('/^https?:\/\//', $domain)) {
        $domain = 'https://' . $domain;
    }
    if (!preg_match('/^https?:\/\/www\./', $domain)) {
        $domain = preg_replace('/^https?:\/\//', '$0www.', $domain);
    }
    return $domain;
}

function checkHumansTxtExistence(string $domain): string|bool
{
    $url = $domain . '/humans.txt';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $lastEffectiveURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    if ($httpCode === 200 && str_ends_with($lastEffectiveURL, 'humans.txt')) {
        return $response;
    }
    return false;
}

function main(): void
{
    setCorsHeaders();

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit;
    }

    $domain = filter_input(INPUT_POST, 'domain', FILTER_SANITIZE_URL)
                ?? filter_input(INPUT_GET, 'domain', FILTER_SANITIZE_URL)
                ?? DEFAULT_DOMAIN;

    $checkUrls = [
        processUrl($domain),
        str_replace('www.', '', processUrl($domain))
    ];

    foreach ($checkUrls as $url) {
        $result = checkHumansTxtExistence($url);
        if ($result) {
            sendToDiscordWebhook("Found humans.txt at {$url}/humans.txt on " . date('l d-m-Y H:i:s'));
            echo $result;
            exit;
        }
    }

    header('HTTP/1.1 404 Not Found');
}

main();
