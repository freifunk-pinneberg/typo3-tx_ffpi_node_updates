<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_gateway',
        'label' => 'node',
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
        'searchFields' => 'node,open_vpn',
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/apps/apps-filetree-mount.svg'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, http_adress, node, ping, open_vpn, network_interface, firewall, exit_vpn',
    ],
    'types' => [
        '1' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, http_adress, node, ping, open_vpn, network_interface, firewall, exit_vpn,  --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime'],
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
                'foreign_table' => 'tx_ffpinodeupdates_domain_model_gateway',
                'foreign_table_where' => 'AND tx_ffpinodeupdates_domain_model_gateway.pid=###CURRENT_PID### AND tx_ffpinodeupdates_domain_model_gateway.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
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
        'http_adress' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_gateway.http_adress',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputLink',
            ]
        ],
        'node' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_gateway.node',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_ffpinodeupdates_domain_model_node',
                'foreign_table_where' => 'AND role = \'gate\'',
                'minitems' => 1,
                'maxitems' => 1,
            ],

        ],
        'ping' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_gateway.ping',
            'config' => [
                'type' => 'input',
                'eval' => 'double2'
            ]
        ],
        'open_vpn' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_gateway.open_vpn',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Unbekannt', \FFPI\FfpiNodeUpdates\Domain\Model\Gateway::STATE_UNKNOWN],
                    ['OK', \FFPI\FfpiNodeUpdates\Domain\Model\Gateway::STATE_OK],
                    ['Fehler', \FFPI\FfpiNodeUpdates\Domain\Model\Gateway::STATE_ERROR],
                ],
            ]
        ],
        'network_interface' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_gateway.network_interface',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Unbekannt', \FFPI\FfpiNodeUpdates\Domain\Model\Gateway::STATE_UNKNOWN],
                    ['OK', \FFPI\FfpiNodeUpdates\Domain\Model\Gateway::STATE_OK],
                    ['Fehler', \FFPI\FfpiNodeUpdates\Domain\Model\Gateway::STATE_ERROR],
                ],
            ]
        ],
        'firewall' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_gateway.firewall',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Unbekannt', \FFPI\FfpiNodeUpdates\Domain\Model\Gateway::STATE_UNKNOWN],
                    ['OK', \FFPI\FfpiNodeUpdates\Domain\Model\Gateway::STATE_OK],
                    ['Fehler', \FFPI\FfpiNodeUpdates\Domain\Model\Gateway::STATE_ERROR],
                ],
            ]
        ],
        'exit_vpn' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_gateway.exit_vpn',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Unbekannt', \FFPI\FfpiNodeUpdates\Domain\Model\Gateway::STATE_UNKNOWN],
                    ['OK', \FFPI\FfpiNodeUpdates\Domain\Model\Gateway::STATE_OK],
                    ['Fehler', \FFPI\FfpiNodeUpdates\Domain\Model\Gateway::STATE_ERROR],
                ],
            ]
        ],
        'last_health_check' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_gateway.last_health_check',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 10,
                'eval' => 'datetime',
                'checkbox' => 1,
                'default' => time()
            ],
        ],
        'last_health_change' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ffpi_node_updates/Resources/Private/Language/locallang.xlf:tx_ffpinodeupdates_domain_model_gateway.last_health_change',
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
