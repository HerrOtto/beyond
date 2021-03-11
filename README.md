# beyond a PHP based CMS

This CMS was created to make static and native PHP pages manageable. A management interface can be introduced without any adjustments to the site. Through slight adjustments, the content can be edited in the second step via the interface of "beyond" in the team.

Features:

* Multi user role based permissions
* Edit PHP files from the admin panel
* Develop a JSON/AJAX based API
* Configure database tables
* Easy plugin development and some basic plugins out of the box like a shop system or a cookiebox
* Update "beyond" from your browser

Installation:

* Add a directory "beyond" to your webroot
* Download all files from her into the "beyond" directory
* Rename beyond/db/main.json.default to main.json (and mondify)
* Rename beyond/db/database.json.default to database.json (and mondify)
* Open "https://www.your-domain-name.com/beyond/" within your browser and enter "admin" and "password" as credentials

# OpenSource

The software is based on the following other OpenSource projects:

* bootstrap (MIT license)
* codemirror (MIT license)
* fontawesome (Font Awesome Free license: https://fontawesome.com/license/free)
* jquery (https://jquery.org/license)
* startbootstrap (MIT license)

## Programming style guide

* Keep it simple
* Don't break compatibility
* Internally only UTF8 encoding
* Internally english language only
* Separate changeable files and static files
* Variables, Classes and Functions: Name first character lower case every word starting with an uppercase letter p.e. "$fileName" or "getVariable()"
* Keep all strings single quoted 'example' instead of "example"
* Keep server logs clean (Handle exceptions)

## TODO

* Updates 
* Plugin: Content
* Plugin: SEO
* Plugin: Menu builder
* Plugin: Shop
* Plugin: customer login (used by shop)
* Plugin: htaccess
* Install/Update plugins from repository