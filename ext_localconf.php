<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function ($extKey) {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
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
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\FFPI\FfpiNodeUpdates\Task\NotificationTask::class] = array(
    'extension' => $_EXTKEY,
    'title' => 'Node Status updates',
    'description' => 'Sends notifications',
    'additionalFields' => ''
);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\FFPI\FfpiNodeUpdates\Task\ImportTask::class] = array(
    'extension' => $_EXTKEY,
    'title' => 'Node Import',
    'description' => 'Imports all Nodes',
    'additionalFields' => \FFPI\FfpiNodeUpdates\Task\ImportTaskAdditionalFieldProvider::class,
);
