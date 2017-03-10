<?php

namespace Oro\Bundle\TestFrameworkProBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroTestFrameworkProBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'OroTestFrameworkBundle';
    }
}
