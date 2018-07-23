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

#### Pre-requisites

* PHP 7.0 or better
* php-json

#### Steps

* Copy the files to a suitable place
* Set up config.php with the configuration values given below
* Set installtype to "onprem"
* Set the overridevalue to be the codeword you want to use to permit access.

### Azure install

#### Pre-requisites

* Make a Linux-based Azure web app
* Make a key vault resource, set up an override secret in it (see below for config settings)
* Set up an Azure application and give it access to the key vault

#### Steps

* Sync the files however you like, probably git deployment (you can mirror this repo once it is public)
* Set app settings with the configuration values given below
* Set installtype to "azure"
* Set overridevalue to be the name of the secret stored in the key vault

### Configuration values

Set these values, either as $ variables in a config.php or as Azure web app settings

* requiredCookie
* forecastApiKey
* forecastLocation
* alphaVantageKey
* alphaVantageStocks
* locationApiKey
* installtype

#### Require auth?

Set this value to 0 if you don't want pages to check for authentication (that is, the overridevalue codeword) before loading or 1 if you do.

* doauth

#### These are the values for the key vault app

* appid
* appsecret
* subscription
* tenant
* keyvaultname

#### Two possible choices for this value

It is either the codeword to permit access (onprem install) or the name of the secret stored in the key vault (azure install).

* overridevalue