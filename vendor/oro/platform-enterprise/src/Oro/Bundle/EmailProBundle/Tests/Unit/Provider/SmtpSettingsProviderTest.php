<?php

namespace Oro\Bundle\EmailProBundle\Tests\Unit\Provider;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Bundle\ConfigBundle\Config\ConfigDefinitionImmutableBag;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Config\GlobalScopeManager;

use Oro\Bundle\EmailProBundle\Provider\SmtpSettingsProvider;

use Oro\Bundle\OrganizationConfigBundle\Config\OrganizationScopeManager;

use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;

class SmtpSettingsProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var SmtpSettingsProvider */
    protected $provider;

    /** @var ConfigManager */
    protected $manager;

    /** @var GlobalScopeManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $globalScopeManager;

    /** @var OrganizationScopeManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $organizationScopeManager;

    /** @var Mcrypt|\PHPUnit_Framework_MockObject_MockObject */
    protected $mcrypt;

    /**
     * @var array
     */
    protected $globalSettings = [
        'oro_email' => [
            'smtp_settings_host' => [
                'value' => 'smtp.orocrm.com',
                'type'  => 'scalar',
            ],
            'smtp_settings_port' => [
                'value' => 465,
                'type'  => 'integer',
            ],
            'smtp_settings_encryption' => [
                'value' => 'ssl',
                'type'  => 'scalar',
            ],
            'smtp_settings_username' => [
                'value' => 'user',
                'type'  => 'scalar',
            ],
            'smtp_settings_password' => [
                'value' => 'pass',
                'type'  => 'scalar',
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $organizationSettings = [
        'oro_email' => [
            'smtp_settings_host' => [
                'value' => 'smtp.sendgrid.net',
                'type'  => 'scalar',
            ],
            'smtp_settings_port' => [
                'value' => 587,
                'type'  => 'integer',
            ],
            'smtp_settings_encryption' => [
                'value' => 'tls',
                'type'  => 'scalar',
            ],
            'smtp_settings_username' => [
                'value' => 'sendgrid_user',
                'type'  => 'scalar',
            ],
            'smtp_settings_password' => [
                'value' => 'sendgrid_pass',
                'type'  => 'scalar',
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $partialOrganizationSettings = [
        'oro_email' => [
            'smtp_settings_host' => [
                'value' => 'smtp.sendgrid.net',
                'type'  => 'scalar',
            ]
        ],
    ];

    protected $orgScopeIdentifier = 1;

    protected function setUp()
    {
        $this->mcrypt = new Mcrypt();
        $this->globalSettings['oro_email']['smtp_settings_password']['value'] = $this->mcrypt->encryptData(
            $this->globalSettings['oro_email']['smtp_settings_password']['value']
        );
        $this->organizationSettings['oro_email']['smtp_settings_password']['value'] = $this->mcrypt->encryptData(
            $this->organizationSettings['oro_email']['smtp_settings_password']['value']
        );

        $globalBag = new ConfigDefinitionImmutableBag($this->globalSettings);
        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = new ConfigManager(
            'global',
            $globalBag,
            $dispatcher
        );

        $this->globalScopeManager = $this->getMockBuilder(GlobalScopeManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->organizationScopeManager = $this->getMockBuilder(OrganizationScopeManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager->addManager('global', $this->globalScopeManager);
        $this->manager->addManager($this->orgScopeIdentifier, $this->organizationScopeManager);

        $this->provider = new SmtpSettingsProvider($this->manager, $this->globalScopeManager, $this->mcrypt);
        $this->provider->setOrgScopeManager($this->organizationScopeManager);
    }

    protected function setOrganizationSettings($settings)
    {
        foreach ($settings as $rootName => $setting) {
            foreach ($setting as $fieldName => $value) {
                $name = sprintf('%s.%s', $rootName, $fieldName);
                $this->manager->set($name, $value['value'], $this->orgScopeIdentifier);
            }
        }
    }

    public function testSmtpSettingOrganizationValues()
    {
        $this->setOrganizationSettings($this->organizationSettings);
        $smtpSettings = $this->provider->getSmtpSettings();

        $this->assertSame($smtpSettings->getHost(), $this->getScopedValue('oro_email.smtp_settings_host'));
        $this->assertSame($smtpSettings->getPort(), $this->getScopedValue('oro_email.smtp_settings_port'));
        $this->assertSame($smtpSettings->getEncryption(), $this->getScopedValue('oro_email.smtp_settings_encryption'));
        $this->assertSame($smtpSettings->getUsername(), $this->getScopedValue('oro_email.smtp_settings_username'));
        $this->assertSame(
            $smtpSettings->getPassword(),
            $this->mcrypt->decryptData($this->getScopedValue('oro_email.smtp_settings_password'))
        );
    }

    public function testSmtpSettingWithPartialOrganizationValues()
    {
        $this->setOrganizationSettings($this->partialOrganizationSettings);
        $smtpSettings = $this->provider->getSmtpSettings();

        $this->assertSame($smtpSettings->getHost(), $this->getScopedValue('oro_email.smtp_settings_host'));
        $this->assertSame($smtpSettings->getPort(), $this->getScopedValue('oro_email.smtp_settings_port'));
        $this->assertSame($smtpSettings->getEncryption(), $this->getScopedValue('oro_email.smtp_settings_encryption'));
        $this->assertSame($smtpSettings->getUsername(), $this->getScopedValue('oro_email.smtp_settings_username'));
        $this->assertSame(
            $smtpSettings->getPassword(),
            $this->mcrypt->decryptData($this->getScopedValue('oro_email.smtp_settings_password'))
        );
    }

    private function getScopedValue($key)
    {
        return $this->manager->get($key, false, false, $this->orgScopeIdentifier);
    }
}
