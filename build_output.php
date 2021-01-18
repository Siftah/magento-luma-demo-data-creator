#!/usr/bin/php
<?php
include('lib.php');
include('lib_fixtures.php');

//Some variables to pass to the API
$countryID = 'GB';//GB (not UK!) as default country code
$minPopulation = '100000';//default minimum population for inclusion

//Some 'configuration' kind of variables
$numWarehouses = '2';//Create this number of warehouse sources
$sourceStock = array ();
///////////////////////////////////////////////////////////////////////////////////////

if ($argv[1] != '') {
    $countryID = $argv[1];
}
    echo('Using country ID: '.$countryID."\r\n");

$fp = fopen('sources_'.strtolower($countryID).'.csv', 'w');
fputcsv($fp, array('source_code','source_name','contact_name','email','enabled','country_id',
	'postcode','street','city','region_id','region','lat','long','phone') );

$fp_source_stock = fopen('source_stock_'.strtolower($countryID).'.csv', 'w');
fputcsv($fp_source_stock, array('source_code','stock','priority'));

$curl = curl_init();
curl_setopt_array($curl, [
	CURLOPT_URL => "https://wft-geo-db.p.rapidapi.com/v1/geo/cities?limit=10&countryIds=".$countryID."&minPopulation=".$minPopulation."&sort=-population&types=CITY",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => [
		"x-rapidapi-host: wft-geo-db.p.rapidapi.com",
		"x-rapidapi-key: 9df5e458e0msh48ac0202a07e046p155a1cjsn8f829d9678d8"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	echo "cURL Error #:" . $err;
} else {
	$json_response = json_decode($response,true);
}

function convertToNative($data,$sourceType){
	$thisPerson = returnPerson();

	//source_code,source_name,contact_name,email,enabled,country_id,postcode,street,city,region_id,region,lat,long,phone
	//us_warehouse,US Warehouse,John McClane,drhodes@thelumastory.com,1,US,78758,11501 Domain Dr #150,Austin,57,Texas,30.4017861,-97.7258717,555-555-5555
	$source_code = strtolower($data['countryCode']).'_'.$sourceType.'_'.strtolower($data['city']);
	$source_name = $data['city'];

		if($sourceType=='warehouse') { $source_name = $source_name .' Warehouse'; }
	$contact_name = $thisPerson['first_name'] . ' ' . $thisPerson['last_name'];
	$email = strtolower($data['city']) . '@thelumastory.com';
	$enabled = '1';
	$country_id = $data['countryCode'];
	$postcode = $data['id'];//this is a bit hacky but it's a numeric id that is unique
	$street = $thisPerson['address1'];
	$city = $data['city'];
	$region_id = $data['region_id'];
	$region = $data['region'];
	$lat = $data['latitude'];
	$long = $data['longitude'];
	$phone = $thisPerson['phone'];

	$thisRow = array($source_code,$source_name,$contact_name,$email,$enabled,$country_id,$postcode,$street,$city,$region_id,$region,$lat,$long,$phone);

	return($thisRow);
}

$count=0;

foreach ($json_response['data'] as $thisData) {
	$count++;
	$thisRow = convertToNative($thisData, 'store');
	fputcsv($fp, $thisRow);

	if($count <= $numWarehouses){
			$thisRow = convertToNative($thisData, 'warehouse');
			fputcsv($fp, $thisRow);
	}

	$sourceStockRow = array ( $thisRow[0], 'Europe', $count);
	fputcsv($fp_source_stock, $sourceStockRow);

	$generatedSources[] = $thisRow[0];

}
fclose($fp);
fclose($fp_source_stock);

$fp_luma_msi_inventory = fopen('luma_msi_inventory_'.strtolower($countryID).'.csv', 'w');
fputcsv($fp_luma_msi_inventory, array('id','sku','source_code', 'status','quantity'));
$line=0;

foreach($generatedSources as $thisSource) {
	foreach($skuList as $thisSku) {
		$thisLine = array ($line, $thisSku, $thisSource, '1', rand(10,400) );
		fputcsv($fp_luma_msi_inventory, $thisLine);
		$line++;
	}
}

fclose($fp_luma_msi_inventory);

