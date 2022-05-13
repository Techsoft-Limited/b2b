<?php
    include "config.php";
    include "b2bAutoloader.php";

    $b2b = new b2bAutoloader();

    /*
     * @amount Amount to be sent, It can only be positive
     * @recipient Beneficially account of the amount
     * @reference Payment reference generated by your system
     * @description Internal description of the transaction if any
     * This method returns object
     *
     * $b2b->auth()->disbursement($config);
     */

    $config = [
        'amount'        => 1000,
        'recipient'     => '',
        'reference'     => 'B2B-'.time(),
        'description'   => ''
    ];

    $payment = $b2b->auth()->disbursement($config); // Initiate the payment call
    echo json_encode($payment);