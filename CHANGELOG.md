# ChangeLog

Changes between versions.

## Current changes

* remove service provider (moved to main framework component)
* remove factory
* remove unneeded exception classes (moved to main framework component)

## v0.4

### v0.4.1

* Fix relative log file path handling in service provider that was causing a notice
to be thrown

### v0.4.0

* Save logger object to application properties and return it on consecutive calls
to avoid re-initializing the logger on each call
* Prepend log file name with the *logFilePath* configuration option if the file
name is a relative path

## v0.3

### v0.3.0

* Config component no longer a requirement, but a suggestion, required if
Factory or Service Providers are used
* Service Provider and Factory now support only multiple loggers

## v0.2

### v0.2.0

* initial version
