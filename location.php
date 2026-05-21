<?php

/*
    Twine is a custom backend for the Vine social media app.
    Copyright (C) 2026  calvink19
*/

// This file contains the code to osm2foursquare by Savefade.

$maintenance = false;
$maintenanceBypassUserAgents = [];
$openStreetMapBaseURL = "https://nominatim.openstreetmap.org/";
$searchLimit = 20;

if($maintenance && !in_array($_SERVER['HTTP_USER_AGENT'], $maintenanceBypassUserAgents)){
    header("HTTP/1.1 400 Bad Request");
    die(json_encode(array(
        'message' => "osm2foursquare is under maintenance. Please try again in a few hours!",
    )));
}

function searchByName($query, $resultLimit){
    $searchQuery = urlencode($query);

    return doHttpRequest("search?q=" . $searchQuery . "&format=jsonv2&addressdetails=1&limit=20");
}

function doHttpRequest($endpoint){

    $options = [
        "http" => [
            "header" => "User-Agent: osm2foursquare (20110211) bridge 1.0\r\n"
        ]
    ];

    $context = stream_context_create($options);
    return file_get_contents($openStreetMapBaseURL . $endpoint, false, $context);
}

function searchByCoordinates($lat, $lon, $resultLimit){
    $latitude = urlencode($lat);
    $longitude = urlencode($lon);

    $searchQuery = $latitude . "," . $longitude;

    return doHttpRequest("search?q=" . $searchQuery . 
    "&format=jsonv2&addressdetails=1&limit=20");
}

function getLocationData($lat, $lon){
    return doHttpRequest("reverse?lat=" . $lat . "&lon=" . $lon .
    "&format=jsonv2&addressdetails=1");
}

function processPlacesFromOpenStreetMap($placesData){
    $placesArray = array();

    foreach($placesData as $place){

        $county = $place["address"]["county"] ?? "Bucharest";
        $state = $place["address"]["state"] ?? "Bucharest";
        $country =  $place["address"]["country"] ?? "Romania";

        $placesArray[] = array (
            "id" => (string)$place["place_id"],
            'name' => $place["name"],
            'contact' => array (),
            'location' => 
            array (
                'address' => $place["display_name"],
                "lat" => (float)$place["lat"],
                "lng" => (float)$place["lon"],
                'labeledLatLngs' => array (
                    array (
                        'label' => 'display',
                        "lat" => (float)$place["lat"],
                        "lng" => (float)$place["lon"],
                    ),
                ),
                'distance' => 0,
                'postalCode' => '0',
                'cc' => $place["address"]["country_code"] ?? "RO",
                'city' => $county,
                'state' => $state,
                'country' => $country,
                'formattedAddress' => array (
                    0 => $place["display_name"],
                    1 => '',
                    2 => '',
                ), //keeping only the display name might create a valid formatted address
            ),
            'categories' => array (
                array (
                "id" => (string)$place["place_id"],
                'name' => $place["name"],
                'pluralName' => $place["name"],
                'shortName' => $place["name"],
                'icon' => array (
                    'prefix' => 'http://google.com/',
                    'suffix' => '.png',
                ),
                'categoryCode' => 18084,
                'mapIcon' => 'fsq-landmarks-outdoors',
                'mapTextColor' => '#000000',
                'mapDarkTextColor' => '#000000',
                'primary' => true,
                ),
            ),
            'verified' => false,
            'stats' => array (
                'tipCount' => 0,
                'usersCount' => 0,
                'checkinsCount' => 0,
            ),
            'header' => 'This place doesn\'t have a rating yet. Once more members of the Foursquare community visit, we can calculate a rating.',
            'explanation' => 'We take into account visits and input from our community. You\'ll see a number on places based off of ratings and tips written by people like you.',
            'beenHere' => array (
                'lastCheckinExpiredAt' => 0,
            ),
            'createdAt' => time(),
            'hereNow' => array (
                'count' => 0,
                'summary' => 'Nobody here',
                'groups' => array (),
            ),
            'venueChains' => array (),
        );
    } // this is never going to be 100% accurate due to osm not providing enough data

    return $placesArray;
}

$response = null;

if(isset($_GET["query"])){
    $response = searchByName($_GET["query"], $searchLimit);
}else{

    $exploadedCooridinates = explode(",", $_GET["ll"]);

    if(!isset($exploadedCooridinates[0]) || !isset($exploadedCooridinates[1])){
        exit;
    }

    $longitute = $exploadedCooridinates[0];
    $latitude = $exploadedCooridinates[1];

    $response = searchByCoordinates($longitute, $latitude, $searchLimit);
}

$places = processPlacesFromOpenStreetMap(json_decode($response, true));

header("Content-Type: application/json");
die(json_encode(array (
    'meta' => 
    array (
        'code' => 200,
        'v' => 20111101,
        'requestId' => time() . "",
    ),
    'response' => 
    array (
        'venues' => $places,
    ),
)));