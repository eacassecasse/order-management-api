## API Documentation

Welcome to the mansto API Documentation. Using the instructions and interactive code examples below you will be able to start making API requests in a matter of minutes. If you have an account already and prefer to skip our detailed documentation, you can also jump to our [3-Step Quickstart Guide](http://api.mansto.com/quickstart) right away.

The mansto API was built to deliver products data for any application and use case, from supplier prices to storages quantity all the way, supporting all major programming languages. Our straightfoward API design will make it easy to use the API - continue reading below to get started.
<br>
<br>
## Getting Started

### API Authentication

* * *

The first step to using the API is to authenticate with your mansto account's Bearer Token, which can be found in your account dashboard after login. To authenticate with the API, simply use the base URL and pass your API access key to API's **access_key** parameter.

**Example API Request:**                 &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;[Sign Up to Run API Request](http://accounts.mansto.com/signup)

> `http://api.mansto.com/v1/endpoints/products?`**access_key=YOUR_ACCESS_KEY**`&page=1&limit=10&sort\[\]=id,desc`

**Keep it safe:** Please make sure to keep your API access key and do not expose it in any publicly available part of the application. If you ever want to reset your token, simply head over to your [account dashboard](http://app.mansto.com/dashboard) to do so.
<br>
#### 256-bit HTTPS Encryption

**Available on: Standard Plan and higher**

Clients using the Standard Plan and higher can connect to the mansto using industry-standard SSL (HTTPS) by attaching an **s** to the HTTP protocol as shown in the example API request below.

**Example API Request:**

> **https**`://api.mansto.com/v1/endpoints/...`

If you are currently on the Free Plan and would like to use the HTTPS API in production, please [upgrade your account](http://mansto.com/plan) now. You can also learn more about available plans on our [Pricing Overview](http://mansto.com/pricing).
<br>
<br>
### API Error Codes

Whenever your API request fails, the mansto API will return an error object in lightweight JSON format. Each error object contains an error code, an error type, an error description containing details about the error and the date & time when the error occurred. Below you will find an example error as well as a list of common API errors.

**Example API Error:**

```json
{
    "error": {
        "code": 404,
        "type": "resource_not_found",
        "description": "The requested resource does not exist",
        "date&time": {
            "date": "2022-07-17 22:33:41.941884",
            "timezone_type": 3,
            "timezone": "Europe/Berlin"
        }
    }
}
```

<br>

**Common API Errors:**  
  

|**Code**   | **Type**                           | **Description**                                       |
|:---       |:---                                |:---                                                   |
|400        |invalid_api_function                |User requested a non-existent API function.            |
|400        |missing_query                       |An invalid (or missing) query value has been specified.|
|401        |missing_access_key                  |User did not supply an access key.                     |
|401        |invalid_access_key                  |User supplied an invalid access key.                   |
|403        |usage_limit_reached                 |User has reached his subscription's monthly request allowance. |
|403        |function_access_restricted          |The user's current subscription does not support this API function. |
|403        |https_access_restricted             |The user's current subscription plan does not support HTTPS. |
|403        |inactive_user                       |User account is inactive or blocked.                   |
|404        |resource_not_found                  |User has requested a resource which does not exist.    |
|418        |database_fetch_failure              |Database query has failed.                             |
|500        |request_failed                      |API request has failed.                                |
|602        |no_results                          |The API request did not return any results.            |
