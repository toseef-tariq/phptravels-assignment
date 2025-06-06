<?php

//https://rapidapi.com/DataCrawler/api/booking-com15/playground/

// API Credentials inline
$apiKey = 'e94c9a6ee3msh4edb9659d89f741p184c00jsn275c5acc7dba';
$apiHost = 'booking-com15.p.rapidapi.com';

// Function to make a GET request to RapidAPI
function fetchFromRapidAPI($url, $apiKey, $apiHost) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            "X-RapidAPI-Key: $apiKey",
            "X-RapidAPI-Host: $apiHost"
        ]
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        die(json_encode(["error" => "cURL Error: $err"]));
    }

    return json_decode($response, true);
}

// Get destination ID for Dubai
$destSearchUrl = "https://$apiHost/api/v1/hotels/searchDestination?query=dubai";
$destData = fetchFromRapidAPI($destSearchUrl, $apiKey, $apiHost);

// Check if destination ID is found
if (!isset($destData['data'][0]['dest_id'])) {
    die(json_encode(["error" => "Destination ID for Dubai not found."]));
}

$destId = $destData['data'][0]['dest_id'];
$destType = $destData['data'][0]['search_type'];

// Search for hotels
$params = [
    'dest_id' => $destId,
    'search_type' => $destType,
    'arrival_date' => '2025-07-01',
    'departure_date' => '2025-07-05',
    'adults' => 2,
    'room_qty' => 1,
    'currency_code' => 'USD',
];

$hotelSearchUrl = "https://$apiHost/api/v1/hotels/searchHotels?" . http_build_query($params);
$hotelResponse = fetchFromRapidAPI($hotelSearchUrl, $apiKey, $apiHost);

// Check hotel list
if (!isset($hotelResponse['data']['hotels']) || !is_array($hotelResponse['data']['hotels'])) {
    die(json_encode(["error" => "No hotel data returned."]));
}

// Format the hotel data
$hotelData = [];
$nights = 4; // number of nights (July 1 to July 5, 2025)

//Map fields from the API response
foreach ($hotelResponse['data']['hotels'] as $hotel) {
    $property = $hotel['property'] ?? [];
    $price = floatval($property['priceBreakdown']['grossPrice']['value'] ?? 0);
    $pricePerNight = $price / $nights; // price per night for 4 nights
    $location = ($property['city'] ?? "Dubai") . ' ' . ($property['country'] ?? "UAE");

    //to append formatted objects to $hotelData
    $hotelData[] = (object)[
        "hotel_id" => $hotel['hotel_id'] ?? '',
        "img" => $property['photoUrls'][0] ?? '',
        "name" => $property['name'] ?? '',
        "location" =>  $location, // Not provided in API response
        "address" => $property['address'] ?? '', // Not provided in API response
        "stars" => $property['accuratePropertyClass'] ?? '', 
        "rating" => floatval($property['reviewScore'] ?? 0),
        "latitude" => floatval($property['latitude'] ?? 0),
        "longitude" => floatval($property['longitude'] ?? 0),
        "actual_price" => round($price, 2),
        "actual_price_per_night" => round($pricePerNight, 2), 
        "markup_price" => round(floatval($price * 1.15), 2), // 15% markup
        "markup_price_per_night" => round(floatval($pricePerNight * 1.15), 2),
        "currency" => "USD",
        "booking_currency" => "USD",
        "service_fee" => "0",
        "supplier_name" => "hotels",
        "supplier_id" => "1",
        "redirect" => "",
        "booking_data" => (object)[],
        "color" => "#FF9900"
    ];
}

// Output the result in JSON format
header('Content-Type: application/json');
echo json_encode($hotelData, JSON_PRETTY_PRINT);
exit();
?>