# File Bundle

This bundle contains an upload service for binary files.

Extended documentation can be found [here](https://gravity-platform-docs.nova.scapp.io/api/file/)

## Configuration

Due to the dependency on gaufrette\file at least one *adapter* and the *filesystems.file_service*
has to be configured in config.yml:

```yml
knp_gaufrette:
    adapters:
        local:
            local:
                directory: '%kernel.root_dir%/files'
                create: true
        s3:
            aws_s3:
                service_id: 'graviton.aws_s3.client'
                bucket_name: '%graviton.aws_s3.bucket_name%'
                options:
                    create: true
    filesystems:
        file_service:
            adapter: %graviton.file.gaufrette.backend%
```

There is further the option to configure the access to the Amazone Webservices S3 for file storage.
Use the following configuration parameters to do so:

```yml
    graviton.file.backend: (local|s3)
    graviton.file.s3.endpoint: (the S3 access host)
    graviton.file.s3.key: (the S3 client key)
    graviton.file.s3.secret: (the S3 preshared secret)
    graviton.file.s3.bucket_name: (the location of the files on S3. Usually: graviton-dev-bucket) 
```

>**NOTICE**
>In case an environment variable named *VCAP_SERVICES* is available every configuration option accessible via parameter.yml will be replaced by the settings provided by *VCAP_SERVICES*. 


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
