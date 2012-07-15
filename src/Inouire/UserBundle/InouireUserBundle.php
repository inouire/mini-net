<?php

namespace Inouire\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class InouireUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
