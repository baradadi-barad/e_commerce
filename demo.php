<?php

$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => "https://pinterest-scraper.p.rapidapi.com/pint/?shortcode=6iVigMK",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => [
		"X-RapidAPI-Host: pinterest-scraper.p.rapidapi.com",
		"X-RapidAPI-Key: d6f1d0c316msh1052c0cf82d45b1p1141f4jsnf7bdc66c9a46"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	echo "cURL Error #:" . $err;
} else {
	echo '<pre>'; print_r($response);
}