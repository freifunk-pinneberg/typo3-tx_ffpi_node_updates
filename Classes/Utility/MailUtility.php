<?php

/***
 *
 * This file is part of the "Freifunk knoten Benachrichtigung" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019 Kevin Quiatkowski <kevin@pinneberg.freifunk.net>
 *
 ***/

namespace FFPI\FfpiNodeUpdates\Utility;

use Throwable;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentContentObject;

class MailUtility
{

    /**
     * @var ObjectManager
     */
    var $objectManager;

    /**
     * @var ConfigurationManager
     */
    var $configurationManager;

    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->configurationManager = $this->objectManager->get(ConfigurationManager::class);
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $templateName
     * @param array $vars
     * @return int the number of recipients who were accepted for delivery
     * @throws Throwable
     */
    public function sendMail(string $to, string $subject, string $templateName, array $vars = array()): int
    {
        //Get the Fluid Template
        $template = $this->getTemplate($templateName, $vars);
        //Render the Template to get the mail body
        $emailBody = $template->render();
        //Create the email object
        /** @var MailMessage $email */
        $email = GeneralUtility::makeInstance(MailMessage::class);
        //Set mail data
        $email->setSubject($subject);
        $email->setFrom(array('service@pinneberg.freifunk.net' => 'Freifunk Pinneberg'));
        $email->setTo($to);
        $email->setBody($emailBody);
        $email->setContentType('text/html');

        //Send mail
        return $email->send();
    }

    /**
     * @param string $template
     * @param array $vars
     * @return StandaloneView
     * @throws Throwable
     */
    private function getTemplate(string $template, array $vars): StandaloneView
    {
        /** @var StandaloneView $emailView */
        $emailView = $this->objectManager->get(StandaloneView::class);

        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'ffpi_node_updates', 'tx_ffpinodeupdates_nodeabo');

        $emailView->getRequest()->setControllerExtensionName('ffpi_node_updates');
        $emailView->getRequest()->setControllerName('mail');
        $view = $this->getTemplatePaths();
        $emailView->setTemplateRootPaths($view['templateRootPaths']);
        $emailView->setPartialRootPaths($view['partialRootPaths']);
        $emailView->setLayoutRootPaths($view['layoutRootPaths']);
        $emailView->setTemplate($template);
        //$templatePathAndFilename = $templateRootPath . '/' . $template;
        //$emailView->setTemplatePathAndFilename($templatePathAndFilename);
        $emailView->assignMultiple($vars);

        return $emailView;
    }

    /**
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    private function getTemplatePaths(): array {
        //Try 1: Try it with configruation Framework. Should work if we are in FE
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'ffpi_node_updates', 'tx_ffpinodeupdates_nodeabo');
        if(isset($extbaseFrameworkConfiguration['view']) && !empty($extbaseFrameworkConfiguration['view']))
        {
            return $extbaseFrameworkConfiguration['view'];
        }

        //Try 2: Get complete TS and use a fixed xpath. Should always work as long as there is valid TS included
        $ts = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        if(isset($ts['plugin.']['tx_ffpinodeupdates_nodeabo.']['view.']) && !empty($ts['plugin.']['tx_ffpinodeupdates_nodeabo.']['view.'])) {
            $view = [];
            $view['templateRootPaths'] = $ts['plugin.']['tx_ffpinodeupdates_nodeabo.']['view.']['templateRootPaths.'];
            $view['partialRootPaths'] = $ts['plugin.']['tx_ffpinodeupdates_nodeabo.']['view.']['partialRootPaths.'];
            $view['layoutRootPaths'] = $ts['plugin.']['tx_ffpinodeupdates_nodeabo.']['view.']['layoutRootPaths.'];
            return $view;
        }

        //Try 3: Give up and use a hardcoded path
        $view = [
            'templateRootPaths' => [
                0 => 'EXT:ffpi_node_updates/Resources/Private/Templates/',
            ],
            'partialRootPaths' => [
                0 => 'EXT:ffpi_node_updates/Resources/Private/Partials/',
            ],
            'layoutRootPaths' => [
                0 => 'EXT:ffpi_node_updates/Resources/Private/Layouts/',
            ],
        ];
        return $view;
    }
}