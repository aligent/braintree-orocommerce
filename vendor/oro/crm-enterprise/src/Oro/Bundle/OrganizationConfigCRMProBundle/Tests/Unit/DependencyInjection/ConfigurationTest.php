<?php

namespace Oro\Bundle\OrganizationConfigCRMProBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\OrganizationConfigCRMProBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $builder = $configuration->getConfigTreeBuilder();

        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $builder);
    }
}
