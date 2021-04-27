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
