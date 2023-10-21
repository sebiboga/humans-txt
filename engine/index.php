<?php
$rawDomain = isset($_POST['domain']) ? $_POST['domain'] : "https:\/\/peviitor.ro";

function addProtocolToDomain($domain) {
    if (strpos($domain, 'http://') !== 0 && strpos($domain, 'https://') !== 0) {
        $domain = 'https://' . $domain; // Add "https://" as the protocol
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

// Call the function to check the existence of humans.txt
$humansTxtExists = checkHumansTxtExistence($domainWithProtocol);

$response = [
    'domain' => $domainWithProtocol,
    'humansTxtExists' => $humansTxtExists,
];

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_SLASHES);
?>