<?php

namespace Entrepids\Bundle\BraintreeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('BraintreeBundle:Default:index.html.twig');
    }
}
