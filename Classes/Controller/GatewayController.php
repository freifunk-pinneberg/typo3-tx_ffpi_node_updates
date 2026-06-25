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
use FFPI\FfpiNodeUpdates\Domain\Repository\GatewayRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class GatewayController extends ActionController
{
    protected GatewayRepository $gatewayRepository;

    public function __construct(GatewayRepository $gatewayRepository)
    {
        $this->gatewayRepository = $gatewayRepository;
    }

    public function overviewAction(): ResponseInterface
    {
        $gateways = $this->gatewayRepository->findAll();
        $this->view->assign('gateways', $gateways);
        return $this->htmlResponse();
    }
}
