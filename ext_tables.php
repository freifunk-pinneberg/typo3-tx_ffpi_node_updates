<?php

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function ($extKey) {

        TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'FFPI.FfpiNodeUpdates',
            'Nodeabo',
            'Node Abo'
        );

        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript', 'Freifunk knoten Benachrichtigung');

        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_ffpinodeupdates_domain_model_node', 'EXT:ffpi_node_updates/Resources/Private/Language/locallang_csh_tx_ffpinodeupdates_domain_model_node.xlf');
        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_ffpinodeupdates_domain_model_node');

        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_ffpinodeupdates_domain_model_abo', 'EXT:ffpi_node_updates/Resources/Private/Language/locallang_csh_tx_ffpinodeupdates_domain_model_abo.xlf');
        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_ffpinodeupdates_domain_model_abo');

    },
    $_EXTKEY
);
