# GravitonProxyBundle

A bundle for proxying thirdparty APIs. At the moment, only APIs using swagger are supported.

## Features
The Bundle supports the following features for `swagger.json` based APIs:
* Exposing all endpoints under `/3rdparty/{apiPrefix}/`
* Forwarding complete requests to the thirdparty API
* Generation of graviton JSON schemas based on the API schema


## Configuration
This bundle can be configured in `config.yml` as follows:

```yml
graviton_proxy:
  sources:
    swagger:
      someApi:
        prefix: someApi
        uri: http://example.org/api/swagger.json
        host: http://api.example.org/
        includeBasePath: false
      anotherApi:
        prefix: anotherApi
        uri: http://example.org/another/api/swagger.json
    custom:
      example:
        prefix: example
        uri: http://example.org/3.0/document
        apiKey: preSharedKey
        queryStringTemplate: shareClass={shareClass}&documentType={documentType}&language={language}
    redirect:
      anotherexapmle:
        prefix: example
        uri: http://example.org

```

See the following table concerning the config nodes which CAN or MUST be set for each API:

| Config Node             | Description                                                                        | Default Value / Behavior (empty if required) |
|-------------------------|------------------------------------------------------------------------------------|----------------------------------------------|
| **prefix**              | The graviton URL prefix where the API endpoints get exposed.                       |                                              |
| **uri**                 | The URL of the API schema (e.g `swagger.json`)                                     |                                              |
| **host**                | Defines the host of the API. This overwrites any host defined in the API schema.   | Use the host defined by the API schema.      |
| **includeBasePath**     | Defines whether the APIs base path should be included in the graviton URL or not.  | `false`                                      |
| **apiKey**              | Defines the preshared key to be granted access to a service                        |                                              |
| **queryStringTemplate** | Defines a querystring templae to be used to request data from the proxied service. |                                              |
|                         | Use '{' and  '}' to encapsulate a string to be replaced.                           |                                              |
| **serviceEndpoint***    | Defines the path to be displayed in the schema                                     |                                              |
