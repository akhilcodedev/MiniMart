<?php

return [


    /*
     * List of Database Migration Seeders to be run in the order.
     *
     */
    'seeders' => [

        \Modules\CMS\Database\Seeders\LanguageTableSeeder::class,
        \Modules\CMS\Database\Seeders\EmailTemplateSeeder::class,
    ],

];
