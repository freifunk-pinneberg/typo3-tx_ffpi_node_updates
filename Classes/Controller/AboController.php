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

        //E-Mail
        $mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
        //Betreff
        $mail->setSubject('Freifunk Pinneberg: Knoten Benachrichtigung');
        //Absender
        $mail->setFrom(array('service@pinneberg.freifunk.net' => 'Freifunk Pinneberg'));
        //Empfänger
        $mail->setTo(array($newAbo->getEmail()));
        //Nachricht
        $mail->setBody("Test Nachricht. \n Secret: " . $secret);
        //Senden
        $mail->send();
    }

    /**
     * action delete
     *
     * @param \FFPI\FfpiNodeUpdates\Domain\Model\Abo $abo
     * @return void
     */
    public function deleteAction(\FFPI\FfpiNodeUpdates\Domain\Model\Abo $abo)
    {
        $uid = $abo->getUid();
        $originalAbo = $this->aboRepository->findByUid($uid);
        if ($abo->getSecret() === $originalAbo->getSecret()) {
            $this->addFlashMessage('The object was deleted.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            $this->aboRepository->remove($abo);
        }
        #$this->redirect('list');
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
