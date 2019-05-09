<?php

$EM_CONF[$_EXTKEY] = [
    'title'              => 'Fluid Styled Slider',
    'description'        => 'A slider Content Element based on fluid_styled_content.',
    'category'           => 'plugin',
    'shy'                => false,
    'version'            => '2.0.0',
    'priority'           => '',
    'loadOrder'          => '',
    'module'             => '',
    'state'              => 'stable',
    'uploadfolder'       => 0,
    'createDirs'         => '',
    'clearcacheonload'   => true,
    'author'             => 'Daniel Goerz',
    'author_email'       => 'ervaude@posteo.de',
    'author_company'     => 'B13',
    'CGLcompliance'      => null,
    'CGLcompliance_note' => null,
    'constraints'        => [
        'depends'   => [
            'typo3' => '>= 8.7.0 < 9.5.99',
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
