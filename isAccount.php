<?php
    include "config.php";
    include "b2bAutoloader.php";

    $b2b = new b2bAutoloader();

    /*
     * There are only two kinds of valid kakupay accounts which this API can check
     * Group Account; A group account is an 4 digit account which belongs to a group on kakupay platform - 1040/5012
     * Personal Account; Personal account is a 10 digit account possibly a local phone number - 0992026866/0888575055
     * @return json formatted data
     *
     */
    $config = [
        'recipient' => ''
    ];

    $account = $b2b->auth()->isAccount($config);
    echo json_encode($account);