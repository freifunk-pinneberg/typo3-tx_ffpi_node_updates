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
            'FfpiNodeUpdates',
            'Nodeabo',
            [
                \FFPI\FfpiNodeUpdates\Controller\AboController::class => 'new, create, removeForm, confirm, remove',
                \FFPI\FfpiNodeUpdates\Controller\NodeController::class => 'list, show'
            ],
            // non-cacheable actions
            [
                \FFPI\FfpiNodeUpdates\Controller\AboController::class => 'create, remove, confirm',
                \FFPI\FfpiNodeUpdates\Controller\NodeController::class => ''
            ]
        );

        TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'FfpiNodeUpdates',
            'Gatewayhealth',
            [
                \FFPI\FfpiNodeUpdates\Controller\GatewayController::class => 'overview'
            ],
            // non-cacheable actions
            [
                \FFPI\FfpiNodeUpdates\Controller\GatewayController::class => ''
            ]
        );

        TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'FfpiNodeUpdates',
            'Freifunkapifile',
            [
                \FFPI\FfpiNodeUpdates\Controller\FreifunkapifileController::class => 'show'
            ],
            // non-cacheable actions
            [
                \FFPI\FfpiNodeUpdates\Controller\FreifunkapifileController::class => ''
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
