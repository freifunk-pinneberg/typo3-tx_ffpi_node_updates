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

use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use Throwable;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class MailUtility
{

    /**
     * @var ConfigurationManager
     */
    public $configurationManager;

    public function __construct()
    {
        $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $templateName
     * @param array $vars
     * @param array $additionalHeader
     * @return bool are mails send?
     * @throws Throwable
     */
    public function sendMail(string $to, string $subject, string $templateName, array $vars = [], array $additionalHeader = []): bool
    {
        //Get the Fluid Template
        $standaloneView = $this->getTemplate($templateName, $vars);
        //Render the Template to get the mail body
        $emailBody = $standaloneView->render();
        //Create the email object
        /** @var MailMessage $mailMessage */
        $mailMessage = GeneralUtility::makeInstance(MailMessage::class);
        //Set mail data
        $mailMessage->setSubject($subject);
        $mailMessage->setFrom(['service@pinneberg.freifunk.net' => 'Freifunk Pinneberg']);
        $mailMessage->setTo($to);
        if (method_exists($mailMessage, 'setContentType')) {
            $mailMessage->text($emailBody);
            $mailMessage->setContentType('text/html');
        } else {
            $mailMessage->setBody()->html($emailBody);
        }
        $headers = $mailMessage->getHeaders();
        foreach ($additionalHeader as $key => $value) {
            $headers->addTextHeader($key, $value);
        }
        $mailMessage->setHeaders($headers);

        //Send mail
        return $mailMessage->send();
    }

    /**
     * @param string $template
     * @param array $vars
     * @return StandaloneView
     * @throws Throwable
     */
    private function getTemplate(string $template, array $vars): StandaloneView
    {
        /** @var StandaloneView $standaloneView */
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);

        $view = $this->getTemplatePaths();
        $standaloneView->getRenderingContext()->getTemplatePaths()->setTemplateRootPaths($view['templateRootPaths']);
        $standaloneView->getRenderingContext()->getTemplatePaths()->setPartialRootPaths($view['partialRootPaths']);
        $standaloneView->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths($view['layoutRootPaths']);
        $standaloneView->getRenderingContext()->setControllerAction($template);
        //$templatePathAndFilename = $templateRootPath . '/' . $template;
        //$emailView->setTemplatePathAndFilename($templatePathAndFilename);
        $standaloneView->assignMultiple($vars);

        return $standaloneView;
    }

    /**
     * @return array
     * @throws InvalidConfigurationTypeException
     */
    private function getTemplatePaths(): array
    {
        //Try 1: Try it with configruation Framework. Should work if we are in FE
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'ffpi_node_updates', 'tx_ffpinodeupdates_nodeabo');
        if (isset($extbaseFrameworkConfiguration['view']) && !empty($extbaseFrameworkConfiguration['view'])) {
            return $extbaseFrameworkConfiguration['view'];
        }

        //Try 2: Get complete TS and use a fixed xpath. Should always work as long as there is valid TS included
        $ts = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        if (isset($ts['plugin.']['tx_ffpinodeupdates_nodeabo.']['view.']) && !empty($ts['plugin.']['tx_ffpinodeupdates_nodeabo.']['view.'])) {
            return ['templateRootPaths' => $ts['plugin.']['tx_ffpinodeupdates_nodeabo.']['view.']['templateRootPaths.'], 'partialRootPaths' => $ts['plugin.']['tx_ffpinodeupdates_nodeabo.']['view.']['partialRootPaths.'], 'layoutRootPaths' => $ts['plugin.']['tx_ffpinodeupdates_nodeabo.']['view.']['layoutRootPaths.']];
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
