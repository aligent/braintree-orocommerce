<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Unit\Acl\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

use Oro\Bundle\MultiWebsiteBundle\Acl\Voter\DefaultWebsiteVoter;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class CustomerGroupVoterTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @dataProvider voteDataProvider
     * @param bool $isDefault
     * @param int $result
     * @param string $attribute
     */
    public function testVote($isDefault, $result, $attribute)
    {
        $voter = new DefaultWebsiteVoter();
        /** @var TokenInterface $token */
        $token = $this->createMock(TokenInterface::class);
        /** @var Website $website */
        $website = (new Website())->setDefault($isDefault);

        $this->assertSame($result, $voter->vote($token, $website, [$attribute]));
    }

    /**
     * @return array
     */
    public function voteDataProvider()
    {
        return [
            'abstain when not default' => [
                'is_default' => false,
                'result' => VoterInterface::ACCESS_GRANTED,
                'attribute' => 'DELETE',
            ],
            'abstain when not supported attribute' => [
                'is_default' => true,
                'result' => VoterInterface::ACCESS_ABSTAIN,
                'attribute' => 'VIEW',
            ],
            'denied when default website' => [
                'is_default' => true,
                'result' => VoterInterface::ACCESS_DENIED,
                'attribute' => 'DELETE',
            ],
        ];
    }
}
