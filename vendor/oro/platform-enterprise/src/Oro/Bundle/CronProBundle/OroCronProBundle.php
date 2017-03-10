<?php

namespace Oro\Bundle\CronProBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroCronProBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'OroCronBundle';
    }
}
