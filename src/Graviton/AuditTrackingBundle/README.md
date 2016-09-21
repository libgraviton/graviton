# GravitonAuditTrackingBundle

## Inner Auditing tool bundle
This tool is meant to run as a hidden service in order to know what each user request or modifies.
It will not limit nor interfere with the users request but only store the changes and data recived.

### version
* 0.0.1-BETA: 2016/09/19 Basic auditing enabled by default. Testing fase start.

#### Configuration

In the folder `AuditTracking/Resources/config/` you can find a file called `parameters.yml` where you can turn on or off logs.

```yml
parameters:
    graviton_audit_tracking:
        # General on/off switch
        log_enabled: true
        # Localhost and not Real User on/off switch
        log_test_calls: true
        # Store request log also on 400 error
        log_on_failure: false
        # Request methods to be saved, array PUT,POST,DELETE,PATCH...
        requests: []
        # Store full request header request data.
        request_headers: false
        # Store full request content body. if true full lenght, can be limited with a integer
        request_content: false
        # Store reponse basic information. if true full lenght, can be limited with a integer
        response: false
        # Store full response header request data.
        response_headers: false
        # Store response body content
        response_content: false
        # Store data base events, array of events, insert, update, delete
        database: ['insert','update','delete']
        # Store all exception
        exceptions: false
        # Exclude header status exceptions code, 400=bad request, form validation
        exceptions_exclude: [400]
```