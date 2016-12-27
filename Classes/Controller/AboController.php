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

namespace FFPI\FfpiNodeUpdates\Controller;

use FFPI\FfpiNodeUpdates\Domain\Model\Abo;
use FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository;
use FFPI\FfpiNodeUpdates\Domain\Model\Dto\AboNewDemand;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * AboController
 */
class AboController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * aboRepository
     *
     * @var \FFPI\FfpiNodeUpdates\Domain\Repository\AboRepository
     * @inject
     */
    protected $aboRepository = null;

    /**
     * nodeRepository
     *
     * @var \FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository
     * @inject
     */
    protected $nodeRepository = null;

    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
        $aboNewDemand = GeneralUtility::makeInstance(AboNewDemand::class);
        $this->view->assign('demand', $aboNewDemand);
        $this->view->assign('nodes', $this->nodeRepository->findAll());
    }

    /**
     * action create
     *
     * @param \FFPI\FfpiNodeUpdates\Domain\Model\Dto\AboNewDemand $aboNewDemand
     * @donotvalidate $aboNewDemand
     * @return void
     */
    public function createAction($aboNewDemand)
    {
        $newAbo = GeneralUtility::makeInstance(Abo::class);
        $newAbo->setEmail($aboNewDemand->getEmail());
        $newAbo->setNode($this->nodeRepository->findOneByNodeId($aboNewDemand->getNodeId()));
        $secret = substr(md5(openssl_random_pseudo_bytes(10)), 0, 10);
        $newAbo->setSecret($secret);
        $this->aboRepository->add($newAbo);

        //Wir brauchen für die E-Mail eine Besätigungs URL
        $pid = $this->uriBuilder->getTargetPageUid();
        $urlAttributes = array();
        $urlAttributes['tx_ffpinodeupdates_nodeabo[action]'] = 'confirm';
        $urlAttributes['tx_ffpinodeupdates_nodeabo[controller]'] = 'Abo';
        $urlAttributes['tx_ffpinodeupdates_nodeabo[email]'] = $newAbo->getEmail();
        $urlAttributes['tx_ffpinodeupdates_nodeabo[secret]'] = $secret;
        $url = $this->uriBuilder;
        $url->reset();
        $url->setTargetPageUid($pid);
        $url->setArguments($urlAttributes);
        $url = $url->buildFrontendUri();

        //E-Mail
        $mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
        //Betreff
        $mail->setSubject('Freifunk Pinneberg: Knoten Benachrichtigung');
        //Absender
        $mail->setFrom(array('service@pinneberg.freifunk.net' => 'Freifunk Pinneberg'));
        //Empfänger
        $mail->setTo(array($newAbo->getEmail()));
        //Nachricht
        $mail->setBody("Hallo, \n jemand hat mit dieser E-Mail ein Benachrichtigungsabo für den Freifunk Knoten " . $aboNewDemand->getNodeId() . " eingerichtet. \n Falls du es warst, bestätige dies bitte mit folgendem Link: $url");
        //Senden
        $mail->send();
    }

    public function removeFormAction()
    {
        $args = $this->request->getArguments();
        $secret = $args['secret'];
        $email = $args['email'];
        $this->view->assign('secret', $secret);
        $this->view->assign('email', $email);
    }

    /**
     * action remove
     *
     * @param \FFPI\FfpiNodeUpdates\Domain\Model\Dto\AboRemoveDemand $aboRemoveDemand
     * @return void
     */
    public function removeAction($aboRemoveDemand)
    {
        $originalAbo = $this->aboRepository->findOneBySecret($aboRemoveDemand->getSecret());
        if (!empty($originalAbo) AND $aboRemoveDemand->getEmail() === $originalAbo->getEmail()) {
            $this->addFlashMessage('The object was deleted.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            $this->aboRepository->remove($originalAbo);
            $this->view->assign('removed', true);
        } else {
            $this->view->assign('removed', false);
        }
    }

    /**
     * action confirm
     *
     * @return void
     */
    public function confirmAction()
    {
        $args = $this->request->getArguments();
        #var_dump($args);
        $secret = $args['secret'];
        $email = $args['email'];

        if (!empty($secret) AND !empty($email)) {
            /**
             * @var \FFPI\FfpiNodeUpdates\Domain\Model\Abo $abo
             */
            $abo = $this->aboRepository->findOneBySecret($secret);
            if (!empty($abo) AND $abo->getEmail() == $email) {
                $abo->setConfirmed(true);
                $this->aboRepository->update($abo);
                $this->view->assign('confirmed', true);
            } else {
                $this->view->assign('confirmed', false);
            }
            $this->view->assign('abo', $abo);
        } else {
            $this->view->assign('confirmed', false);
        }
    }

    /**
     * @param \FFPI\FfpiNodeUpdates\Domain\Model\Abo $abo
     * @return bool
     */
    private function checkAbo($abo)
    {
        if (empty($abo->getEmail()) OR empty($abo->getNode())) {
            return false;
        }
        return true;
    }
}
