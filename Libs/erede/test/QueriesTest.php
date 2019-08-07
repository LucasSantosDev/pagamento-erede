<?php

require(dirname(__FILE__).'/../Classloader.php');

use erede\model\EnvironmentType;
use erede\model\RefundRequest;
use erede\model\TransactionKind;
use erede\model\TransactionRequest;
use erede\model\UrlKind;

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
$reference = $response->getReference();

$transactionRequest = new TransactionRequest();
$transactionRequest->setAmount('2000');
$response = $ac->capture($tid, $transactionRequest);

$refundRequest = new RefundRequest();
$refundRequest->setAmount('2000');
$response = $ac->refund($tid, $refundRequest);

$refundId = $response->getRefundId();

$qry = new Query('10000205','c222797e7101493fa9a92a7a555d206e', EnvironmentType::HOMOLOG);

// Transaction by TID
$response = $qry->getTransactionByTid($tid);
echo "Transaction by Tid $tid:\n" . $response->toJson() . "\n\n";

// Transaction by reference
$response = $qry->getTransactionByReference($reference);
echo "Transaction by Reference $reference:\n" . $response->toJson() . "\n\n";

// Refund
$response = $qry->getRefund($tid, $refundId);
echo "Refund by id $refundId:\n" . $response->toJson() . "\n\n";

// List of refunds
$response = $qry->getRefunds($tid);
echo "List of refunds by Tid $tid:\n" . $response->toJson() . "\n\n";
