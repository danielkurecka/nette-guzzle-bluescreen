# Nette Bluescreen for Guzzle
This extension adds a new panel to Tracy Bluescreen for Guzzle HTTP client. 

When Guzzle exception occures, it will log full request/response body and headers. 
It will also pretty print the body when the content type is application/json.

[![Nette Guzzle Bluescreen](https://danielkurecka.github.io/nette-guzzle-bluescreen/guzzle-bluescreen.png)](https://danielkurecka.github.io/nette-guzzle-bluescreen/guzzle-bluescreen.png)

# Installation
`composer require daku/nette-guzzle-bluescreen`

## Usage
Register the extension in config.neon:
```neon
extensions:
	guzzleBluescreen: Daku\Nette\Guzzle\GuzzleBlueScreenExtension
```

## Configuration
```neon
guzzleBluescreen:
	# Set maximum body length (in kB) that will be logged, if exceeded the body will be truncted. Default is 100 kB.
	maxBodyLength: 200
	# Enable/disable pretty print of json responses. Default is true.
	prettyPrint: false
```

## Requirements
PHP >= 7.1\
Nette >= 2.4
