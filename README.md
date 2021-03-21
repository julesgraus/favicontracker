# favicontracker
Enables tracking of users via a favicon tricks in your Laravel project. 
Even when they are incognito. For educational purposes only.

## Installation
* run ```composer require julesgraus/favicontracker``` in the root of your laravel project.
* Publish the config file using ```php artisan vendor:publish --tag=fit``` in the root of your laravel project.

## Usage
More info following soon. Project is not ready to be tested / used.

## How to clear the favicon cache
### Chrome Mac:
Delete ```~/Library/Application Support/Google/Chrome/Default/Favicons```
and ```~/Library/Application Support/Google/Chrome/Default/Favicons-journal```.
Then restart chrome.

### Chrome Windows
Delete ```C:\Users\<your username>\AppData\Local\Google\Chrome\User Data\Default```.
Then restart chrome