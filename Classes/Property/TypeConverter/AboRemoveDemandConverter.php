<?php

namespace FFPI\FfpiNodeUpdates\Property\TypeConverter;

use FFPI\FfpiNodeUpdates\Domain\Model\Dto\AboRemoveDemand;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

class AboRemoveDemandConverter extends AbstractTypeConverter
{
    protected $sourceTypes = ['array'];
    protected $targetType = AboRemoveDemand::class;
    protected $priority = 1;

    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        $aboRemoveDemand = new AboRemoveDemand();
        $aboRemoveDemand->setEmail($source['email'] ?? '');
        $aboRemoveDemand->setSecret($source['secret'] ?? '');

        return $aboRemoveDemand;
    }
}
