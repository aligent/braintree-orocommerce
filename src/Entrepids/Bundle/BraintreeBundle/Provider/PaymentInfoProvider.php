<?php

namespace Entrepids\Bundle\BraintreeBundle\Provider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Entity\Repository\PaymentTransactionRepository;
use Symfony\Component\Translation\TranslatorInterface;

class PaymentInfoProvider
{

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        TranslatorInterface $translator
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->translator = $translator;
    }

    /**
     *
     * @param object $entity
     * @return string
     */
    public function getPaymentInfo($entity)
    {
        $transaction = $this->fetchEntityTransaction($entity);

        if ($transaction) {
            $transactionOptions = $transaction->getTransactionOptions();
            if (isset($transactionOptions['creditCardDetails'])) {
                $ccDetails = unserialize($transactionOptions['creditCardDetails']);
                $translated = $this->translator->trans('entrepids.braintree.order_view.info_detail', [
                    '{{brand}}' => $ccDetails->cardType,
                    '{{type}}' => ($ccDetails->debit == 'Yes'  ? 'Debit' : 'Credit'),
                    '{{last4}}' => $ccDetails->last4,
                ]);
                return $translated;
            }
        }
        return $this->translator->trans('entrepids.braintree.order_view.info_nodata');
    }

    /**
     *
     * @param unknown $entity
     */
    public function isApplicable($entity)
    {
        $transaction = $this->fetchEntityTransaction($entity);

        if ($transaction) {
            $transactionOptions = $transaction->getTransactionOptions();
            if (isset($transactionOptions['isBraintreeEntrepids'])) {
                return $transactionOptions['isBraintreeEntrepids'];
            }
        }

        return false;
    }


    /**
     * @param $entity
     * @return PaymentTransaction
     */
    private function fetchEntityTransaction($entity): PaymentTransaction
    {
        $className = $this->doctrineHelper->getEntityClass($entity);
        $identifier = $this->doctrineHelper->getSingleEntityIdentifier($entity);
        /** @var PaymentTransactionRepository $repository */
        $repository = $this->doctrineHelper->getEntityRepository(PaymentTransaction::class);

        /** @var PaymentTransaction $transaction */
        $transaction = $repository->findOneBy([
            'entityClass' => $className,
            'entityIdentifier' => $identifier,
        ]);
        return $transaction;
    }
}
