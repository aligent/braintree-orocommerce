<?php

namespace Oro\Bundle\MultiCurrencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\MultiCurrencyBundle\Entity\Repository\RateRepository")
 * @ORM\Table(
 *      name="oro_multicurrency_rate",
 *      indexes={@ORM\Index(name="rate_currency_code_idx",columns={"currency_code"})}
 * )
 * @Config(
 *      routeName="oro_multicurrency_rate_index",
 *      routeView="oro_multicurrency_rate_view",
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="sales_data",
 *              "field_acl_supported" = "true"
 *          },
 *          "dataaudit"={
 *              "auditable"=true,
 *              "immutable"=true
 *          }
 *     }
 * )
 *
 */
class Rate
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *  defaultValues={
     *      "importexport"={
     *          "order"=0
     *      }
     *  }
     * )
     */
    protected $id;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var string
     *
     * @ORM\Column(name="currency_code", type="currency", length=3, nullable=false)
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={
     *          "auditable"=true
     *      },
     *      "importexport"={
     *          "identity"=true,
     *          "order"=20
     *      }
     *  }
     * )
     */
    protected $currencyCode;

    /**
     * Conversion rate from entity currency to base currency
     * @var double
     *
     * @ORM\Column(name="rate_from", type="decimal", precision=25, scale=10, nullable=false)
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={
     *          "auditable"=true
     *      },
     *      "importexport"={
     *          "identity"=true,
     *          "order"=30
     *      }
     *  }
     * )
     */
    protected $rateFrom;

    /**
     * Conversion rate from base currency to entity currency
     * @var double
     *
     * @ORM\Column(name="rate_to", type="decimal", precision=25, scale=10, nullable=false)
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={
     *          "auditable"=true
     *      },
     *      "importexport"={
     *          "identity"=true,
     *          "order"=40
     *      }
     *  }
     * )
     */
    protected $rateTo;

    /**
     * @var double
     *
     * @ORM\Column(name="scope", type="string", length=16, nullable=false)
     */
    protected $scope;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Organization $organization
     *
     * @return Rate
     */
    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return null|Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param string $currencyCode
     *
     * @return Rate
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param double $rateFrom
     *
     * @return Rate
     */
    public function setRateFrom($rateFrom)
    {
        $this->rateFrom = $rateFrom;

        return $this;
    }

    /**
     * @return string
     */
    public function getRateFrom()
    {
        return $this->rateFrom;
    }

    /**
     * @param double $rateTo
     *
     * @return Rate
     */
    public function setRateTo($rateTo)
    {
        $this->rateTo = $rateTo;

        return $this;
    }

    /**
     * @return string
     */
    public function getRateTo()
    {
        return $this->rateTo;
    }

    /**
     * @param string $scope
     *
     * @return Rate
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }
}
