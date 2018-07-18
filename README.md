# Slow Web Portal

## What is this?

A collection of scripts I put together to access various bits of data that interest me and display them in as light of a format as can be managed.

## But...why?

For crappy Internet connections.  It's really difficult to check baseball scores or stocks or whatever on really slow Internet connections because so many pages load a few megabytes of data just to format things in a pretty way.  These pages are deliberately simple and, some would say, deliberately ugly.  It's also a hobby project to try out various things I don't normally get to use.

## How do I set it up?

You can do it two ways.

### Prerequisites

* API key from LocationIQ
* API key from Alphavantage
* API key from Forecast.io

### Local install

* PHP 7.0 or better
* php-json

Copy the files to a suitable place and set up config.php with the configuration values given below.  Note that cookie auth currently doesn't work with a local install so you'll have to modify the files.  (There's an open issue to generalize this into a function that can be turned on or off and is suitable for either install method.)

### Azure install

* Make a Linux-based Azure web app
* Make a key vault resource, set up an override secret in it
* Set up an Azure application and give it access to the key vault
* Sync the files however you like, probably git deployment (you can mirror this repo once it is public)
* Set app settings with the configuration values given below

### Configuration values

Set these values, either as $ variables in a config.php or as Azure web app settings

* requiredCookie
* forecastApiKey
* forecastLocation
* alphaVantageKey
* alphaVantageStocks
* locationApiKey

#### These are the values for the key vault app

* appid
* appsecret
* subscription
* tenant
* keyvaultname

#### This is the override value stored in the key vault

* overridevalue