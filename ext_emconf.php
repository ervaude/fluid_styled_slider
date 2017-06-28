<?php

$EM_CONF[$_EXTKEY] = [
    'title'              => 'Fluid Styled Slider',
    'description'        => 'A slider Content Element based on fluid_styled_content.',
    'category'           => 'plugin',
    'shy'                => false,
    'version'            => '1.1.3',
    'priority'           => '',
    'loadOrder'          => '',
    'module'             => '',
    'state'              => 'stable',
    'uploadfolder'       => 0,
    'createDirs'         => '',
    'clearcacheonload'   => true,
    'author'             => 'Daniel Goerz',
    'author_email'       => 'ervaude@gmail.com',
    'author_company'     => 'Lightwerk GmbH',
    'CGLcompliance'      => null,
    'CGLcompliance_note' => null,
    'constraints'        => [
        'depends'   => [
            'typo3' => '>= 7.6.0 < 9.0.0',
            'fluid_styled_content' => ''
        ],
        'conflicts' => [],
        'suggests'  => []
    ],
    'autoload' => [
        'psr-4' => [
            'DanielGoerz\\FluidStyledSlider\\' => 'Classes',
        ]
    ]
];
