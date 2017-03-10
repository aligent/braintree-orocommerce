<?php

namespace Oro\Bundle\SecurityProBundle\Form\Model;

/**
 * Factory is intended to provide flexible way to extend form models
 */
class Factory
{
    /**
     * @return Share
     */
    public function getShare()
    {
        return new Share();
    }
}
