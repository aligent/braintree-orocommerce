<?php

namespace Entrepids\Bundle\BraintreeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// ORO REVIEW:
// Seems that this controller (and his test) is excess
class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('BraintreeBundle:Default:index.html.twig');
    }
}
