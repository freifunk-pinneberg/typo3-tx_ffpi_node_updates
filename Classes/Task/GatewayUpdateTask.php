<?php

namespace FFPI\FfpiNodeUpdates\Task;

use FFPI\FfpiNodeUpdates\Domain\Model\Gateway;
use FFPI\FfpiNodeUpdates\Domain\Repository\GatewayRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class GatewayUpdateTask extends AbstractTask
{
    /** @var GatewayRepository */
    protected $gatewayRepository;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var PersistenceManager */
    protected $persistenceManager;

    protected function inistalizeTask()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->gatewayRepository = $this->objectManager->get(GatewayRepository::class);
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
    }

    /**
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function execute()
    {
        $this->inistalizeTask();

        /** @var Gateway[] $gateways */
        $gateways = $this->gatewayRepository->findAll();

        foreach ($gateways as $gateway) {
            if (empty($gateway->getHttpAdress())) {
                continue;
            }
            $gatewayData = $this->getGatewayData($gateway->getHttpAdress());
            $gateway = $this->updateGatewayObject($gateway, $gatewayData);
            $this->gatewayRepository->update($gateway);
        }
        $this->persistenceManager->persistAll();

        return true;
    }

    /**
     * @param string $url
     * @return array
     */
    protected function getGatewayData(string $url): array
    {
        $ret['ping'] = $this->ping(parse_url($url, PHP_URL_HOST));
        $ffgateCheck = $this->getFfgateCheckData($url);
        $ffgateCheck = $this->formatGatewayData($ffgateCheck);
        return array_merge($ret, $ffgateCheck);
    }

    /**
     * @param string $host
     * @return int|null
     */
    protected function ping(string $host): ?int
    {
        //@TODO: Implement Ping
        return null;
    }

    /**
     * @param string $url
     * @return array
     */
    protected function getFfgateCheckData(string $url): array
    {
        $file = file_get_contents($url);
        if ($file === false) {
            return [];
        }

        $statusArray = preg_split('/\r\n|\r|\n/', $file);
        $ret = [];
        foreach ($statusArray as $status) {
            $status = explode(':', $status);
            $ret[trim($status[0])] = trim($status[1]);
        }
        return $ret;
    }

    /**
     * @param array $gatewayData
     * @return array
     */
    protected function formatGatewayData(array $gatewayData): array
    {
        $ret = [];
        foreach ($gatewayData as $key => $value) {
            switch ($key) {
                case 'OpenVPN process':
                    $newKey = 'openVpn';
                    break;
                case 'Interface mullvad':
                    $newKey = 'networkInterface';
                    break;
                case 'Firewall-Interface':
                    $newKey = 'firewall';
                    break;
                case 'Tunnel mullvad':
                case 'Tunnel earthvpn':
                    $newKey = 'exitVpn';
                    break;
                default:
                    $newKey = null;
            }
            if ($newKey !== null) {
                $ret[$newKey] = $this->translateValue($value);
            }
        }
        return $ret;
    }

    /**
     * @param string $value
     * @return int
     */
    protected function translateValue(string $value): int
    {
        $value = str_replace('.', '', $value);
        $value = trim($value);
        if (empty($value)) {
            return Gateway::STATE_UNKNOWN;
        }

        if (in_array($value, ['running', 'exists', 'is correct', 'is up', 'ok', 'online', 'true', 'valid'])) {
            return Gateway::STATE_OK;
        }

        return Gateway::STATE_ERROR;
    }

    protected function updateGatewayObject(Gateway $gateway, array $gatewayData): Gateway
    {
        $properties = ['ping', 'openVpn', 'networkInterface', 'firewall', 'exitVpn'];
        $healtChanged = false;
        foreach ($properties as $property) {
            if (isset($gatewayData[$property])) {
                $value = $gatewayData[$property];
            } else {
                $value = Gateway::STATE_UNKNOWN;
            }
            if ($gateway->_hasProperty($property) && ($gateway->_getProperty($property) != $value)) {
                $healtChanged = true;
                $gateway->_setProperty($property, $value);
            }
        }
        if($healtChanged){
            $gateway->setLastHealthChange(new \DateTime());
        }
        $gateway->setLastHealthCheck(new \DateTime());
        return $gateway;
    }
}