<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'UniqueAliasMapper',
    'description' => 'Allows to configure unique alias mappers just like RealURL did',
    'category' => 'fe',
    'version' => '1.1.1',
    'state' => 'stable',
    'clearcacheonload' => 1,
    'author' => 'Benni Mack',
    'author_email' => 'typo3@b13.com',
    'author_company' => 'b13 GmbH',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-11.5.99',
        ],
    ],
];
