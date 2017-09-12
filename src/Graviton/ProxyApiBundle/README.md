# GravitonProxyApiBundle

## Inner Working
Intented as a simple configurable Proxy service. Configure if needed own classes to manipulate in 3 steps the data or
even the request. 

Configuration is Compiled for faster response and validation. 

#### Url 

Endpoint api `/proxy/{proxy_name}/{service}?queryparams=values`.

`proxy name ` Unique ID for a service.
`service ` Will be appended: uri + serviceEndpoint + service ->  proxy.


##### Configuration  `description`

```
parameters:
    graviton.proxy_api.sources:
        proxy1:  # unique ID, called in url: proxy_name
            uri: # http full qualified domain url
            queryAdditionals: # optional appending params to proxy query
            queryParams: # optional Unique params to proxy request
            serviceEndpoint: # optional append to uri on requesting data
            preProcessorService: # optional, valid service id. implement Interface for pre process request data.
            proxyProcessorService: # optional, valid service id. implement Interface for execute proxy request
            postProcessorService: # optional, valid service id. implement Interface for post manipulate result
```


##### An example for `full proxy config`

```
parameters:
    graviton.proxy_api.sources:
        proxy1:
            uri: 'http://gateway.proxy.com'
            queryAdditionals: 
                appid: ap-key-id-xyz
            queryParams:
                name: '{city}'
            serviceEndpoint: /docs
            preProcessorService: 'valid.service.id.step.1'
            proxyProcessorService: 'valid.service.id.step.2'
            postProcessorService: 'valid.service.id.step.3'
```

