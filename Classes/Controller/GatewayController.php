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

use FFPI\FfpiNodeUpdates\Domain\Repository\GatewayRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class GatewayController extends ActionController
{
    /**
     * gatewayRepository
     *
     * @var GatewayRepository
     *
     */
    protected $gatewayRepository = null;

    /**
     * @param GatewayRepository $gatewayRepository
     */
    public function injectNodeRepository(GatewayRepository $gatewayRepository): void
    {
        $this->gatewayRepository = $gatewayRepository;
    }

    public function overviewAction(): void
    {
        $gateways = $this->gatewayRepository->findAll();
        $this->view->assign('gateways', $gateways);
    }
}
