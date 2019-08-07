<?php

require(dirname(__FILE__).'/../Classloader.php');

use erede\model\EnvironmentType;
use erede\model\TransactionKind;
use erede\model\TransactionRequest;
use erede\model\IataRequest;
use erede\model\ThreeDSecureRequest;
use erede\model\UrlRequest;
use erede\model\UrlKind;
use erede\model\AvsRequest;
use erede\model\AddressRequest;
use erede\model\ThreeDSecureOnFailure;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$ac = new Acquirer('10000205','c222797e7101493fa9a92a7a555d206e', EnvironmentType::HOMOLOG);

$transactionRequest = new TransactionRequest();
$transactionRequest->setCapture('true');
$transactionRequest->setKind(TransactionKind::CREDIT);
$transactionRequest->setReference('php' . ((string)mt_rand(0, 999999999)));
$transactionRequest->setAmount('2000');
$transactionRequest->setInstallments('0');
$transactionRequest->setCardHolderName('Portador');
$transactionRequest->setCardNumber('5448280000000007');
$transactionRequest->setExpirationMonth('05');
$transactionRequest->setExpirationYear('20');
$transactionRequest->setSecurityCode('123');
$transactionRequest->setOrigin('1');

// $iataRequest = new IataRequest();
// $iataRequest->setCode('101010');
// $iataRequest->setDepartureTax('5000');
// $transactionRequest->setIata($iataRequest);

// $threeDsRequest = new ThreeDSecureRequest();
// $threeDsRequest->setEmbedded('false');
// $threeDsRequest->setCavv('jF6hPiHFPmPwCBER3JmBBUMAAAA=');
// $threeDsRequest->setEci('05');
// $threeDsRequest->setXid('WEUxbk0xMDJ1VGxwSHdocHJwR2s=');
// // $threeDsRequest->setOnFailure(ThreeDSecureOnFailure::DECLINE);
// // $threeDsRequest->setUserAgent('Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405');
// $transactionRequest->setThreeDSecure($threeDsRequest);

// $urls = array();
// $urlCallback = new UrlRequest();
// $urlCallback->setUrlKind(UrlKind::THREE_D_SECURE_SUCCESS);
// $urlCallback->setUrl('https://redirecturl.com/3ds/success');
// $urls[] = $urlCallback;
// $urlFailure = new UrlRequest();
// $urlFailure->setUrlKind(UrlKind::THREE_D_SECURE_FAILURE);
// $urlFailure->setUrl('https://redirecturl.com/3ds/failure');
// $urls[] = $urlFailure;
// $transactionRequest->setUrls($urls);

echo "Request JSON: " . $transactionRequest->toJson() . "\n";
$v = $ac->authorize($transactionRequest);
echo "Response: JSON: " . $v->toJson() . "\n";
echo "TID: " . $v->getTid();
