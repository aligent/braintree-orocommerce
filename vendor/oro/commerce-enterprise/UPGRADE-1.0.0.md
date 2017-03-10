UPGRADE FROM 1.0.0-RC.1 to 1.0.0
=======================================

General
-------
* For upgrade from **1.0.0-rc.1** use command:
```bash
php app/console oro:platform:upgrade20 --env=prod --force
```

AccountProBundle
----------------
- Bundle renamed to `CustomerProBundle`

MultiWebsiteBundle
------------------
* The following classes were renamed:
    - `AbstractAccountFormViewListener` to `AbstractCustomerFormViewListener`
    - `AccountGroupFormViewListener` to `CustomerGroupFormViewListener`
    - `AccountFormViewListener` to `CustomerFormViewListener`

WebsiteElasticSearchBundle
--------------------------
* The class `AccountIdPlaceholder` was renamed to `CustomerIdPlaceholder`.
* The methods `getEntityName`, `getServiceFields` and `addServiceFieldsToQuery` were removed from class `ElasticSearchEngine`.
* New method `setPlaceholderHelper` was added to class `ElasticSearchEngine`.
* New protected method `getPlaceholderHelper` was added to class `ElasticSearchEngine`.
* New method `setPlaceholderHelper` was added to class `ElasticSearchIndexer`.
* New protected method `getPlaceholderHelper` was added to class `ElasticSearchIndexer`.
* The following methods were renamed in class `ElasticSearchPartialUpdateManager`:
    - `createAccountWithoutAccountGroupVisibility` to `createCustomerWithoutCustomerGroupVisibility`
    - `addAccountVisibility`/`deleteAccountVisibility` to `addCustomerVisibility`/`deleteCustomerVisibility`
