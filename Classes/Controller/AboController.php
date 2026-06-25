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

use Psr\Http\Message\ResponseInterface;
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
    protected AboRepository $aboRepository;

    protected NodeRepository $nodeRepository;

    public function __construct(AboRepository $aboRepository, NodeRepository $nodeRepository)
    {
        $this->aboRepository = $aboRepository;
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction(): ResponseInterface
    {
        $aboNewDemand = GeneralUtility::makeInstance(AboNewDemand::class);
        $this->view->assign('demand', $aboNewDemand);
        $this->view->assign('nodes', $this->nodeRepository->findAll()->getQuery()->setOrderings(
            [
                'nodeName' => QueryInterface::ORDER_ASCENDING,
                'nodeId' => QueryInterface::ORDER_ASCENDING
            ]
        )->execute());
        return $this->htmlResponse();
    }

    /**
     * action create
     *
     * @param AboNewDemand $aboNewDemand
     * @return void
     * @throws Throwable
     */
    public function createAction(AboNewDemand $aboNewDemand): ResponseInterface
    {
        $newAbo = GeneralUtility::makeInstance(Abo::class);
        $newAbo->setEmail($aboNewDemand->getEmail());
        $newAbo->setNode($this->nodeRepository->findOneByNodeId($aboNewDemand->getNodeId()));
        $randomData = openssl_random_pseudo_bytes(10);
        if($randomData === false or empty($randomData)){
            throw new \RuntimeException('No Random Data available. Unable to create a secret');
        }
        $secret = substr(md5($randomData), 0, 10);
        $newAbo->setSecret($secret);
        $this->aboRepository->add($newAbo);


        $this->sendConfirmEmail($newAbo, $secret);
        return $this->htmlResponse();
    }

    /**
     * @return void
     */
    protected function initializeRemoveFormAction()
    {
        $this->arguments->getArgument('aboRemoveDemand')
            ->getPropertyMappingConfiguration()
            ->allowProperties('email', 'secret');
    }

    /**
     * @param AboRemoveDemand|null $aboRemoveDemand
     * @return void
     * action removeForm
     */
    public function removeFormAction(AboRemoveDemand $aboRemoveDemand = null): ResponseInterface
    {
        if (!($aboRemoveDemand instanceof AboRemoveDemand)) {
            $aboRemoveDemand = new AboRemoveDemand();
        }
        $this->view->assign('aboRemoveDemand', $aboRemoveDemand);
        return $this->htmlResponse();
    }

    /**
     * action remove
     *
     * @param AboRemoveDemand $aboRemoveDemand
     * @return void
     * @throws Throwable
     */
    public function removeAction(AboRemoveDemand $aboRemoveDemand): ResponseInterface
    {
        $originalAbo = $this->aboRepository->findOneBySecret($aboRemoveDemand->getSecret());
        if (!empty($originalAbo) and $aboRemoveDemand->getEmail() === $originalAbo->getEmail()) {
            $this->addFlashMessage('The object was deleted.', '', AbstractMessage::ERROR);
            $this->aboRepository->remove($originalAbo);
            $this->view->assign('removed', true);
        } else {
            $this->view->assign('removed', false);
        }
        return $this->htmlResponse();
    }

    /**
     * action confirm
     *
     * @return void
     * @throws Throwable
     */
    public function confirmAction(): ResponseInterface
    {
        $args = $this->request->getArguments();
        $secret = $args['secret'];
        $email = $args['email'];

        if (!empty($secret) and !empty($email)) {
            /**
             * @var Abo $abo
             */
            $abo = $this->aboRepository->findOneBySecret($secret);
            if (($abo instanceof Abo) && $abo->getEmail() === $email) {
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
        return $this->htmlResponse();
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

        $emailData = [
            'node' => $newAbo->getNode(),
            'url' => $url,
        ];

        //send mail
        $mail = new MailUtility();
        $sendMails = $mail->sendMail($newAbo->getEmail(), 'Freifunk Pinneberg: Knoten Benachrichtigung', 'Mail/ConfirmEmail.html', $emailData);
        return $sendMails;
    }

    /**
     * @param string $email
     * @param string $secret
     * @return string Link
     */
    private function getConfirmLink($email, $secret)
    {
        $pid = $this->uriBuilder->getTargetPageUid();
        $urlAttributes = [];
        $urlAttributes['tx_ffpinodeupdates_nodeabo[action]'] = 'confirm';
        $urlAttributes['tx_ffpinodeupdates_nodeabo[controller]'] = 'Abo';
        $urlAttributes['tx_ffpinodeupdates_nodeabo[email]'] = $email;
        $urlAttributes['tx_ffpinodeupdates_nodeabo[secret]'] = $secret;
        $url = $this->uriBuilder;
        $url->reset();
        if (is_int($pid)) {
            $url->setTargetPageUid($pid);
        }
        $url->setCreateAbsoluteUri(true);
        $url->setArguments($urlAttributes);
        $url = $url->buildFrontendUri();
        return $url;
    }
}
