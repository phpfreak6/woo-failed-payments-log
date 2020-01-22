<?php
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/client_secret.json');
require __DIR__ . '/vendor/autoload.php';
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
$client = new Google_Client;
$client->useApplicationDefaultCredentials();

$client->setApplicationName("Google Sheets API");
$client->setScopes(['https://www.googleapis.com/auth/drive','https://spreadsheets.google.com/feeds']);
	$client->isAccessTokenExpired() ;
if ($client->isAccessTokenExpired()) {
   $client->refreshTokenWithAssertion();
}

$accessToken = $client->fetchAccessTokenWithAssertion()["access_token"];
ServiceRequestFactory::setInstance(
    new DefaultServiceRequest($accessToken)
);

try{
$spreadsheet = (new Google\Spreadsheet\SpreadsheetService)
   ->getSpreadsheetFeed()
   ->getByTitle($spreadsheet_name);
// Get the first worksheet (tab)
$worksheets = $spreadsheet->getWorksheetFeed()->getEntries();
$worksheet = $worksheets[0];

$cellFeed = $worksheet->getCellFeed();

$cellFeed->editCell(1,1, "Subscription ID");
$cellFeed->editCell(1,2, "Order ID");
$cellFeed->editCell(1,3, "First Name");
$cellFeed->editCell(1,4, "Last Name");
$cellFeed->editCell(1,5, "Email");
$cellFeed->editCell(1,6, "Subscription Status");
$cellFeed->editCell(1,7, "Order Status");
$cellFeed->editCell(1,8, "Payment Method");
$cellFeed->editCell(1,9, "Date");

$listFeed = $worksheet->getListFeed();
$datainsert = $listFeed->insert(array("subscriptionid" => $subscription_id, "orderid" => $order_id,"firstname"=>$first_name,"lastname"=>$last_name,"email"=>$email,"subscriptionstatus"=>$status,"orderstatus"=>$statusorder,"paymentmethod"=>$payment_method,"failreason"=>$reason,"date"=>$date));
}catch(Exception $e){
	
	echo $error = $e->getMessage();
	
}
