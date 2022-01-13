SlimifyMiddleware
=================

Requirements...
---------------

```json
{
    "php": ">=7.1.0",
    "ext-zlib": "*",
    "slim/psr7": "^0.5",
    "slim/slim": "^4.1",
    "district5/slimify": "*"
}
````

Available middlewares...
------------------------

* `GzipMiddleware`
    * Gzip a response where appropriate and supported.
* `ErrorHandlingMiddleware`
    * Handles error pages and logging