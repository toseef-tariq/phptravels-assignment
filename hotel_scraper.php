<?php

//https://rapidapi.com/DataCrawler/api/booking-com15/playground/

// API Credentials
$apiKey = 'e94c9a6ee3msh4edb9659d89f741p184c00jsn275c5acc7dba';
$apiHost = 'booking-com.p.rapidapi.com';

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
$destSearchUrl = "https://$apiHost/v1/hotels/locations?locale=en-gb&name=Dubai";
$destData = fetchFromRapidAPI($destSearchUrl, $apiKey, $apiHost);

// Check if destination ID is found
if (!isset($destData[0]['dest_id']) && ($destData[0]['name'] != "Dubai")) {
    die(json_encode(["error" => "Destination ID for Dubai not found."]));
}

$destId = $destData[0]['dest_id'];

// Search for hotels
$params = [
    'adults_number' => 2,
    'locale' => 'en-gb',
    'dest_type' => 'city',
    'filter_by_currency' => 'USD',
    'dest_id' => $destId,
    'order_by' => 'popularity',
    'units' => 'metric',
    'checkout_date' => '2025-07-05',
    'room_number' => 1,
    'checkin_date' => '2025-07-01'
];

$hotelSearchUrl = "https://$apiHost/v1/hotels/search?" . http_build_query($params);
$hotelResponse = fetchFromRapidAPI($hotelSearchUrl, $apiKey, $apiHost);

// Check if we have results
if (!isset($hotelResponse['result']) || !is_array($hotelResponse['result'])) {
    die(json_encode(["error" => "No hotel data returned."]));
}

// Format the hotel data
$hotelData = [];
$nights = 4; // number of nights (July 1 to July 5, 2025)

//Map fields from the API response
foreach ($hotelResponse['result'] as $hotel) {
    $price = floatval($hotel['composite_price_breakdown']['gross_amount_hotel_currency']['value'] ?? 0);
    $pricePerNight = floatval($hotel['composite_price_breakdown']['gross_amount_per_night']['value'] ?? 0);
    $location = $hotel['city_name_en'] . ', ' . $hotel['country_trans'];

    //to append formatted objects to $hotelData
    $hotelData[] = (object)[
        "hotel_id" => $hotel['hotel_id'] ?? '',
        "img" => $hotel['main_photo_url'] ?? '',
        "name" => $hotel['hotel_name'] ?? '',
        "location" => $location,
        "address" => $hotel['address_trans'] ?? '',
        "stars" => $hotel['class'] ?? '', 
        "rating" => floatval($hotel['review_score'] ?? 0),
        "latitude" => floatval($hotel['latitude'] ?? 0),
        "longitude" => floatval($hotel['longitude'] ?? 0),
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
echo json_encode($hotelData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit();
?>
