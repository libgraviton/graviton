# GravitonDocumentBundle

Simple MongoDB ODM documents used throughout graviton.

## Features

* Basic abstract MongoDB ODM documents
* ``extref`` type for exposing MongoDBRefs as JSON-Reference

### ``extref`` Customer Datatype

The ``extref`` data type may be used in doctrine schemas (like ``MyDocument.mongodb.xml`` as follows.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mongo-mapping xmlns="http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping.xsd">
  <document name="MyDocument">
    <field fieldName="id" type="string" id="true"/>
    <field fieldName="link" type="extref"/>
  </document>
</doctrine-mongo-mapping>
```

If you want to use it in a JSON definition for the generator a link field would look as follows.

```js
{
  "id": "MyDocument",
  "service": {
    "routerBase": "/my/document",
    "fixtures": [],
  },
  "target": {
    "indexes": [],
    "relations": [],
    "fields": [
      {
        "name": "id",
        "type": "varchar",
        "title": "ID",
        "description": "Unique identifier for an item."
      },
      {
        "name": "link",
        "type": "extref",
        "title": "Link",
        "description": "URL-Link"
      }
    ]
  }
}
```
