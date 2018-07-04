# GravitonAnalyticsBundle

## Inner Working

### version
Using MongoDB [Aggregation](https://docs.mongodb.com/v3.0/applications/aggregation/) will allow you with this bundle to build simple Aggregation queries.
As this has a major impact on performance of the database it we have limited it's use be configuration files rather than RQL enabled option.

Schema output shall be manually configured as data will not be mapped to match but rather use the query directly.

It's also possible to cache the query for performance. 

#### How to configure which version are reported

To create definition json files please locate the folder `app/config/analytics`.
 
Analytic files will correct definition will be enable and visible at the endpoint api `/analytics`.

##### An example for `app.json`

```
{
  "collection": "App",  // Collection name on where to query
  "route": "app",       // Route to be published to /analytics/{route}
  "type": "object",     // object|array, if to query SINGLE or as ARRAY data output
  "cacheTime": 120,     // The time in seconds to keep the queried data in memory
  "aggregate": [        // Query aggregation.
    {        
    "$group": {
      "_id": "app-count",
      "count": {
        "$sum": 1
      }
    }
  ]  
  },
  "schema": {           // Here we deffine how the data will look like, so that clients can map the output
    "title": "Application usage",
    "description": "Data use for application access",
    "type": "object", 
    "properties": {            // Manually added schema for output data
      "id": {
        "title": "ID",
        "description": "Unique identifier",
        "type": "string"
      },
      "count": {
        "title": "count",
        "description": "Sum of result",
        "type": "integer"
      }
    }
  }
}
```
