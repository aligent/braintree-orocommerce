<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\MultiWebsiteBundle\Matcher\WebsiteMatcherInterface;
use Oro\Bundle\MultiWebsiteBundle\DependencyInjection\Compiler\WebsiteMatcherPass;
use Oro\Bundle\MultiWebsiteBundle\Matcher\WebsiteMatcherRegistry;

class WebsiteMatcherPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $containerBuilder = new ContainerBuilder();

        $containerBuilder->register('oro_website_matcher_stub', WebsiteMatcherInterface::class);
        $matcherDefinition = $containerBuilder->getDefinition('oro_website_matcher_stub');
        $matcherDefinition->addTag(WebsiteMatcherPass::TAG, ['alias' => 'matcher_alias']);

        $containerBuilder->register(WebsiteMatcherPass::REGISTRY_SERVICE, WebsiteMatcherRegistry::class);

        $matcherPass = new WebsiteMatcherPass();
        $matcherPass->process($containerBuilder);

        $matcherDefinition = $containerBuilder->getDefinition(WebsiteMatcherPass::REGISTRY_SERVICE);
        $methodCalls = $matcherDefinition->getMethodCalls();

        $this->assertCount(1, $methodCalls);
        $call = $methodCalls[0];
        $this->assertEquals('addMatcher', $call[0]);
        $this->assertEquals('matcher_alias', $call[1][0]);
        /** @var Reference $reference */
        $reference = $call[1][1];
        $this->assertEquals(new Reference('oro_website_matcher_stub'), $reference);
    }
}
