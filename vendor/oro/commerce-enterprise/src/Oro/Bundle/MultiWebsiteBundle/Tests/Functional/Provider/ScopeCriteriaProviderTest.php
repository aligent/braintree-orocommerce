<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Functional\Provider;

use Oro\Bundle\ScopeBundle\Tests\Functional\AbstractScopeProviderTestCase;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;

class ScopeCriteriaProviderTest extends AbstractScopeProviderTestCase
{
    public function testProviderRegistered()
    {
        self::assertProviderRegisteredWithScopeTypes(
            ScopeCriteriaProvider::WEBSITE,
            [
                'category_visibility',
                'product_visibility',
                'customer_group_product_visibility',
                'customer_product_visibility',
                'workflow_definition',
                'web_content'
            ]
        );
    }
}
