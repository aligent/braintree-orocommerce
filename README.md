Braintree Payment Gateway Bundle
===============================================

Facts
-----
- version: 3.1.0
- composer name: aligent/braintree-orocommerce

Description
-----------
This bundle allows you to use the [Braintree Drop In UI](https://developers.braintreepayments.com/guides/drop-in/overview/javascript/v3) 
with OroCommerce.  

Installation Instructions
-------------------------
1. Install this module via Composer

        composer require aligent/braintree-orocommerce

1. Clear cache
        
        php bin/console cache:clear --env=prod
        
1. Run Migrations
        
        php bin/console oro:migration:load --force --env=prod
        
Set up Instructions
-----------
Go to the "System -> Integrations -> Manage integrations" and click "Create Integration". Select "Braintree" as the integration type and fill all required fields.

To Enable select the Enabled checkbox in the PayPal section and add values for all fields. Note: Your Braintree account must be setup to accept PayPal payments see here: https://articles.braintreepayments.com/guides/payment-methods/paypal/setup-guide for more details.

![Braintree Integration Form](src/Resources/doc/images/braintree_integration.png?raw=true "Braintree Integration Form")

Once complete you must now create an appropriate 'Payment Rule' see: https://doc.oroinc.com/user/back-office/system/payment-rules/#sys-payment-rules for more details.
        
Supported Payment Methods
-----------
Current:
- PayPal
- Credit Card

Coming Soon:
- Google Pay
- Apple Pay
- PayPal Credit
- Venmo

Supported Payment Actions
-----------
Currently this bundle only supports the 'Purchase' action, 'Validate' and 'Capture' are coming soon.

Extension Points
-----------
####Events
BraintreePaymentActionEvent (aligent_braintree.payment_action.{action}):
 
This event is fired when a payment action executes but before the payload is sent to the payment gateway. It is used internally
to build up the payment payload and can be extended with other listeners to add data to be sent to the payment gateway. 

Currently supported events:
- aligent_braintree.payment_action.purchase

####Actions
Custom payment actions can be added by implementing the BraintreeActionInterface and then tagging the service with:

`{ name: braintree.action, action: action_name }`

Support
-------
If you have any issues with this bundle, please feel free to open [GitHub issue](https://github.com/aligent/braintree-orocommerce/issues) with version and steps to reproduce.

Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Adam Hall <adam.hall@aligent.com.au>.

License
-------
[MIT](https://opensource.org/licenses/mit)

Copyright
---------
(c) 2018-19 Aligent Consulting