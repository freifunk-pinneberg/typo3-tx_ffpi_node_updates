<?php

declare(strict_types=1);

namespace FFPI\FfpiNodeUpdates\Updates;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('ffpiFfpiNodeUpdatesCTypeMigration')]
final class FFPIFfpiNodeUpdatesCTypeMigration extends AbstractListTypeToCTypeUpdate
{
    public function getTitle(): string
    {
        return 'Migrate "FFPI FfpiNodeUpdates" plugins to content elements.';
    }

    public function getDescription(): string
    {
        return 'The "FFPI FfpiNodeUpdates" plugins are now registered as content element. Update migrates existing records and backend user permissions.';
    }

    /**
     * This must return an array containing the "list_type" to "CType" mapping
     *
     *  Example:
     *
     *  [
     *      'pi_plugin1' => 'pi_plugin1',
     *      'pi_plugin2' => 'new_content_element',
     *  ]
     *
     * @return array<string, string>
     */
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'ffpinodeupdates_nodeabo' => 'ffpinodeupdates_nodeabo',
            'ffpinodeupdates_gatewayhealth' => 'ffpinodeupdates_gatewayhealth',
            'ffpinodeupdates_freifunkapifile' => 'ffpinodeupdates_freifunkapifile',
        ];
    }
}
