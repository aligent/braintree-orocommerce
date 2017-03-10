<?php

namespace Oro\Bundle\UserProBundle\Mailer;

use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\UserBundle\Mailer\BaseProcessor;

class Processor extends BaseProcessor
{
    const TEMPLATE_USER_AUTO_DEACTIVATE = 'auto_deactivate_failed_logins';
    const TEMPLATE_USER_AUTHENTICATION_CODE = 'authentication_code';

    /**
     * @param UserInterface $user
     * @param int $limit The exceed limit
     *
     * @return int
     */
    public function sendAutoDeactivateEmail(UserInterface $user, $limit)
    {
        return $this->getEmailTemplateAndSendEmail(
            $user,
            static::TEMPLATE_USER_AUTO_DEACTIVATE,
            ['entity' => $user, 'limit' => $limit]
        );
    }

    /**
     * @param  UserInterface $user
     * @param  string        $code
     * @param  \DateTime     $expiresAt
     * @param  array         $clientData
     *
     * @return int
     */
    public function sendAuthenticationCodeEmail(UserInterface $user, $code, \DateTime $expiresAt, $clientData)
    {
        return $this->getEmailTemplateAndSendEmail(
            $user,
            static::TEMPLATE_USER_AUTHENTICATION_CODE,
            ['entity' => $user, 'code' => $code, 'expiresAt' => $expiresAt, 'clientData' => $clientData]
        );
    }
}
