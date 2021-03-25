# favicontracker
Enables tracking of users via a favicon tricks in your Laravel project. 
Even when they are incognito. For educational purposes only.

## Installation
* run ```composer require julesgraus/favicontracker``` in the root of your laravel project.
* Publish the config file using ```php artisan vendor:publish --tag=fit``` in the root of your laravel project.

## Usage
More info following soon. Project is not ready to be tested / used.

## Verified tracking procedures:
These procedures where followed in order to manually verify the tracking technique per browser:
### Chrome Mac v89.0.4389.90
* Browser was opened.
* Read procedure was started. Resulted in tracking id of 0.
* Browser was closed completely and started.
* Write procedure was started. Resulted in storing a plus zero id.
* Browser was closed completely and started.
* Read procedure was started. Resulted in retrieving the plus zero id.

### Safari Mac v4.0.3 (16610.4.3.1.7)
* Browser was opened
* Read procedure was started. Resulted in tracking id of 0.
* Write procedure was started. Resulted in storing a plus zero id.
* Read procedure was started. Resulted in retrieving the plus zero id.
* Browser was closed completely and started.
* Read procedure was started. Resulted in retrieving the plus zero id.
* Write procedure was started. Resulted in storing a plus zero id.

### Firefox Mac (87.0)
* No tracking procedure seems to work.

### Edge Mac (89.0.774.63)
* Browser was opened.
* Read procedure was started. Resulted in tracking id of 0.
* Browser was closed completely and started.
* Write procedure was started. Resulted in storing a plus zero id.
* Browser was closed completely and started.
* Read procedure was started. Resulted in retrieving the plus zero id.

## How to clear the favicon cache
Make sure you close your browser completely first.

### Chrome Mac:
Delete ```~/Library/Application Support/Google/Chrome/Default/Favicons```
and ```~/Library/Application Support/Google/Chrome/Default/Favicons-journal```.

### Chrome Windows
Delete ```C:\Users\<your username>\AppData\Local\Google\Chrome\User Data\Default```.

### Safari Mac:
Delete everything in ```~/Library/Safari/Favicon Cache/```
Does not always seem to work immediately (Safari 4.0.3)

### Microsoft Edge Mac
Delete ```~/Library/Application Support/Microsoft Edge/Default/Favicons```
and ```~/Library/Application Support/Microsoft Edge/Default/Favicons-journal```.