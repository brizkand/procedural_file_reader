<?php

declare(strict_types=1);

/**
 * Get the path of transaction folder and concatenate the sample.csv file
 */
function getTransactionFile(string $dirPath): array{
    $file = [];

    foreach(scandir($dirPath) as $file){
        if(is_dir($file)){
            continue;
        }
        $files[] =  $dirPath . $file;
    }
    return $files;
}

function getTransactions(string $fileName, ?callable $transactionHandler = null): array{
    if(! file_exists($fileName)){
        trigger_error('File "' . $fileName . '"does not exist', E_USER_ERROR);
    }

    $file = fopen($fileName, 'r');

    /**
     * removing the first line of the csv file
     */
    fgetcsv($file);

    $transactions = [];

    // while(($transaction = fgetcsv($file)) !== false){
    //     $transactions[] = $transaction;
    // }
    while(! feof($file)){
        $transaction = fgetcsv($file);
        if($transactionHandler !== null){
            $transaction = $transactionHandler($transaction);
            $transactions[] = $transaction;
        }else{
            $transactions[] = extractTransactions($transaction);
        }
        
    }

    return $transactions;
}

function extractTransactions(array $transactionRow): array{

    [$date, $checkNumber, $description, $amount] = $transactionRow;
    $amount = (float) str_replace(['$', ','], '', $amount);
    return [
        'date' => $date,
        'checkNumber' => $checkNumber,
        'description' => $description,
        'amount' => $amount
    ];
}

function calculateTotal(array $transactions): array{
    $totals = ['netTotal' => 0, 'totalIncome' => 0, 'totalExpense' => 0];

    foreach($transactions as $transaction){
        $totals['netTotal'] += $transaction['amount'];
        if($transaction['amount'] >= 0){
            $totals['totalIncome'] += $transaction['amount'];
        }else{
            $totals['totalExpense'] += $transaction['amount'];
        }
    }
   

    return $totals;
}