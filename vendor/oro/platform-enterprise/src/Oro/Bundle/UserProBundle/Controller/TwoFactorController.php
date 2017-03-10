<?php

namespace Oro\Bundle\UserProBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorController extends Controller
{
    /**
     * @Route("/two-factor-auth", name="oropro_user_two_factor_auth")
     * @Template
     */
    public function authAction()
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        // 302 redirect does not processed by Backbone.sync handler, but 401 error does.
        if ($request->isXmlHttpRequest()) {
            return new Response(null, 401);
        }

        $helper = $this->get('security.authentication_utils');

        return [
            // last authentication error (if any)
            'error' => $helper->getLastAuthenticationError(),
        ];
    }

    /**
     * @Route("/two-factor-check", name="oropro_user_two_factor_check")
     */
    public function checkAction()
    {
        throw new \RuntimeException(
            'You must configure the check path to be handled by the firewall ' .
            'using two_factor in your security firewall configuration.'
        );
    }
}
