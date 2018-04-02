<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\Operation\Authorize;

use BeSimple\SoapCommon\Type\KeyValue\Boolean;
use Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

/**
 * This class checks and validate all data in authorization process
 */
class OperationAuthorize extends AbstractBraintreeOperation
{

    /**
     *
     * @var Boolean
     */
    protected $isValidData;

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::preProcessOperation()
     */
    protected function preProcessOperation()
    {
        $paymentTransaction = $this->paymentTransaction;
        $sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();
        if ($sourcePaymentTransaction) {
            $paymentTransaction->setCurrency($sourcePaymentTransaction->getCurrency())
                ->setReference($sourcePaymentTransaction->getReference())
                ->setSuccessful($sourcePaymentTransaction->isSuccessful())
                ->setActive($sourcePaymentTransaction->isActive())
                ->setRequest()
                ->setResponse();
            $this->isValidData = false;
            ;
        } else {
            $this->isValidData = true;
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::postProcessOperation()
     */
    protected function postProcessOperation()
    {
        $paymentTransaction = $this->paymentTransaction;
        
        if ($this->isValidData) {
            // chequear si el nonce viene en transactionOptions cuando debugeo
            // En OperationValidate en la linea 39 - 40 se lee de POST de la misma manera y no hay comentarios al respecto
            // Hice pruebas con nueva tarjea, tarjeta guardada, eu o us con Authorization y Authorization Charge
            // Y nunca se llama a la authorize, PABLO como logro probar esto, porque entra al validate y dependiendo de la
            // configuracion lo deja en authorize o en Paid in full. La primera vez que entra en validate ya lo guarda en 
            // $transactionOptions['nonce'] = $nonce; linea 45 de OperationValidate.
            // Lo que queda es obtenerlo del transactionOptions, no pude probar este tema
            //  $nonce = $transactionOptions['nonce']; // y listo, tratemos de reproducirlo sino lo cambio y listo
            
            $nonce = $_POST["payment_method_nonce"];
            $transactionOptions = $paymentTransaction->getTransactionOptions();
            $transactionOptions['nonce'] = $nonce;
            $paymentTransaction->setTransactionOptions($transactionOptions);
            $paymentTransaction->setSuccessful(true)
                ->setAction(PaymentMethodInterface::VALIDATE)
                ->setActive(true);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Entrepids\Bundle\BraintreeBundle\Method\Operation\AbstractBraintreeOperation::preprocessDataToSend()
     */
    protected function preprocessDataToSend()
    {
    }
}
