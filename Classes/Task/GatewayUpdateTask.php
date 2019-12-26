<?php

namespace FFPI\FfpiNodeUpdates\Task;

use FFPI\FfpiNodeUpdates\Domain\Model\Gateway;
use FFPI\FfpiNodeUpdates\Domain\Repository\GatewayRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class GatewayUpdateTask extends AbstractTask
{
    /** @var GatewayRepository */
    protected $gatewayRepository;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var PersistenceManager */
    protected $persistenceManager;

    /** @var int */
    public $pid;

    protected function inistalizeTask()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->gatewayRepository = $this->objectManager->get(GatewayRepository::class);
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $querySettings = new Typo3QuerySettings();
        $querySettings->setStoragePageIds([(int)$this->pid]);
        $this->gatewayRepository->setDefaultQuerySettings($querySettings);
    }

    /**
     * This method returns the destination pid as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation(): string
    {
        return 'Storage Page: ' . $this->pid;
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
        $ret = [];
        if(!empty(parse_url($url, PHP_URL_HOST))) {
            try {
                $ret['ping'] = $this->ping(parse_url($url, PHP_URL_HOST));
            } catch (\Exception $e) {
                $this->logException($e);
            }
        }
        $ffgateCheck = $this->getFfgateCheckData($url);
        $ffgateCheck = $this->formatGatewayData($ffgateCheck);
        return array_merge($ret, $ffgateCheck);
    }

    /**
     * @param string $host
     * @return float|null
     */
    protected function ping(string $host): ?float
    {
        try {
            $pingRawResult = exec('ping -q -c 2 ' . $host . ' | grep avg');
        } catch (\Exception $e) {
            return null;
        }
        $result = preg_replace('/rtt\smin\/avg\/max\/mdev\s=\s\d+.\d+\/(\d+.\d+)\/\d+.\d+\/\d+.\d+\sms/', '${1}', $pingRawResult);
        $result = floatval($result);
        return $result;
    }

    /**
     * @param string $url
     * @return array
     */
    protected function getFfgateCheckData(string $url): array
    {
        $file = @file_get_contents($url);
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
                case 'Interface earthvpn':
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
                if ($property !== 'ping') {
                    $healtChanged = true;
                    $gateway->_setProperty($property, $value);
                } elseif (($gateway->_getProperty($property) > 0 && ($value == 0 || $value == null)) ||
                    ($value > 0 && ($gateway->_getProperty($property) == 0 || $gateway->_getProperty($property) == null))) {
                    $healtChanged = true;
                    $gateway->_setProperty($property, $value);
                } else {
                    $gateway->_setProperty($property, $value);
                }
            }
        }
        if ($healtChanged) {
            $gateway->setLastHealthChange(new \DateTime());
        }
        $gateway->setLastHealthCheck(new \DateTime());
        return $gateway;
    }
}