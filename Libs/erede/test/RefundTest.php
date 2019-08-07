<?php

require(dirname(__FILE__).'/../Classloader.php');

use erede\model\EnvironmentType;
use erede\model\RefundRequest;
use erede\model\TransactionKind;
use erede\model\TransactionRequest;
use erede\model\UrlRequest;
use erede\model\UrlKind;

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

$response = $ac->authorize($transactionRequest);
$tid = $response->getTid();

$refundRequest = new RefundRequest();
$refundRequest->setAmount('2000');
$urls = array();
$urlCallback = new UrlRequest();
$urlCallback->setKind(UrlKind::CALLBACK);
$urlCallback->setUrl('https://callback.com');
$urls[] = $urlCallback;
$refundRequest->setUrls($urls);

echo "Request JSON: " . $refundRequest->toJson() . "\n";
$response = $ac->refund($tid, $refundRequest);
echo "Response JSON: " . $response->toJson() . "\n";
echo "RefundId: " . $response->getRefundId();
