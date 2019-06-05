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

use FFPI\FfpiNodeUpdates\Task\ImportTask;
use FFPI\FfpiNodeUpdates\Task\ImportTaskAdditionalFieldProvider;
use FFPI\FfpiNodeUpdates\Task\NotificationTask;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function ($extKey) {

        ExtensionUtility::configurePlugin(
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

    },
    $_EXTKEY
);
// Add task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][NotificationTask::class] = array(
    'extension' => $_EXTKEY,
    'title' => 'Node Status updates',
    'description' => 'Sends notifications',
    'additionalFields' => ''
);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][ImportTask::class] = array(
    'extension' => $_EXTKEY,
    'title' => 'Node Import',
    'description' => 'Imports all Nodes',
    'additionalFields' => ImportTaskAdditionalFieldProvider::class,
);
