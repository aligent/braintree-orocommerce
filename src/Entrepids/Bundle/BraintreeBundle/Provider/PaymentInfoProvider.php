<?php
namespace Entrepids\Bundle\BraintreeBundle\Provider;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareTrait;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\PaymentBundle\Event\TransactionCompleteEvent;
use Oro\Bundle\PaymentBundle\Entity\Repository\PaymentTransactionRepository;
use Symfony\Component\Translation\TranslatorInterface;

class PaymentInfoProvider
{
    
    use LoggerAwareTrait;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $paymentTransactionClass;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     *
     * @param DoctrineHelper $doctrineHelper
     * @param TokenStorageInterface $tokenStorage
     * @param EventDispatcherInterface $dispatcher
     * @param unknown $paymentTransactionClass
     * @param TranslatorInterface $translator
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $dispatcher,
        $paymentTransactionClass,
        TranslatorInterface $translator
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->paymentTransactionClass = $paymentTransactionClass;
        $this->dispatcher = $dispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
    }

    /**
     *
     * @param object $entity
     * @return string
     */
    public function getPaymentInfo($entity)
    {
        return $this->getPaymentData($entity);
    }

    /**
     * this method return the detail of order in Braintree to show
     *
     * @param unknown $entity
     */
    protected function getPaymentData($entity)
    {
        $className = $this->doctrineHelper->getEntityClass($entity);
        $identifier = $this->doctrineHelper->getSingleEntityIdentifier($entity);
        /**
         * @var PaymentTransactionRepository $repository
         */
        $repository = $this->doctrineHelper->getEntityRepository(PaymentTransaction::class);
        
        /**
         * @var PaymentTransaction $transaction
         */
        $transaction = $repository->findOneBy([
            'entityClass' => $className,
            'entityIdentifier' => $identifier
        ]);
        
        if (! $transaction) {
            return '';
        } else {
            $transactionOptions = $transaction->getTransactionOptions();
            $creditCardDetails = null;
            if (isset($transactionOptions['creditCardDetails'])) {
                $creditCardDetails = $transactionOptions['creditCardDetails'];
            }
            if ($creditCardDetails != null) {
                $object = unserialize($creditCardDetails);
                $cardType = $object->cardType;
                $last4 = $object->last4;
                $debit = $object->debit;
                
                $typeCard = 'Credit';
                if ($debit == 'Yes') {
                    $typeCard = 'Debit';
                }
                $valueShow = $this->translator->trans('entrepids.braintree.order_view.info_detail', [
                    '{{brand}}' => $cardType,
                    '{{type}}' => $typeCard,
                    '{{last4}}' => $last4
                ]);
                
                return $valueShow;
            } else {
                return $this->translator->trans('entrepids.braintree.order_view.info_nodata');
            }
        }
        
        return '';
    }

    /**
     *
     * @param unknown $entity
     */
    public function isApplicable($entity)
    {
        $className = $this->doctrineHelper->getEntityClass($entity);
        $identifier = $this->doctrineHelper->getSingleEntityIdentifier($entity);
        /**
         * @var PaymentTransactionRepository $repository
         */
        $repository = $this->doctrineHelper->getEntityRepository(PaymentTransaction::class);
        $methods = $repository->getPaymentMethods($className, [
            $identifier
        ]);
        /**
         * @var PaymentTransaction $transaction
         */
        $transaction = $repository->findOneBy([
            'entityClass' => $className,
            'entityIdentifier' => $identifier
        ]);
        
        if (! $transaction) {
            return false;
        } else {
            $transactionOptions = $transaction->getTransactionOptions();
            $isBraintreeEntrepids = null;
            if (isset($transactionOptions['isBraintreeEntrepids'])) {
                $isBraintreeEntrepids = $transactionOptions['isBraintreeEntrepids'];
                return true;
            }
        }
        
        return false;
    }
}
