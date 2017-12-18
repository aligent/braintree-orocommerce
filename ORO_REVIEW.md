General comments
----------------

* JS error occurs on the Payment method step of a checkout.
```
r {name: "BraintreeError", code: "INSTANTIATION_OPTION_REQUIRED", message: "options.authorization is required when instantiating a client.", type: "MERCHANT", details: undefined}
```
* Choose and use standard for code formatting, for example [PSR-2](http://www.php-fig.org/psr/psr-2/), and fix phpcs violations according to it. You can find phpcs report below.
* All files contain spaces and tabs mixed, please, choose and use one approach. PSR-2 uses spaces.
* Please, remove unused variables 
* Please, remove unused use's from headers of a php files.
* Is is highly recommended to cover the code with tests, at least with unit

PHP Mass Detector report
------------------------

Please, see [PHPMD](./PHPMD_REPORT.txt) report, it was generated with oro/commerce/build_config/phpmd.xml config.

PHP Code Sniffer report
------------------------

Please, see [PHPCS](./PHPCS_REPORT.txt) report, it was generated with oro/platform/build/phpcs.xml config.
