UPGRADE FROM 1.0.0-BETA.5 to 1.0.0-RC.1
=======================================

General
-------
* Changed minimum required `php` version to **5.6**
* For upgrade from **1.0.0-beta.1**,  **1.0.0-beta.2**, **1.0.0-beta.3** or **1.0.0-beta.4** use command:
```bash
php app/console oro:platform:upgrade20 --env=prod --force
```

* For upgrade from **1.0.0-beta.5** use command:
```bash
php app/console oro:platform:update --env=prod --force
```

MultiWebsiteBundle
------------------
* The class `AssetsContext` was removed.

WarehouseBundle
---------------
* The classes `OrderLineItemWarehouseGridListener` and `OrderFormViewListener` was removed.
* The methods `onBuildBefore` and `addConfigElement` were removed from class `InventoryLevelGridListener`.
* The method `addConfigElement` was removed from class `OrderWarehouseGridListener`.
* The method `finishView` was removed from class `OrderFormExtension`.

WebsiteMenuBundle
-----------------
* The class `WebsiteOwnershipProvider` was removed.