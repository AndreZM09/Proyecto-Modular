<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Driver
    |--------------------------------------------------------------------------
    |
    | The default driver you would like to use for location retrieval.
    |
    */

    'driver' => env('LOCATION_DRIVER', 'ipapi'),

    /*
    |--------------------------------------------------------------------------
    | Driver Fallbacks
    |--------------------------------------------------------------------------
    |
    | The drivers you want to use to retrieve the users location
    | if the above selected driver is unavailable.
    |
    | These will be called upon in order (first to last).
    |
    */

    'fallbacks' => [
        'ipinfo',
        'maxmind',
    ],

    /*
    |--------------------------------------------------------------------------
    | Position
    |--------------------------------------------------------------------------
    |
    | Here you may configure the position instance that is created
    | and returned from the above drivers. The instance you
    | create must extend the built-in Position class.
    |
    */

    'position' => Stevebauman\Location\Position::class,

    /*
    |--------------------------------------------------------------------------
    | Localhost Testing
    |--------------------------------------------------------------------------
    |
    | If your running your website locally and want to test different
    | IP addresses to see location detection, set this to true.
    |
    */

    'testing' => env('LOCATION_TESTING', false),

    /*
    |--------------------------------------------------------------------------
    | Localhost IP
    |--------------------------------------------------------------------------
    |
    | The IP address used for localhost testing.
    |
    | This is used for testing when LOCATION_TESTING is set to true.
    |
    */

    'testing_ip' => env('LOCATION_TESTING_IP', '66.102.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Maxmind Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration for the maxmind driver.
    |
    | If web service is enabled, you must fill in your user ID and license key.
    |
    | You may also set the `services` array to configure specific maxmind web
    | services. The current services available are: 'country', 'insights', 'city'.
    |
    */

    'maxmind' => [
        'web' => [
            'enabled' => false,
            'user_id' => env('MAXMIND_USER_ID'),
            'license_key' => env('MAXMIND_LICENSE_KEY'),
            'services' => ['city'],
        ],

        'local' => [
            'type' => 'city',
            'path' => database_path('maxmind/GeoLite2-City.mmdb'),
            'url' => sprintf('https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz', env('MAXMIND_LICENSE_KEY')),
        ],
    ],

    'ipapi' => [
        'token' => env('IPAPI_TOKEN'),
    ],

    'ipinfo' => [
        'token' => env('IPINFO_TOKEN'),
    ],

    'ipdata' => [
        'token' => env('IPDATA_TOKEN'),
    ],
];