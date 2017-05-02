<?php
/**
 * Created by PhpStorm.
 * User: taachja1
 * Date: 03.04.17
 * Time: 11:15
 */

namespace Graviton\GeneratorV2Bundle\Service;


use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\JsonDefinitionField;
use Graviton\GeneratorBundle\Definition\Schema\XDynamicKey;

class SchemaMapper
{


    public function convert(JsonDefinition $definition)
    {
        $schema = [
            "x-documentClass" => $definition->getId()
        ];

        if (!$definition->getDef()->getTarget()) {
           // return $schema;
        }

        $requiredFields = [];
        $searchableFields = [];
        $readOnlyFields = [];


        $schema = [
            "x-documentClass" => $definition->getId(),
            "title" => $definition->getTitle(),
            "description" => $definition->getDescription(),
            "x-recordOriginModifiable" => $definition->isRecordOriginModifiable(),
            "properties" => []
        ];
        if (method_exists($definition, 'isVersionedService')) {
            $schema["x-versioning"] = $definition->isVersionedService();
        }

        $exposeFieldNames = array_flip(['title', 'type', 'readOnly', 'description', 'recordOriginException']);

        if (!$definition->getDef()->getTarget()) {
            return $schema;
        }

        /** @var JsonDefinitionField $field */
        foreach ($definition->getFields() as $field) {
            $def = $field->getDefAsArray();
            $key = $field->getName();
            if (isset($def['required']) && $def['required']) {
                $requiredFields[] = $field->getName();
            }
            if (isset($def['searchable']) && $def['searchable']) {
                $searchableFields[] = $field->getName();
            }
            if (isset($def['readOnly']) && $def['readOnly']) {
                $readOnlyFields[] = $field->getName();
            }

            $data = array_intersect_key($def,$exposeFieldNames);
            $schema["properties"][$key] = $data;

            /** @var XDynamicKey $xkex */
            if (method_exists($field, 'getXDynamicKey') && $xkex = $field->getXDynamicKey()) {
                $schema["properties"][$key]["x-dynamic-key"] = [
                    "document-id" => $xkex->getDocumentId(),
                    "repository-method" => $xkex->getRepositoryMethod(),
                    "ref-field" => $xkex->getRefField()
                ];
            }
        }

        $schema["required"] = $requiredFields;
        $schema["searchable"] = $searchableFields;
        $schema["readOnlyFields"] = $readOnlyFields;

        return $schema;


    }


    /*
     *

    {

  "x-documentClass": {{ (base ~ 'Document\\' ~ document) | json_encode() }},

{% if json is defined %}
  "description": {{ json.getDescription()|json_encode() }},
{% else %}
  "description": "@todo replace me",
{% endif %}

  "x-versioning": {{ isVersioning|json_encode() }},

{% if noIdField is not defined %}
  "x-id-in-post-allowed": false,
{% else %}
  "x-id-in-post-allowed": true,
{% endif %}

{% if json is defined %}
    "title": "{{ json.getTitle() }}",
{% endif %}

  "properties": {
{% set requiredFields = [] %}
{% set searchableFields = [] %}
{% set readOnlyFields = [] %}

{% if idField is defined %}
      {% if idField.required is defined and idField.required == true %}
      {% set requiredFields = requiredFields|merge(['id']) %}
      {% endif %}
{% endif %}



{% for field in fields %}
    "{{ field.fieldName }}": {
        "title": {{ field.title|json_encode() }},

{% if field.collection is defined and field.type == 'extref' %}
      "collection": {{ field.collection|json_encode() }},
{% endif %}

{% if field.readOnly is defined and field.readOnly == true %}
      "readOnly": {{ field.readOnly|json_encode() }},
{% endif %}

{% if field.recordOriginException is defined and field.recordOriginException == true %}
      "recordOriginException": {{ field.recordOriginException|json_encode() }},
{% endif %}

{% if field.xDynamicKey is defined and field.xDynamicKey != null %}
      "x-dynamic-key": {
          "document-id": "{{ field.xDynamicKey.documentId }}",
          "repository-method": "{{ field.xDynamicKey.repositoryMethod }}",
          "ref-field": "{{ field.xDynamicKey.refField }}"
      },
{% endif %}

{% if field.constraints is defined and field.constraints != null %}
      "x-constraints": {{ field.constraints|json_encode() }},
{% endif %}

{% if field.description is defined and field.description != '' %}
      "description": {{ field.description|json_encode() }}
{% else %}
      "description": "@todo replace me"
{% endif %}

{% if field.required is defined and field.required == true %}
    {% set requiredFields = requiredFields|merge([field.fieldName]) %}
{% endif %}

{% if field.searchable is defined and field.searchable > 0 %}
    {% set searchableFields = searchableFields|merge([field.fieldName]) %}
{% endif %}

{% if field.readOnly is defined and field.readOnly == true %}
    {% set readOnlyFields = readOnlyFields|merge([field.fieldName]) %}
{% endif %}

    },
{% endfor %}



    "id": {
      "title": "ID",
      "description": "Unique identifier"
{% if isrecordOriginFlagSet %}
    },
    "recordOrigin": {
      "title": "record origin",
      "description": "A small string like 'core' to determine the record origin. Documents from some sources must not be modified. The 'core' value is defined as readonly by default."
{% endif %}
    }
  },

{#
the whole recordOrigin thing is kinda messed up as you need 2 vars to correctly detect what should be done.
for schema, we condense it so recordOriginModifiable holds the whole truth in one..
 #}
{% if (recordOriginModifiable is defined and recordOriginModifiable == "false") and
  (isrecordOriginFlagSet is defined and isrecordOriginFlagSet == true) %}
  "recordOriginModifiable": false,
{% else %}
  "recordOriginModifiable": true,
{% endif %}
  "required": {{ requiredFields|json_encode() }},
  "searchable": {{ searchableFields|json_encode() }},
  "readOnlyFields": {{ readOnlyFields|json_encode() }}
}


     *
     *
     *
     *
     */
}