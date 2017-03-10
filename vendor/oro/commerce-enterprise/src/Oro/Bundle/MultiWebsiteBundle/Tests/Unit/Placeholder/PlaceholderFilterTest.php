<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Placeholder;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\MultiWebsiteBundle\Placeholder\PlaceholderFilter;

class PlaceholderFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testIsWebsitePageTrue()
    {
        $placeholderFilter = new PlaceholderFilter();
        $this->assertTrue($placeholderFilter->isWebsitePage($this->createMock(Website::class)));
    }

    public function testIsWebsitePageFalse()
    {
        $placeholderFilter = new PlaceholderFilter();
        $this->assertFalse($placeholderFilter->isWebsitePage(new \stdClass()));
    }
}
