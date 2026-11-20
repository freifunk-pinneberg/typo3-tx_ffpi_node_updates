<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use FFPI\FfpiNodeUpdates\Controller\AboController;
use FFPI\FfpiNodeUpdates\Controller\NodeController;
use FFPI\FfpiNodeUpdates\Controller\GatewayController;
use FFPI\FfpiNodeUpdates\Controller\FreifunkapifileController;
use FFPI\FfpiNodeUpdates\Task\NotificationTask;
use FFPI\FfpiNodeUpdates\Task\NotificationTaskAdditionalFieldProvider;
use FFPI\FfpiNodeUpdates\Task\ImportTask;
use FFPI\FfpiNodeUpdates\Task\ImportTaskAdditionalFieldProvider;
use FFPI\FfpiNodeUpdates\Task\GatewayUpdateTask;
use FFPI\FfpiNodeUpdates\Task\GatewayUpdateTaskAdditionalFieldProvider;

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

defined('TYPO3') || die('Access denied.');

call_user_func(
    function ($extKey) {

        ExtensionUtility::configurePlugin(
            'FfpiNodeUpdates',
            'Nodeabo',
            [
                AboController::class => 'new, create, removeForm, confirm, remove',
                NodeController::class => 'list, show'
            ],
            // non-cacheable actions
            [
                AboController::class => 'create, remove, confirm',
                NodeController::class => ''
            ],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        ExtensionUtility::configurePlugin(
            'FfpiNodeUpdates',
            'Gatewayhealth',
            [
                GatewayController::class => 'overview'
            ],
            // non-cacheable actions
            [
                GatewayController::class => ''
            ],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        ExtensionUtility::configurePlugin(
            'FfpiNodeUpdates',
            'Freifunkapifile',
            [
                FreifunkapifileController::class => 'show'
            ],
            // non-cacheable actions
            [
                FreifunkapifileController::class => ''
            ],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

    },
    'ffpi_node_updates'
);

// Add task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][NotificationTask::class] = [
    'extension' => 'ffpi_node_updates',
    'title' => 'Node Status updates',
    'description' => 'Sends notifications',
    'additionalFields' => NotificationTaskAdditionalFieldProvider::class,
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][ImportTask::class] = [
    'extension' => 'ffpi_node_updates',
    'title' => 'Node Import',
    'description' => 'Imports all Nodes',
    'additionalFields' => ImportTaskAdditionalFieldProvider::class,
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][GatewayUpdateTask::class] = [
    'extension' => 'ffpi_node_updates',
    'title' => 'Gateway Update',
    'description' => 'Updates the gateways',
    'additionalFields' => GatewayUpdateTaskAdditionalFieldProvider::class,
];
