<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Tests\Unit\Placeholder;

use Oro\Bundle\WebsiteElasticSearchBundle\Placeholder\CustomerIdPlaceholder;

class CustomerIdPlaceholderTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerIdPlaceholder */
    private $placeholder;

    protected function setUp()
    {
        $this->placeholder = new CustomerIdPlaceholder();
    }

    public function testGetPlaceholder()
    {
        $this->assertInternalType('string', $this->placeholder->getPlaceholder());
        $this->assertEquals('ACCOUNT_ID', $this->placeholder->getPlaceholder());
    }

    public function testReplace()
    {
        $this->assertEquals(
            'visibility_customer_1',
            $this->placeholder->replace('visibility_customer_ACCOUNT_ID', [CustomerIdPlaceholder::NAME => 1])
        );
    }

    public function testReplaceDefault()
    {
        $value = $this->placeholder->replaceDefault('string_ACCOUNT_ID');

        $this->assertInternalType('string', $value);
        $this->assertEquals('string_[0-9]+', $value);
    }

    public function testReplaceWithoutValue()
    {
        $this->assertEquals(
            'string_ACCOUNT_ID',
            $this->placeholder->replace('string_ACCOUNT_ID', ['NOT_ACCOUNT_ID' => '1'])
        );
    }
}
