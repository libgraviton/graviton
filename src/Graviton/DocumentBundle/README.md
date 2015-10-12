# GravitonDocumentBundle

Simple MongoDB ODM documents used throughout graviton.

## Features

* Basic abstract MongoDB ODM documents
* ``extref`` type for exposing MongoDBRefs as JSON-Reference

### ``extref`` Custom Datatype

Values using the ``extref`` datatype always need to be exposed with a leading dollar sign in their name.

The ``extref`` data type may be used in doctrine schemas (like ``MyDocument.mongodb.xml`` as follows.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mongo-mapping xmlns="http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping.xsd">
  <document name="MyDocument">
    <field fieldName="id" type="string" id="true"/>
    <field fieldName="ref" type="extref"/>
  </document>
</doctrine-mongo-mapping>
```

The serializer also needs to know how to properly expose the field.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<serializer>
  <class name="MyDocument" exclusion-policy="ALL">
    <property name="id" type="string" accessor-getter="getId" accessor-setter="setId" expose="true"/>
    <property name="ref" serialized-name="$ref" accessor-getter="getRef" accessor-setter="setRef" expose="true">
      <type><![CDATA[Graviton\DocumentBundle\Entity\ExtReference]]></type>
    </property>
  </class>
</serializer>
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
        "name": "ref",
        "type": "extref",
        "title": "Link",
        "exposeAs": "$ref",
        "description": "URL-Link"
      }
    ]
  }
}
```
