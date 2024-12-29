<?php
return [
    'ui.entity-selector' => [
        'value' => [
            'entities' => [
                [
                    'entityId' => 'awzutm-user',
                    'provider' => [
                        'moduleId' => 'awz.utm',
                        'className' => '\\Awz\\Utm\\Access\\EntitySelectors\\User'
                    ],
                ],
                [
                    'entityId' => 'awzutm-group',
                    'provider' => [
                        'moduleId' => 'awz.utm',
                        'className' => '\\Awz\\Utm\\Access\\EntitySelectors\\Group'
                    ],
                ],
            ]
        ],
        'readonly' => true,
    ]
];