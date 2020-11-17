<?php
/**
 * @see https://github.com/Edujugon/PushNotification
 */

return [
    'gcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'apiKey' => 'My_ApiKey',
    ],
    'fcm' => [
        'priority' => 'high',
        'dry_run' => false,
        'apiKey' => 'AAAAJN1IhPU:APA91bGYz-St0Wzc-Ni_ITCTNAvhAeX-h3oZwgtptJ6PQD3V5RNyIh5ECSzZ17wRCP46zUXiYvp7lYfvwzkmzZ3liZpCV1w12_Uh8yP6ytLT6jPNXKrwn-Kz6zpIcnGWiChWuUCRDmq4',
    ],
    'apn' => [
        'certificate' => __DIR__ . '/iosCertificates/Fundi_dist.pem',
        'passPhrase' => '123456', //Optional
        //'passFile' => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
        'dry_run' => false, //if dist so make as false , if dev pem file so true
    ],
];
