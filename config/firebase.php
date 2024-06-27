<?php
/**
 * @author tmtuan
 * created Date: 29-Mar-21
 */
return [
    'sale_projectId' => 'citigym-44c8b',
    'member_projectId' => 'citi-gym-member',
    'server_key' => env('FIREBASE_SERVER_KEY', 'AAAAMB7_L5M:APA91bHR4dj9M9NAeB4hvCynppsK5xrENtKsmZZJhNUOpiH22E2XJvsDu_w1GBUgoWXpG1MZEpgWePPSyL8VZnPduiHFMw-zFOWQsGSHXR1emH5Xw0nbLI3aBhZfmFCytNMwBeNUovhf'),
    'device_type' => [
        'ios' => 'iOS',
        'android' => 'android'
    ],
    'sound' => 'default',

    'message_group' => [
        'member_app' =>[
            'all', // all user that using member app
            'member', // all member
            'ms', //all MS user
        ],

        'sale_app' => [
            'all',// all user that using sales/pt app
            'user_sale', // all user that using sales/pt app and had role Sale
            'user_pt', // all user that using sales/pt app and hd role PT
        ],

    ],
    'sale_client_path' => storage_path('google/citigym-fcm.json'),
    'member_client_path' => storage_path('google/citi-gym-member-fcm.json'),
    'scopes' => [
        'https://www.googleapis.com/auth/firebase.messaging'
    ],
    'api_endpoint' => 'https://fcm.googleapis.com/v1/projects/'
];
