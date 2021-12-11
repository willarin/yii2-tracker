yii2-tracker
=========

This extension provides the third party tracking functionality which could be easily integrated into your project. 
The module is also intended for registration of information about a separate user session, 
data about the user's device and browser, his behavior on the site during a visit.

## Documentation

### 1. Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ php composer.phar require willarin/yii2-tracker "*"
```

or add

```
"willarin/yii2-tracker": "*"
```

to the required section of your `composer.json` file.

### 2. Migrations

Before using this extension, we need to prepare the database.

```
php yii migrate --migrationPath=@willarin/tracker/migrations
```

### 3. Usage

Each unique user's URL visit should be registered within a module. 
Our recommendation is to use bariew/yii2-event-component - eg.

```php
'eventManager' => [
            'class' => 'bariew\eventManager\EventManager',
            'events' => [
                'module\controllers\IndexController' => [
                    'beforeAction' => [
                        ['willarin\tracker\models\SessionUrl', 'saveUrlVisit'],
                    ],
                ],
            ]
        ],   
```

Once the extension is installed, modify your application configuration to include:

```php
return [
	'modules' => [
	    ...
            'tracker' => [
                'class' => 'willarin\tracker\Module',
            ]
	    ...
	],
	...
]
```

#### Tables used

_Session_ This table stores data about cookies, server variables for the session, device type, 
OS, browser, click's cost and income received from a specific click.

_SessionUrl_ This table stores data about each url visited by the user, duration of url's visit, 
number of scrolls up and down for a specific url.

_SessionEvent_ This table stores custom data about events, each event could be associated 
with a certain URL

_Person_ This table stores data about customer and his cookie string identifier