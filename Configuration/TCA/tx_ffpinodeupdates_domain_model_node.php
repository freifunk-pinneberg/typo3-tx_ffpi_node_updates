<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_node',
        'label' => 'node_name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => false,

        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'node_id,node_name,role,online,last_change,',
        'iconfile' => 'EXT:ffpi_node_updates/Resources/Public/Icons/wifi-router.svg',
    ],
    'types' => [
        '1' => ['showitem' => 'hidden,node_id,node_name,online,role,last_change,domain,hardware_model,first_seen,last_seen,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,starttime,endtime'],
    ],
    'columns' => [
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.enabled'
                    ]
                ],
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 13,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 13,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],
        'node_id' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_node.node_id',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],

        ],
        'node_name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_node.node_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'role' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_node.role',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'domain' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_node.domain',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'hardware_model' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_node.hardware_model',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'online' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_node.online',
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.enabled'
                    ]
                ],
                'default' => 0
            ]
        ],
        'last_change' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_node.last_change',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 10,
                'eval' => 'datetime',
                'checkbox' => 1,
                'default' => time()
            ],
        ],
        'first_seen' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_node.first_seen',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 10,
                'eval' => 'datetime',
                'checkbox' => 1,
                'default' => time()
            ],
        ],
        'last_seen' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_node.last_seen',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 10,
                'eval' => 'datetime',
                'checkbox' => 1,
                'default' => time()
            ],
        ],
    ],
];
