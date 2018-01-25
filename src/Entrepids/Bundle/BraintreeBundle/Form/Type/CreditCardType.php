<?php

namespace Entrepids\Bundle\BraintreeBundle\Form\Type;

use Doctrine\Common\Collections\Criteria;
use Entrepids\Bundle\BraintreeBundle\Model\Adapter\BraintreeAdapter;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This is the class that loads the form in the checkout process
 */
class CreditCardType extends AbstractType
{

    const NAME = 'entrepids_braintree_credit_card';

    /**
     *
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    protected $paymentsTransactions;

    /**
     * @var BraintreeAdapter
     */
    protected $adapter;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     *
     * @var String
     */
    protected $selectedCard;

    /**
     *
     * @param DoctrineHelper $doctrineHelper
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->getTransactionCustomerORM();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'payment_method_nonce',
            'hidden',
            [
            'mapped' => true,
            'attr' => [
                'data-gateway' => true]
            ]
        );
        
        $creditsCards = [];
        $creditsCards = $this->getCreditCardsSaved();
        $creditsCardsCount = count($creditsCards);
        if ($creditsCardsCount > 1) {
            $builder = $this->setCreditsCard($builder, $creditsCards);
        } else {
            $builder = $this->setNewCreditCard($builder);
        }
        
        if ($options['zeroAmountAuthorizationEnabled']) {
            $builder->add('save_for_later', 'checkbox', [
                'required' => false,
                'label' => 'entrepids.braintree.settings.save_for_later.label',
                'mapped' => false,
                'data' => false,
                'attr' => [
                    'data-save-for-later' => true
                ]
            ]);
        }
        
        if ($options['data'] !== null) {
            $config = $options['data'];
            $this->adapter = new BraintreeAdapter($config);
            $this->adapter->initCredentials();
            $braintreeClientToken = $this->adapter->generate();
            
            $builder->add('braintree_client_token', 'hidden', [
                'mapped' => true,
                'data' => $braintreeClientToken
            ]);
        } else {
            $builder->add('braintree_client_token', 'hidden', [
                'mapped' => true,
                'data' => 'basura'
            ]);
        }
        
        $builder->add('credit_card_value', 'hidden', [
            'mapped' => true,
            'attr' => [
                'data-gateway' => true
            ]
        ]);
    }

    /**
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'entrepids.braintree.methods.credit_card.label',
            'csrf_protection' => false,
            'zeroAmountAuthorizationEnabled' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children as $child) {
            $child->vars['full_name'] = $child->vars['name'];
        }
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     *
     * @return string
     *
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     *
     * @return CustomerUser|null
     */
    protected function getLoggedCustomerUser()
    {
        $token = $this->tokenStorage->getToken();
        if (! $token) {
            return null;
        }
        
        $user = $token->getUser();
        
        if ($user instanceof CustomerUser) {
            return $user;
        }
        
        return null;
    }

    /**
     * The method get the customer user and then get the transactions to determine if they have any saved card
     */
    private function getTransactionCustomerORM()
    {
        $customerUser = $this->getLoggedCustomerUser();
       
        $where = "p.frontendOwner = :customerUser AND p.reference IS NOT NULL";
        $paymentTransactionEntity = $this->doctrineHelper->getEntityRepository(
            PaymentTransaction::class
        )
            ->createQueryBuilder('p')
        ->setParameters(array('customerUser' => $customerUser))
        ->where($where)
        ->getQuery()
        ->getResult();
        
        $this->paymentsTransactions = $paymentTransactionEntity;
    }

    /**
     * get credit cards saved from customer user
     *
     * @return array
     */
    private function getCreditCardsSaved()
    {
        $creditsCards = [];
        
        $countCreditCards = 0;
        
        foreach ($this->paymentsTransactions as $paymentTransaction) {
            $reference = $paymentTransaction->getReference();
            $paymentID = $paymentTransaction->getId();
            $response = $paymentTransaction->getResponse();
            $valueCreditCard = $this->translator->trans('entrepids.braintree.braintreeflow.existing_card', [
                 '{{brand}}' => $response['cardType'],
                 '{{last4}}' => $response['last4'],
                 '{{month}}' => $response['expirationMonth'],
                 '{{year}}' => $response['expirationYear']
            ]);
            $creditsCards[$paymentID] = $valueCreditCard;
            $countCreditCards ++;
            if ($countCreditCards == 1) {
                 $this->selectedCard = $paymentID;
            }
        }
        
        $creditsCards['newCreditCard'] = 'entrepids.braintree.braintreeflow.use_different_card';
        return $creditsCards;
    }

    /**
     *
     * @param FormBuilderInterface $builder
     */
    private function setCreditsCard(FormBuilderInterface $builder, $creditsCards)
    {
        $builder->add(
            'credit_cards_saved',
            ChoiceType::class,
            [
            'required' => true,
            'choices' => $creditsCards,
            'choices_as_values' => false,
            'choice_value' => function ($choice) {
                return $choice;
            },
            'label' => 'entrepids.braintree.braintreeflow.use_authorized_card',
            'attr' => [
                'data-credit-cards-saved' => true]
            ]
        );
        
        $builder->add('credit_card_first_value', 'hidden', [
            'mapped' => true,
            'data' => $this->selectedCard,
            'attr' => [
                'data-credit_card_first_value' => $this->selectedCard
            ]
        ]);
        
        return $builder;
    }

    /**
     *
     * @param FormBuilderInterface $builder
     */
    private function setNewCreditCard(FormBuilderInterface $builder)
    {
        $builder->add('credit_card_first_value', 'hidden', [
            'mapped' => true,
            'data' => 'newCreditCard',
            'attr' => [
                'data-credit_card_first_value' => 'newCreditCard'
            ]
        ]);
        
        return $builder;
    }
}
