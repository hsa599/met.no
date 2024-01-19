<?php

$location = "Oslo"; // Replace with the desired location

// Make a request to the MET Norway API
$response = file_get_contents("https://api.met.no/weatherapi/locationforecast/2.0/compact?query=" . urlencode($location));

// Check if the request was successful
if ($response !== false) {
    $data = json_decode($response, true);

    // Extract relevant information from the response
    $temperature = $data['properties']['timeseries'][0]['data']['instant']['details']['air_temperature'];
    $windSpeed = $data['properties']['timeseries'][0]['data']['instant']['details']['wind_speed'];

    // Output the weather information
    echo "Temperature in $location: $temperature °C<br>";
    echo "Wind speed in $location: $windSpeed m/s";
} else {
    // Handle error if the request fails
    echo "Failed to retrieve weather data.";
}

?>
