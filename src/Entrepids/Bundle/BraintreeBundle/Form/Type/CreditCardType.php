<?php

namespace Entrepids\Bundle\BraintreeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CreditCardType extends AbstractType
{
    const NAME = 'entrepids_braintree_credit_card';

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;
    
    /** @var TokenStorageInterface */
    protected $tokenStorage;
    
    protected $paymentsTransactions;
    
	/**
	 * 
	 * @param DoctrineHelper $doctrineHelper
	 * @param TokenStorageInterface $tokenStorage
	 */
    public function __construct(DoctrineHelper $doctrineHelper,  TokenStorageInterface $tokenStorage){
    	$this->doctrineHelper = $doctrineHelper; 
    	$this->tokenStorage = $tokenStorage;
    	$this->getTransactionCustomerORM();
    }
    
    
    private function getTransactionCustomerORM (){
   
    	$qb = $this->doctrineHelper->getEntityRepository(PaymentTransaction::class)->createQueryBuilder('pt');
    	$res =  $qb->select('response')
    	->where(
    			$qb->expr()->isNotNull('reference')
    	)
    	->orderBy('id');
    	
    	$customerUser = $this->getLoggedCustomerUser();

    	$criteria = Criteria::create()
    	->where(Criteria::expr()->isNull('reference'));
    	
    	$paymentTransactionEntity = $this->doctrineHelper->getEntityRepository(PaymentTransaction::class)->findBy([
    			'frontendOwner' => $customerUser,
    	]);
    
		$this->paymentsTransactions = $paymentTransactionEntity;

    	
    	$query = $qb->getQuery();

    	$result = new BufferedQueryResultIterator($qb);
    	

    }
    
    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
        	'payment_method_nonce',
        	'hidden',
            [
            		'mapped' => true,
                'attr' => [
                    'data-gateway' => true,
                ],
            ]
        );
		
        if ($options['zeroAmountAuthorizationEnabled']) {
        	$builder->add(
        			'save_for_later',
        			'checkbox',
        			[
        					'required' => false,
        					'label' => 'oro.paypal.credit_card.save_for_later.label',
        					'mapped' => false,
        					'data' => false,
        					'attr' => [
        							'data-save-for-later' => true,
        					],
        			]
        	);
        }
        
        $builder->add(
        		'credit_card_value',
        		'hidden',
        		[
        				'mapped' => true,
        				'attr' => [
        						'data-gateway' => true,
        				],
        		]
        );
        $creditsCards = [];
        $creditsCards['newCreditCard'] = 'entrepids.braintree.braintreeflow.new_credit_card';
        foreach ($this->paymentsTransactions as $paymentTransaction){
        	$reference = $paymentTransaction->getReference ();
        	$paymentID = $paymentTransaction->getId ();
        	if (trim($reference)) { // esto porque más arriba tengo que obtener los pagos en donde reference no sea null
        		// Significa que tiene un reference que no esta vacio
        		$response = $paymentTransaction->getResponse ();
        		$token = $response['token'];
        		$last4 = $response['last4'];
        		$cardType = $response['cardType'];
        		$expirationMonth = $response['expirationMonth'];
        		$expirationYear = $response['expirationYear'];
        		$expiresXXX = $cardType . ' xxxx xxxx xxxx ' . $last4 . ' (Expires ' .$expirationMonth . '/' . $expirationYear . ')';
        		$creditsCards [$paymentID] = $expiresXXX;
        	}
        	 
        }
        
		$creditsCardsCount = count($creditsCards);
        
		if ($creditsCardsCount > 1){
			$builder->add('credit_cards_saved', ChoiceType::class, [
					'required' => true,
					'choices' => $creditsCards,
					'label' => 'entrepids.braintree.braintreeflow.use_authorized_card',
					'attr' => [
							'data-credit-cards-saved' => true,
					],
			
			]);			
		}
		
        
        

        
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'entrepids.braintree.methods.credit_card.label',
            'csrf_protection' => false,
            'zeroAmountAuthorizationEnabled' => false,
            'requireCvvEntryEnabled' => true,
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
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
    
    /**
     * @return CustomerUser|null
     */
    protected function getLoggedCustomerUser()
    {
    	$token = $this->tokenStorage->getToken();
    	if (!$token) {
    		return null;
    	}
    
    	$user = $token->getUser();
    
    	if ($user instanceof CustomerUser) {
    		return $user;
    	}
    
    	return null;
    }
}
