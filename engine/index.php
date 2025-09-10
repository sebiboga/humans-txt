<?php
// Set the allowed origin (change * to the specific domain you want to allow)
header("Access-Control-Allow-Origin: *");

// Set the allowed HTTP methods (e.g., GET, POST, PUT, DELETE)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

// Set the allowed HTTP headers
header("Access-Control-Allow-Headers: Content-Type, Referrer-Policy");

// Allow credentials (if needed)
header("Access-Control-Allow-Credentials: true");

// Set the cache expiration time (in seconds) for the preflight request
header("Access-Control-Max-Age: 3600");

// Handle preflight requests
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    // This is a preflight request, so just respond with the allowed headers
    header("HTTP/1.1 200 OK");
    exit;
}

$rawDomain = isset($_POST['domain']) ? $_POST['domain'] : "https:\/\/peviitor.ro";
$rawDomain = isset($_GET['domain']) ? $_GET['domain'] : $rawDomain;

function discord_webhook($msg) {
    $prefix ='Found humans.txt at '; 
    $date = '  on '.date("l d-m-Y H:i:s");
    $msg =$prefix.$msg.'/humans.txt'.$date;
    $method = 'POST';

	$server_id = '{{SERVER_ID}}'; // Placeholder for the SERVER_ID
    $channel_id = '{{CHANNEL_ID}}'; // Placeholder for the CHANNEL_ID
	
    $url = "https://discord.com/api/webhooks/".$server_id."/".$channel_id;
    $data = '{"content": "'.$msg.'"}';

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => $data
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) { /* Handle error */ }
    
 }

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
    $url = $domain.'/humans.txt';
    $headers =[];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
     if ($httpCode == 200) {
        return $response;
     }

     else {

    if (strpos($response, 'humans.txt') !== false) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
        $response = curl_exec($ch);
        $lastEffectiveURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   
    if ($httpCode == 404) {
       return false;
    } elseif (strpos($response, 'humans.txt') !== false) 
               { 
        return $response;
               } 
                else 
                     {  return false;
                     }
                                                  }
     }
    curl_close($ch);
}

// Remove backslashes and call the function to add the protocol
$domainWithProtocol = addProtocolToDomain(stripslashes($rawDomain));

// Call the function to add "www" after "https://"
$domainWithWww = addWwwAfterHttps($domainWithProtocol);

// Call the function to check the existence of humans.txt
 $result = checkHumansTxtExistence($domainWithWww);
if (!$result) {
     $result_r = checkHumansTxtExistence($domainWithProtocol);
       if (!$result_r) {
            header("HTTP/1.1 404 Not Found"); 
                      }
            else { 
                discord_webhook($domainWithProtocol);
                echo $result_r;}
             }
              else
                 {
                  discord_webhook($domainWithWww);
                  echo $result;
                  }

?>
