<?php
$rawDomain = isset($_POST['domain']) ? $_POST['domain'] : "https:\/\/peviitor.ro";
$rawDomain = isset($_GET['domain']) ? $_GET['domain'] : $rawDomain;

function addProtocolToDomain($domain) {
    if (strpos($domain, 'http://') !== 0 && strpos($domain, 'https://') !== 0) {
        $domain = 'https://' . $domain; // Add "https://" as the protocol
    }
    return $domain;
}

function addWwwAfterHttps($domain) {
    if (strpos($domain, 'https://www.') !== 0) {
        $domain = str_replace('https://', 'https://www.', $domain); // Add "www" after "https://"
    }
    return $domain;
}

function checkHumansTxtExistence($domain) {
    $humansTxtURL = $domain . '/humans.txt';
    $headers = @get_headers($humansTxtURL);
    
    return strpos($headers[0], '200 OK') !== false;
}

// Remove backslashes and call the function to add the protocol
$domainWithProtocol = addProtocolToDomain(stripslashes($rawDomain));

// Call the function to add "www" after "https://"
$domainWithWww = addWwwAfterHttps($domainWithProtocol);

// Call the function to check the existence of humans.txt
$humansTxtExists = checkHumansTxtExistence($domainWithWww);

$response = [
    'domain' => $domainWithWww,
    'humans.txt' => $humansTxtExists,
];

header('Content-Type: application/json');
echo json_encode($response);
?>