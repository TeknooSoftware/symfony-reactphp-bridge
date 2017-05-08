#Teknoo Software - Symfony bridge for React PHP

##[0.0.1-alpha5]
###Fixed
- Request Listener pass body to Request Bridge only when Content-Length or Transfer-Encoding are defined into headre,
  following rfc2616. (Not depending of HTTP's method)

### Added
- Request Parser to parse the Request from React PHP with several and simply parser to generate the Symfony Request.
- Allow usage of $_SERVER, cookies, `application/x-www-form-urlencoded`, `multipart/form-data` or `application/json``
  request.

##[0.0.1-alpha4] - 2017-05-04
###Fixed
- RequestListener supports header Content Type value as array from React.
- Issue with Doctrine Connection Factory : Connection is not always automatically closed at end of ReactPHP Request.
    Overload Doctrine configuration to replace the Doctrine Connection Factory to keep in a static context the connection
    to share between requests.

###Updated
- Fix minimum requirement of Symfony at Symfony 3.0

###Added
- LoggerInterface support in RequestBridge to log request result and errors during execution
- Logging error and request in RequestBridge
- StdLogger, a logger implementing the PSR3 and forward message to stdout or stderr according to message level
- Secured server, need React/HTTP ^0.6

##[0.0.1-alpha3] - 2017-03-29
###Fixed
- Enable deep cloning into RequestBridge class to avoid Symfony's Kernel sharing betwin requests. Fix #1

##[0.0.1-alpha2] - 2017-03-05
###Added
- Add a Symfony command
- Transform to bundle to manage the Symfony command

##[0.0.1-alpha1] - 2017-03-04
###Added
- First alpha release
- Request Listener to manage ReactPHP event and retrieve request's body and prepare bridge
- Request Bridge to transform ReactPHP's Request to Symfony Request and Symfony Response to ReactPHP Response and
  manage Symfony handling.

