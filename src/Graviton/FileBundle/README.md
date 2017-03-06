# File Bundle

This bundle contains an upload service for binary files.

Extended documentation can be found [here](https://gravity-platform-docs.scapp.io/api/file/)

## Configuration

Due to the dependency on gaufrette\file at least one *adapter* and the *filesystems.file_service*
has to be configured in config.yml:

```yml
oneup_flysystem:
    adapters:
        localfile:
            local:
                directory: '%kernel.root_dir%/files'
    filesystems:
        graviton:
            adapter: localfile
```

For other storages (such as S3) and more, additional adapters can be installed and configured on demand.

Please see [http://flysystem.thephpleague.com/](http://flysystem.thephpleague.com/) for a list of available adapters and their configuration.

## Usage
### Creating a new file

```bash
curl -X POST \
     -H 'Content-Type: text/plain' \
     -d "Hello World!" \
     http://localhost/file
```

### Adding metadata to the file

```bash
curl -X PUT \
     -H 'Content-Type: application/json' \
     -d '{"id": "<id>", "links": [{"$ref": "http://localhost/core/app/tablet"}]}' \
     http://localhost/file/<id>
```

### Retrieving the file

```bash
curl -X PUT \
     -H 'Accept: text/plain' \
     http://localhost/file/<id>
```

## Creating a new file and sending meta data in one request
Since this is basically a form submit the information is send as form fields:
- *metadata* » use for the metadata formerly sent as payload in step 2
- *upload* » use to send the file to be stored

```bash
curl -X POST \
     -F 'metadata={"action":[{"command":"print"}]}' \
     -F upload=@test.txt \
     http://localhost/file
```

In addition to the POST method, *multipart/form-data* was implemented for the PUT method as well.
In order to update a file and its' metadata simultaneously, one needs to GET the file meta information first by 
sending a request with the *Accept: application/json* header added.

```bash
curl -X PUT \
    -F 'metadata={"id": "myPersonalFile","links":[],"metadata":{"action":[{"command":"print"}]}}' \
    -F upload=@example.jpg \ 
    http://example.org/file/myPersonalFile
```

## File content hashing : hash
Property file Hash is meant to easily know if file content has changed.
The value can either be calculated on client side by sending the information via Post/Put param or in query URL, or server side.
- *hash* » Optional, It's by default a sha256 based encoding of file content.

```bash
curl -X POST -F \
    'metadata={"links":[],"metadata": {"action":[], "hash":"custom-hash","additionalInformation":"some-extra"}}' \
     -F upload=@Karta-2013_PSD_web.jpg 'http://localhost:8000/file' -v
```
Updating some data.
```bash
curl -X PUT -F \
    'metadata={"links":[],"metadata": {"action":[],"additionalInformation":"update-extra"}}'
    'http://localhost:8000/file/{fileId}' -v
```
