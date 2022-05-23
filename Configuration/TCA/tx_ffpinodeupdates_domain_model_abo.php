<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_abo',
        'label' => 'email',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => false,

        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'email,confirmed,last_notification,secret,node,',
        'iconfile' => 'EXT:install/Resources/Public/Icons/modules/install-test-mail.svg'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, email, confirmed, last_notification, secret, node',
    ],
    'types' => [
        '1' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, email, confirmed, last_notification, secret, node, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime'],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ]
                ],
                'default' => 0,
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_ffpinodeupdates_domain_model_abo',
                'foreign_table_where' => 'AND tx_ffpinodeupdates_domain_model_abo.pid=###CURRENT_PID### AND tx_ffpinodeupdates_domain_model_abo.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
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
                        '0' => 'LLL:EXT:lang/locallang_core.xlf:labels.enabled'
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

        'email' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_abo.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],

        ],
        'confirmed' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_abo.confirmed',
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:lang/locallang_core.xlf:labels.enabled'
                    ]
                ],
                'default' => 0
            ]

        ],
        'last_notification' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_abo.last_notification',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 10,
                'eval' => 'datetime',
                'checkbox' => 1,
                'default' => time()
            ],

        ],
        'secret' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_abo.secret',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],

        ],
        'node' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_abo.node',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_ffpinodeupdates_domain_model_node',
                'minitems' => 0,
            ],

        ],
    ],
];
