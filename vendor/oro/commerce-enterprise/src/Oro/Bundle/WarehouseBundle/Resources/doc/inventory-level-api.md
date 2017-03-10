Inventory Level API
===================

Table of Contents
-----------------
 - [Description](#description)
 - [GET Inventory Levels](#get-inventory-levels)
 - [PATCH Inventory Level](#patch-inventory-level)
 - [POST Inventory Level](#post-inventory-level)
 - [DELETE Inventory Level](#delete-inventory-level)
 - [DELETE Inventory Levels](#delete-inventory-levels)

Description:
============

Inventory Level APIs are altered by WarehouseBundle.
This is not the complete documentation for Inventory Level APIs! Please use this documentation together with the one from InventoryBundle!
Below you can find what changed.

GET Inventory Levels
==============================

One or more Warehouse ids can be provided in order to filter the received data.

Parameters
----------

### warehouse
Warehouse ID(s).

One or more Warehouse ids can be provided as request query.

E.g.: `filter[warehouse]=1` or `filter[warehouse]=1,2`

Example Request
---------------
http://demo.orocommerce.com/admin/api/inventorylevels?filter[product.sku]=0RT28,1AB92&filter[productUnitPrecision.unit.code]=item&filter[warehouse]=1,2&include=product,productUnitPrecision

Example Request Body
--------------------
No Request Body is required.

Example Response
----------------
```json
{
  "data": [
    {
      "type": "inventorylevels",
      "id": "1",
      "relationships": {
        "product": {
          "data": {
            "type": "products",
            "id": "1"
          }
        },
        "productUnitPrecision": {
          "data": {
            "type": "productunitprecisions",
            "id": "1"
          }
        },
        "warehouse": {
          "data": {
            "type": "warehouses",
            "id": "1"
          }
        }
      },
      "attributes": {
        "quantity": "10.0000000000"
      }
    },
    {
      "type": "inventorylevels",
      "id": "3",
      "relationships": {
        "product": {
          "data": {
            "type": "products",
            "id": "2"
          }
        },
        "productUnitPrecision": {
          "data": {
            "type": "productunitprecisions",
            "id": "2"
          }
        },
        "warehouse": {
          "data": {
            "type": "warehouses",
            "id": "1"
          }
        }
      },
      "attributes": {
        "quantity": "82.0000000000"
      }
    }
  ],
  "included": [
    {
      "type": "products",
      "id": "1",
      "attributes": {
        "sku": "0RT28",
        "hasVariants": false,
        "status": "enabled",
        "variantFields": [],
        "createdAt": "2016-07-26T11:01:40Z",
        "updatedAt": "2016-07-26T11:01:43Z"
      },
      "relationships": {
        "owner": {
          "data": {
            "type": "businessunits",
            "id": "1"
          }
        },
        "organization": {
          "data": {
            "type": "organizations",
            "id": "1"
          }
        },
        "primaryUnitPrecision": {
          "data": {
            "type": "productunitprecisions",
            "id": "1"
          }
        },
        "inventory_status": {
          "data": {
            "type": "prodinventorystatuses",
            "id": "out_of_stock"
          }
        },
        "unitPrecisions": {
          "data": [
            {
              "type": "productunitprecisions",
              "id": "1"
            },
            {
              "type": "productunitprecisions",
              "id": "65"
            }
          ]
        }
      }
    },
    {
      "type": "productunitprecisions",
      "id": "1",
      "attributes": {
        "precision": 0,
        "conversionRate": 1,
        "sell": true
      },
      "relationships": {
        "product": {
          "data": {
            "type": "products",
            "id": "1"
          }
        },
        "unit": {
          "data": {
            "type": "productunits",
            "id": "item"
          }
        }
      }
    },
    {
      "type": "products",
      "id": "2",
      "attributes": {
        "sku": "1AB92",
        "hasVariants": false,
        "status": "enabled",
        "variantFields": [],
        "createdAt": "2016-07-26T11:01:40Z",
        "updatedAt": "2016-07-26T11:01:43Z"
      },
      "relationships": {
        "owner": {
          "data": {
            "type": "businessunits",
            "id": "1"
          }
        },
        "organization": {
          "data": {
            "type": "organizations",
            "id": "1"
          }
        },
        "primaryUnitPrecision": {
          "data": {
            "type": "productunitprecisions",
            "id": "2"
          }
        },
        "inventory_status": {
          "data": {
            "type": "prodinventorystatuses",
            "id": "out_of_stock"
          }
        },
        "unitPrecisions": {
          "data": [
            {
              "type": "productunitprecisions",
              "id": "2"
            },
            {
              "type": "productunitprecisions",
              "id": "66"
            }
          ]
        }
      }
    },
    {
      "type": "productunitprecisions",
      "id": "2",
      "attributes": {
        "precision": 0,
        "conversionRate": 1,
        "sell": true
      },
      "relationships": {
        "product": {
          "data": {
            "type": "products",
            "id": "2"
          }
        },
        "unit": {
          "data": {
            "type": "productunits",
            "id": "item"
          }
        }
      }
    }
  ]
}
```

PATCH Inventory Level
===============================

One Warehouse ID must be provided if there are multiple Warehouses in the system.

Parameters
----------

### warehouse
Warehouse ID.

One Warehouse ID must be provided if there are multiple Warehouses in the system.

Warehouse ID is provided in the `attributes` section.

E.g.:
```json
"attributes": {
  "warehouse": "1"
}
```

Example Request
---------------
http://demo.orocommerce.com/admin/api/inventorylevels/0RT28

Example Request Body
--------------------
```json
{
  "data": {
    "type": "inventorylevels",
    "id": "0RT28",
    "attributes": {
      "quantity": "17",
      "warehouse": "1",
      "unit": "item"
    }
  }
}
```

Example Response
----------------
```json
{
  "data": {
    "type": "inventorylevels",
    "id": "1",
    "attributes": {
      "quantity": 17
    },
    "relationships": {
      "warehouse": {
        "data": {
          "type": "warehouses",
          "id": "1"
        }
      },
      "product": {
        "data": {
          "type": "products",
          "id": "1"
        }
      },
      "productUnitPrecision": {
        "data": {
          "type": "productunitprecisions",
          "id": "1"
        }
      }
    }
  }
}
```

POST Inventory Level
=============================

One Warehouse ID must be provided as a Warehouse relationship if there are multiple Warehouses in the system in order to relate the Inventory Level to a Warehouse.

Parameters
----------

### warehouse
Warehouse ID.

One Warehouse ID must be provided as a Warehouse relationship if there are multiple Warehouses in the system in order to relate the Inventory Level to a Warehouse.

Warehouse ID is provided in the `relationships` section. The Type of the resource must be provided.

E.g.:
```json
relationships": {
  "warehouse": {
    "data": {
      "type": "warehouses",
      "id": "2"
    }
  },
}
```

Example Request
---------------
http://demo.orocommerce.com/admin/api/inventorylevels

Example Request Body
--------------------
```json
{
  "data": {
    "type": "inventorylevels",
    "attributes": {
      "quantity": "17"
    },
    "relationships": {
      "warehouse": {
        "data": {
          "type": "warehouses",
          "id": "2"
        }
      },
      "product": {
        "data": {
          "type": "products",
          "id": "0RT28"
        }
      },
      "unit": {
        "data": {
          "type": "productunitprecisions",
          "id": "set"
        }
      }
    }
  }
}
```

Example Response
----------------
```json
{
  "data": {
    "type": "inventorylevels",
    "id": "133",
    "attributes": {
      "quantity": 17
    },
    "relationships": {
      "warehouse": {
        "data": {
          "type": "warehouses",
          "id": "2"
        }
      },
      "product": {
        "data": {
          "type": "products",
          "id": "1"
        }
      },
      "productUnitPrecision": {
        "data": {
          "type": "productunitprecisions",
          "id": "65"
        }
      }
    }
  }
}
```

DELETE Inventory Levels
=================================

One or more Warehouse ids can be provided in order to filter the deleted data.

Parameters
----------

### warehouse
Warehouse ID(s).

One or more Warehouse ids can be provided as request query.

E.g.: `filter[warehouse]=1` or `filter[warehouse]=1,2`

Example Request
---------------
http://demo.orocommerce.com/admin/api/inventorylevels?filter[product.sku]=0RT28&filter[productUnitPrecision.unit.code]=item&filter[warehouse]=1

Example Request Body
--------------------
No Request Body is required.

Example Response
----------------
No Response Body will be received.
