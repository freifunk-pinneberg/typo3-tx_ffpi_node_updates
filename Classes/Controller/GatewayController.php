<?php


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
    public function injectNodeRepository(GatewayRepository $gatewayRepository)
    {
        $this->gatewayRepository = $gatewayRepository;
    }

    public function overviewAction(){
        $gateways = $this->gatewayRepository->findAll();
        $this->view->assign('gateways', $gateways);
    }
}