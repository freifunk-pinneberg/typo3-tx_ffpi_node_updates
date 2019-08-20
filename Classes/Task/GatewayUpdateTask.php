<?php

namespace FFPI\FfpiNodeUpdates\Task;

use FFPI\FfpiNodeUpdates\Domain\Model\Gateway;
use FFPI\FfpiNodeUpdates\Domain\Repository\GatewayRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class GatewayUpdateTask extends AbstractTask
{
    /** @var GatewayRepository */
    protected $gatewayRepository;

    /** @var ObjectManager */
    protected $objectManager;

    protected function inistalizeTask(){
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->gatewayRepository = $this->objectManager->get(GatewayRepository::class);
    }

    public function execute()
    {
        $this->inistalizeTask();

        /** @var Gateway[] $gateways */
        $gateways = $this->gatewayRepository->findAll();

        foreach ($gateways as $gateway){
            $gatewayData = $this->getGatewayData($gateway->getHttpAdress());
            $gateway->setPing($gatewayData['ping']);
        }
    }

    protected function getGatewayData(string $url): array {
        $ret['ping'] = $this->ping(parse_url($url, PHP_URL_HOST));
    }

    protected function ping(string $host): ?int {

    }
}