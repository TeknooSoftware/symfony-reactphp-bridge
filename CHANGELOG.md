#Teknoo Software - Symfony bridge for React PHP

##[0.0.1-alpha4] - 2017-0
###Fixed
- RequestListener supports header Content Type value as array from React.

###Updated
- Fix minimum requirement of Symfony at Symfony 3.0

###Added
- LoggerInterface support in RequestBridge to log request result and errors during execution
- Logging error and request in RequestBridge
- StdLogger, a logger implementing the PSR3 and forward message to stdout or stderr according to message lever
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

