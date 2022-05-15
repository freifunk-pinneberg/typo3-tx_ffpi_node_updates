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

namespace FFPI\FfpiNodeUpdates\Controller;

use FFPI\FfpiNodeUpdates\Domain\Model\Abo;
use FFPI\FfpiNodeUpdates\Domain\Model\Dto\AboRemoveDemand;
use FFPI\FfpiNodeUpdates\Domain\Model\Dto\AboNewDemand;
use FFPI\FfpiNodeUpdates\Domain\Repository\AboRepository;
use FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository;
use FFPI\FfpiNodeUpdates\Utility\MailUtility;
use Throwable;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * AboController
 */
class AboController extends ActionController
{
    /**
     * aboRepository
     *
     * @var AboRepository
     *
     */
    protected $aboRepository;

    /**
     * nodeRepository
     *
     * @var NodeRepository
     *
     */
    protected $nodeRepository;

    /**
     * @param AboRepository $aboRepository
     */
    public function injectAboRepository(AboRepository $aboRepository)
    {
        $this->aboRepository = $aboRepository;
    }

    /**
     * @param NodeRepository $nodeRepository
     */
    public function injectNodeRepository(NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
        $aboNewDemand = GeneralUtility::makeInstance(AboNewDemand::class);
        $this->view->assign('demand', $aboNewDemand);
        $this->view->assign('nodes', $this->nodeRepository->findAll()->getQuery()->setOrderings(
            [
                'nodeName' => QueryInterface::ORDER_ASCENDING,
                'nodeId' => QueryInterface::ORDER_ASCENDING
            ]
        )->execute());
    }

    /**
     * action create
     *
     * @param AboNewDemand $aboNewDemand
     * @return void
     * @throws Throwable
     */
    public function createAction(AboNewDemand $aboNewDemand)
    {
        $newAbo = GeneralUtility::makeInstance(Abo::class);
        $newAbo->setEmail($aboNewDemand->getEmail());
        $newAbo->setNode($this->nodeRepository->findOneByNodeId($aboNewDemand->getNodeId()));
        $secret = substr(md5(openssl_random_pseudo_bytes(10)), 0, 10);
        $newAbo->setSecret($secret);
        $this->aboRepository->add($newAbo);


        $this->sendConfirmEmail($newAbo, $secret);
    }

    /**
     *
     */
    protected function initializeRemoveFormAction(){
        /** @var \TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = $this->arguments['aboRemoveDemand']->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowProperties('email', 'secret');
    }

    /**
     * @param \FFPI\FfpiNodeUpdates\Domain\Model\Dto\AboRemoveDemand $aboRemoveDemand
     * action removeForm
     */
    public function removeFormAction($aboRemoveDemand = null)
    {
        if (!($aboRemoveDemand instanceof AboRemoveDemand)) {
            $aboRemoveDemand = new AboRemoveDemand();
        }
        $this->view->assign('aboRemoveDemand', $aboRemoveDemand);
    }

    /**
     * action remove
     *
     * @param \FFPI\FfpiNodeUpdates\Domain\Model\Dto\AboRemoveDemand $aboRemoveDemand
     * @return void
     * @throws Throwable
     */
    public function removeAction(AboRemoveDemand $aboRemoveDemand)
    {
        $originalAbo = $this->aboRepository->findOneBySecret($aboRemoveDemand->getSecret());
        if (!empty($originalAbo) AND $aboRemoveDemand->getEmail() === $originalAbo->getEmail()) {
            $this->addFlashMessage('The object was deleted.', '', AbstractMessage::ERROR);
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
     * @throws Throwable
     */
    public function confirmAction()
    {
        $args = $this->request->getArguments();
        $secret = $args['secret'];
        $email = $args['email'];

        if (!empty($secret) AND !empty($email)) {
            /**
             * @var Abo $abo
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
     * @param Abo $newAbo
     * @param string $secret
     * @return bool
     * @throws Throwable
     */
    private function sendConfirmEmail(Abo $newAbo, string $secret): bool
    {
        //Wir brauchen für die E-Mail eine Besätigungs URL
        $url = $this->getConfirmLink($newAbo->getEmail(), $secret);

        $emailData = array(
            'node' => $newAbo->getNode(),
            'url' => $url,
        );

        //send mail
        $mail = new MailUtility();
        $mail->sendMail($newAbo->getEmail(), 'Freifunk Pinneberg: Knoten Benachrichtigung', 'Mail/ConfirmEmail.html', $emailData);
        if ($mail >= 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $email
     * @param string $secret
     * @return string Link
     */
    private function getConfirmLink($email, $secret)
    {
        $pid = $this->uriBuilder->getTargetPageUid();
        $urlAttributes = array();
        $urlAttributes['tx_ffpinodeupdates_nodeabo[action]'] = 'confirm';
        $urlAttributes['tx_ffpinodeupdates_nodeabo[controller]'] = 'Abo';
        $urlAttributes['tx_ffpinodeupdates_nodeabo[email]'] = $email;
        $urlAttributes['tx_ffpinodeupdates_nodeabo[secret]'] = $secret;
        $url = $this->uriBuilder;
        $url->reset();
        $url->setTargetPageUid($pid);
        $url->setCreateAbsoluteUri(true);
        $url->setArguments($urlAttributes);
        $url = $url->buildFrontendUri();
        return $url;
    }

    /**
     * @param Abo $abo
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
