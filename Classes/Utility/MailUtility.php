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
     * @param string $language
     * @return int the number of recipients who were accepted for delivery
     * @throws Throwable
     */
    public function sendMail(string $to, string $subject, string $templateName, array $vars = array(), string $language = ''): int
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
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        //$templateRootPaths = $extbaseFrameworkConfiguration['view']['templateRootPaths'];
        $emailView->setTemplateRootPaths($extbaseFrameworkConfiguration['view']['templateRootPaths']);
        $emailView->setPartialRootPaths($extbaseFrameworkConfiguration['view']['partialRootPaths']);
        $emailView->setLayoutRootPaths($extbaseFrameworkConfiguration['view']['layoutRootPaths']);
        $emailView->setTemplate($template);
        //$templatePathAndFilename = $templateRootPath . '/' . $template;
        //$emailView->setTemplatePathAndFilename($templatePathAndFilename);
        $emailView->assignMultiple($vars);

        return $emailView;
    }
}