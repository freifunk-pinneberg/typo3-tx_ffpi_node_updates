<?php

/***
 *
 * This file is part of the "Freifunk knoten Benachrichtigung" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2016 Kevin Quiatkowski <kevin@pinneberg.freifunk.net>
 *
 ***/

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function ($extKey) {

        TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'FFPI.FfpiNodeUpdates',
            'Nodeabo',
            [
                'Abo' => 'new, create, removeForm, confirm, remove',
                'Node' => 'list, show'
            ],
            // non-cacheable actions
            [
                'Abo' => 'create, remove, confirm',
                'Node' => ''
            ]
        );

        TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'FFPI.FfpiNodeUpdates',
            'Gatewayhealth',
            [
                'Gateway' => 'overview'
            ],
            // non-cacheable actions
            [
                'Gateway' => ''
            ]
        );

        TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'FFPI.FfpiNodeUpdates',
            'Freifunkapifile',
            [
                'Freifunkapifile' => 'show'
            ],
            // non-cacheable actions
            [
                'Freifunkapifile' => ''
            ]
        );

    },
    'ffpi_node_updates'
);

// Add task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][FFPI\FfpiNodeUpdates\Task\NotificationTask::class] = [
    'extension' => 'ffpi_node_updates',
    'title' => 'Node Status updates',
    'description' => 'Sends notifications',
    'additionalFields' => FFPI\FfpiNodeUpdates\Task\NotificationTaskAdditionalFieldProvider::class,
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][FFPI\FfpiNodeUpdates\Task\ImportTask::class] = [
    'extension' => 'ffpi_node_updates',
    'title' => 'Node Import',
    'description' => 'Imports all Nodes',
    'additionalFields' => FFPI\FfpiNodeUpdates\Task\ImportTaskAdditionalFieldProvider::class,
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][FFPI\FfpiNodeUpdates\Task\GatewayUpdateTask::class] = [
    'extension' => 'ffpi_node_updates',
    'title' => 'Gateway Update',
    'description' => 'Updates the gateways',
    'additionalFields' => FFPI\FfpiNodeUpdates\Task\GatewayUpdateTaskAdditionalFieldProvider::class,
];
