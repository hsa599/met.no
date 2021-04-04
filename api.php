function curlGet($url){

    $filePath = "test.cache";
    $time = 180 * 60;

    if (!file_exists($filePath) || filemtime($filePath) < (time() - $time))
    {

	// read json source
	$ch = curl_init($url) or die("curl issue");
	$curl_options = array(
		CURLOPT_RETURNTRANSFER	=> true,
		CURLOPT_HEADER 		=> false,
		CURLOPT_FOLLOWLOCATION	=> false,
		CURLOPT_ENCODING	=> "",
		CURLOPT_AUTOREFERER 	=> true,
		CURLOPT_CONNECTTIMEOUT 	=> 7,
		CURLOPT_TIMEOUT 	=> 7,
		CURLOPT_MAXREDIRS 	=> 3,
		CURLOPT_SSL_VERIFYHOST	=> false,
		CURLOPT_USERAGENT	=> "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13"
	);
	curl_setopt_array($ch, $curl_options);
	$curlcontent = curl_exec( $ch );
	curl_close( $ch );
	
	$handle = fopen($filePath, 'wb') or die('no fopen');	
	$json_cache = $curlcontent;
	fwrite($handle, $json_cache);
	fclose($handle);
} else {
	$json_cache = file_get_contents($filePath); //locally
}

 return $json_cache;
	
}

$url = 'https://api.met.no/weatherapi/locationforecast/2.0/compact?lat=32.660&lon=51.671';
$response = curlGet($url);
$data = json_decode($response, true);
$all = array();

foreach($data['properties']['timeseries'] as $ts) {
	$time    = $ts['time'];
	$date = new DateTime($time, new DateTimeZone("UTC"));
	//echo $date->format("Y-m-d H:i:s") . "<br>";
	$date->setTimezone(new DateTimeZone('Asia/Tehran'));
	$h = $date->format("H");
	$d = $date->format("md");
	//echo $h . "<hr>";
	$temp = $ts["data"]["instant"]["details"]["air_temperature"];
	$symbol = $ts["data"]["next_1_hours"]["summary"]["symbol_code"];
	$wind = $ts["data"]["instant"]["details"]["wind_speed"];
	if(is_numeric($wind) && is_numeric($temp) && $symbol){	
		$all[$d]["winds"][] = $wind;
		
		if($h >= 0 && $h <= 12){
			$symbol = $ts["data"]["next_12_hours"]["summary"]["symbol_code"];
			$all[$d]["morning"]["temps"][] = $temp;			
			if(!$all[$d]["morning"]["symbols"]) $all[$d]["morning"]["symbols"] = $symbol;
		}
		if($h > 12 && $h <= 17){
			$symbol = $ts["data"]["next_6_hours"]["summary"]["symbol_code"];
			$all[$d]["afternoon"]["temps"][] = $temp;
			if(!$all[$d]["afternoon"]["symbols"]) $all[$d]["afternoon"]["symbols"] = $symbol;
		}
		if($h > 17 && $h <= 20){
			$all[$d]["evening"]["temps"][] = $temp;
			if($h > 18)
			$all[$d]["evening"]["symbols"] = $symbol;			
		}
		if($h > 20 && $h <= 24){
			$all[$d]["night"]["temps"][] = $temp;
			$all[$d]["night"]["symbols"] = $symbol;
		} 	
	
		
		
		if(isset($all[$d]["min"])){
			if($temp < $all[$d]["min"]){
				$all[$d]["min"] = $temp;
			}
		}else{
			$all[$d]["min"] = $temp;
		}
		
		if(isset($all[$d]["max"])){
			if($temp > $all[$d]["max"]){
				$all[$d]["max"] = $temp;
			}
		}else{
			$all[$d]["max"] = $temp;
		}
	}

}
foreach($all as $k=>$row){
	echo $k . "<br>";
	echo "Wind: " . round((array_sum($row["winds"]) / count($row["winds"]) * 3.6)) . " km/h<br>";
	echo "Min Temp: " . round($row["min"]) . "<br>";
	if(is_array($row["afternoon"]["temps"])){
		echo "AfterNoon Temp: " . min($row["afternoon"]["temps"]) . " to " . max($row["afternoon"]["temps"]) . "<br>";
	}
	echo "<hr>";	
}
