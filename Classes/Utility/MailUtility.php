<?php

/***
 *
 * This file is part of the "Freifunk knoten Benachrichtigung" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2017 Kevin Quiatkowski <kevin@pinneberg.freifunk.net>
 *
 ***/

namespace FFPI\FfpiNodeUpdates\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailUtility {

    /** @var \TYPO3\CMS\Extbase\Object\ObjectManager */
    var $objectManager = null;

    /** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager */
    var $configurationManager = null;

    public function __construct(){
        $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $this->configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $templateName
     * @param array $vars
     * @param string $language
     * @return int the number of recipients who were accepted for delivery
     */
    public function sendMail($to, $subject, $templateName, $vars=array(), $language=''){

        //Get the Fluid Template
        $template = $this->getTemplate($templateName, $vars);
        //Render the Template to get the mail body
        $emailBody = $template->render();
        //Create the email object
        /** @var \TYPO3\CMS\Core\Mail\MailMessage $email */
        $email = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Mail\MailMessage::class);
        //Set mail data
        $email->setSubject($subject);
        $email->setFrom(array('service@pinneberg.freifunk.net' => 'Freifunk Pinneberg'));
        $email->setTo($to);
        $email->setBody($emailBody);

        //Send mail
        $send = $email->send();

        return $send;
    }

    /**
     * @param string $template
     * @param array $vars
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     */
    private function getTemplate($template, $vars){
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
        $emailView = $this->objectManager->get(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
        $templatePathAndFilename = $templateRootPath . '/' . $template;
        $emailView->setTemplatePathAndFilename($templatePathAndFilename);
        $emailView->assignMultiple($vars);

        return $emailView;

    }
}