<?php

require(dirname(__FILE__).'/../Classloader.php');

use erede\model\EnvironmentType;
use erede\model\TransactionKind;
use erede\model\TransactionRequest;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$ac = new Acquirer('10000205','c222797e7101493fa9a92a7a555d206e', EnvironmentType::HOMOLOG);

$transactionRequest = new TransactionRequest();
$transactionRequest->setCapture('false');
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

$transactionRequest = new TransactionRequest();
$transactionRequest->setAmount('1000');

echo "Request JSON: " . $transactionRequest->toJson() . "\n";
$response = $ac->capture($tid, $transactionRequest);
echo "Response JSON: " . $response->toJson() . "\n";
