<?php
    include "config.php";
    include "b2bAutoloader.php";

    /*
     * @reference Payment reference generated by your system which you later sent to us
     * This method returns object
     *
     * $b2b->auth()->status($config);
     */

    $b2b = new b2bAutoloader();

    $config = [
        'reference'     => 'B2B-Ref-1'
    ];

    $status = $b2b->auth()->status($config);
    echo json_encode($status);