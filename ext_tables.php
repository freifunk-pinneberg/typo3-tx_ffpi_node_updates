<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        ExtensionUtility::registerPlugin(
            'FFPI.FfpiNodeUpdates',
            'Nodeabo',
            'Node Abo'
        );

        ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript', 'Freifunk knoten Benachrichtigung');

        ExtensionManagementUtility::addLLrefForTCAdescr('tx_ffpinodeupdates_domain_model_node', 'EXT:ffpi_node_updates/Resources/Private/Language/locallang_csh_tx_ffpinodeupdates_domain_model_node.xlf');
        ExtensionManagementUtility::allowTableOnStandardPages('tx_ffpinodeupdates_domain_model_node');

        ExtensionManagementUtility::addLLrefForTCAdescr('tx_ffpinodeupdates_domain_model_abo', 'EXT:ffpi_node_updates/Resources/Private/Language/locallang_csh_tx_ffpinodeupdates_domain_model_abo.xlf');
        ExtensionManagementUtility::allowTableOnStandardPages('tx_ffpinodeupdates_domain_model_abo');

    },
    $_EXTKEY
);
