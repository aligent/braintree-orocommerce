<?php

namespace Oro\Bundle\MultiWebsiteBundle\Matcher;

use Oro\Bundle\WebsiteBundle\Entity\Website;

interface WebsiteMatcherInterface
{
    /**
     * @return Website|null
     */
    public function match();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return string
     */
    public function getTooltip();

    /**
     * @param int $priority
     */
    public function setPriority($priority);

    /**
     * @return int
     */
    public function getPriority();
}
