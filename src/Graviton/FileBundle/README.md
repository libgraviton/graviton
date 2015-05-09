# File Bundle

This bundle contains an upload service for binary files.

## Creating a new file

```bash
curl -X POST \
     -H 'Content-Type: text/plain' \
     -d "Hello World!" \
     http://localhost/file
```

## Adding metadata to the file

```bash
curl -X PUT \
     -H 'Content-Type: application/json' \
     -d '{"id": "<id>", "links": [{"$ref": "http://localhost/core/app/tablet"}]}' \
     http://localhost/file/<id>
```

## Retrieving the file

```bash
```
