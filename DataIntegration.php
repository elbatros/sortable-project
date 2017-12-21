<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Products-Listings page</title>
</head>
<body>
<?php

//Read file contents
$strProducts = file_get_contents('products.json');
$jsonarrProducts = json_decode($strProducts,true);

$strListings = file_get_contents('listings.json');
$jsonarrListings = json_decode($strListings,true);


//Calculate Count of both the arrays
$countProducts = count($jsonarrProducts);
$countListings = count($jsonarrListings);


if($countProducts>0 && $countListings>0)
{
$allproductnames = array_column($jsonarrListings, 'manufacturer');
$uniqueproductNames= array_unique($allproductnames);

$finalarray = array();

foreach ($uniqueproductNames as &$value) 
{
$replaceVal=str_replace("Canada","",$value);
$replaceVal=strtolower(trim($replaceVal));

$new = array_filter($jsonarrListings, function ($var)  use ($value){
    return ($var['manufacturer'] ==$value);
});



$firstWord = explode(' ',trim($value));

if(array_key_exists($replaceVal, $finalarray))
{
	$tempArr= $finalarray[$replaceVal];
	$newArr=array_merge($tempArr, $new);
}
else if(Count($firstWord)>0 && array_key_exists(strtolower(trim($firstWord[0])), $finalarray))
{
	$tempArr= $finalarray[strtolower(trim($firstWord[0]))];
	$newArr=array_merge($tempArr, $new);
	$replaceVal=strtolower(trim($firstWord[0]));
}
else
{
	$newArr=$new;
}



$finalarray[$replaceVal]=$newArr;
}



//Loop thru products
$finalProductArray=array();
foreach ($jsonarrProducts as &$productVal) 
{
$manufacturerVal=strtolower(trim($productVal["manufacturer"]));
$modelVal=strtolower(trim($productVal["model"]));
if(array_key_exists($manufacturerVal, $finalarray))
{
//Check 
//1. If manufacturer name and model is in title 
//2. If model is a number then it should not be part of another number e.g.: 600 is not equal to 1600
$filtered_array = array_filter($finalarray[$manufacturerVal], function($a) use ($manufacturerVal, $modelVal){$isVal=false; if (is_numeric($modelVal)) {   if(preg_match("/\b$modelVal\b/i", $a['title'])) {$isVal=true;}} else {$isVal=stristr($a['title'],$modelVal);} return stristr($a['title'],$manufacturerVal)!==false && $isVal;});

if(Count($filtered_array)>0)
{
$finalProductArray[] = array('product_name'=> $productVal["product_name"], 'listings'=> array_values($filtered_array));
}
else
$finalProductArray[] = array('product_name'=> $productVal["product_name"], 'listings'=> array());
}

}

//Encoding values
$jsonEncodeValues=json_encode(array_values($finalProductArray));
$writeValueToJSON=substr($jsonEncodeValues,1,-1);
$writeValueToJSON=str_replace(",{\"product_name\"","\r\n{\"product_name\"",$writeValueToJSON);

//Write to a JSON
$fp = fopen('results.txt', 'w');
fwrite($fp, $writeValueToJSON);
fclose($fp);
}



?>
</body>
</html>