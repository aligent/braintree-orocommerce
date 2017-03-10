<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Tests\Unit\Placeholder;

use Oro\Bundle\WebsiteElasticSearchBundle\Placeholder\LocalizationIdPlaceholder;

class LocalizationIdPlaceholderTest extends \PHPUnit_Framework_TestCase
{
    /** @var LocalizationIdPlaceholder */
    private $placeholder;

    protected function setUp()
    {
        $this->placeholder = new LocalizationIdPlaceholder();
    }

    protected function tearDown()
    {
        unset($this->placeholder);
    }

    public function testGetPlaceholder()
    {
        $this->assertInternalType('string', $this->placeholder->getPlaceholder());
        $this->assertEquals('LOCALIZATION_ID', $this->placeholder->getPlaceholder());
    }

    public function testReplaceDefault()
    {
        $value = $this->placeholder->replaceDefault('string_LOCALIZATION_ID');

        $this->assertInternalType('string', $value);
        $this->assertEquals('string_([0-9]+|default)', $value);
    }

    public function testReplace()
    {
        $this->assertEquals(
            'string_replaced_value',
            $this->placeholder->replace('string_LOCALIZATION_ID', ['LOCALIZATION_ID' => 'replaced_value'])
        );
    }

    public function testReplaceWithoutValue()
    {
        $this->assertEquals(
            'string_LOCALIZATION_ID',
            $this->placeholder->replace('string_LOCALIZATION_ID', ['NOT_LOCALIZATION_ID' => '1'])
        );
    }
}
