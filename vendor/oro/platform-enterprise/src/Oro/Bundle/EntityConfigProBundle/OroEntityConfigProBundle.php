<?php

namespace Oro\Bundle\EntityConfigProBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroEntityConfigProBundle extends Bundle
{
    public function getParent()
    {
        return 'OroEntityConfigBundle';
    }
}
