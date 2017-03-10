<?php

namespace Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CollectOnDeliveryBundle:Default:index.html.twig');
    }
}
