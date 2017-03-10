<?php

namespace Oro\Bundle\EmailProBundle\Provider;

use Oro\Bundle\EmailBundle\Form\Model\SmtpSettings;
use Oro\Bundle\EmailBundle\Provider\AbstractSmtpSettingsProvider;
use Oro\Bundle\OrganizationConfigBundle\Config\OrganizationScopeManager;

class SmtpSettingsProvider extends AbstractSmtpSettingsProvider
{
    /** @var OrganizationScopeManager */
    protected $orgScopeManager;

    /**
     * @inheritdoc
     */
    public function getSmtpSettings($scopeIdentifier = null)
    {
        return $this->getConfigurationSmtpSettings();
    }

    /**
     * @param OrganizationScopeManager $manager
     */
    public function setOrgScopeManager($manager)
    {
        $this->orgScopeManager = $manager;
    }
}
