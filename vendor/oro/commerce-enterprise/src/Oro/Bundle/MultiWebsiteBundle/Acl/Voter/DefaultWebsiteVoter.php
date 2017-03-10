<?php

namespace Oro\Bundle\MultiWebsiteBundle\Acl\Voter;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DefaultWebsiteVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $attribute === 'DELETE' && $subject instanceof Website;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return !$subject->isDefault();
    }
}
