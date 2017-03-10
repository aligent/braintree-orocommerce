UPGRADE FROM 1.12 to 2.0
========================

####General
- **Upgrade to 2.0 is available only from 1.12 version**.

  To correctly upgrade to version 2.0 follow the steps in the guide [How to Upgrade to a New Version](https://www.orocrm.com/documentation/index/current/cookbook/how-to-upgrade-to-new-version).
  At **Step 7** instead of running
  ```shell
  $ sudo -u www-data php app/console oro:platform:update --env=prod --force
  ```
  you will run **only once** the upgrade command introduced to help upgrading from 1.12 to 2.0
  ```shell
  $ sudo -u www-data php app/console oro:platform:upgrade20 --env=prod --force
  ```
  
  Upgrade from version less then 1.12 is not supported.
- Changed minimum required php version to 5.6
  
####OroProElasticSearchBundle
* Public method `OroPro\Bundle\ElasticSearchBundle\Engine\IndexAgent recreateTypeMapping` was split into
  two separate methods - `validateTypeMapping` and `clearType`
* Explicit configuration in `oro_search` section has higher priority than implicit search configuration in `parametrs.yml`.
See [Client configuration](src/Oro/Bundle/ElasticSearchBundle/Resources/doc/configuration.md#client-configuration)

####OroProUserProBundle

- Added `oro:user:unlock` CLI command that can be used to transition user status from locked to active
