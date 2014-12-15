# Tres logger

This is the logger package used for [Tres Framework][tf]. This is a stand-alone 
package, which means that it can also be used without the framework.

This logger is a tool to keep track of events, errors or exceptions. It is very
useful, because it gives you the option to backtrace a problem regarding the
application.

In a production environment, it is highly discouraged to show exception/error
messages regarding the application, because it may scare your users away, but 
more importantly: it might expose sensitive information.

By using this tool, you could safely hide all exceptions and (non fatal)
errors: log them instead!

## Requirements
- PHP 5.4 or greater.
- A web server (preferably with .htaccess support).

[tf]: http://tresframework.com
