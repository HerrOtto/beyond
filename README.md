# beyond a PHP based CMS

This CMS was created to make static and native PHP pages manageable. A management interface can be introduced without any adjustments to the site. Through slight adjustments, the content can be edited in the second step via the interface of "beyond" in the team.

Features:

* Multi user role based permissions
* Edit PHP files from the admin panel
* Multilingual content
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
* fontawesome (Font Awesome Free license: https://fontawesome.com/license/free)
* jquery (https://jquery.org/license)
* startbootstrap (MIT license)
* ace (AS IS, Copyright (c) 2010, Ajax.org B.V.)
* phpmailer (GNU LESSER GENERAL PUBLIC LICENSE)

## Programming style guide

* Keep it simple
* Don't break backward compatibility
* Internally only UTF8 encoding
* Internally english language only
* Separate changeable files and static files
* Variables, Classes and Functions: Name first character lower case every word starting with an uppercase letter p.e. "$fileName" or "getVariable()"
* Keep all strings single quoted 'example' instead of "example"
* Keep server logs clean (Handle exceptions)

## TODO

* Rename file
* Download file
* Move file
* Search in files
* Save old versions
* Persistent hide/show plugins from editor
* Plugin: Menu builder
* Plugin: Shop
* Plugin: htaccess
* Plugin: backup/restore
* Develop edit users backend from files.php
* Install/Update plugins from repository
* Update cleanup, delete unused files, introduce plugin cleanup
* System compatibility check, permission check (PHP7.3, modules, ...)
* Installer

## Use API

Embedd API base script:

<pre>&lt;head&gt;
...
&lt;script src="/beyond/base.php"&gt;&lt;/script&gt; 
...
&lt;/head&gt;</pre>

You now have access to the following variables:

<pre>beyond_languages // Array of configured languages
beyond_language // Current session language
beyond_api // API class
</pre>

Javascript call to an API function:

<pre>beyond_api.<font color="red">CLASS_NAME</font>.<font color="green">FUNCTION_NAME</font>({
  'parameter1': 'value1',
  'parameter2': 'value2'
}, function (error, data) {
  if (error) {
    alert('Error: ' + data);  
    return;
  }
  console.log(data); // Handle result here
});</pre>

## PHP files

Add beyond functionality to your PHP files by adding this lines in the very beginning:

<pre>&lt;?php
    include_once \_\_DIR\_\_ . '/beyond/inc/init.php';
?&gt;</pre>

## Plugin: content

To output content field from your PHP code: 

<pre>&lt;?php
    $beyond->content->get('fieldNameHere');
    // get($fieldName, $print = true, $language = false)
?&gt;</pre>

## Plugin: blocks

To output global content blocks from your PHP code: 

<pre>&lt;?php
    $beyond->blocks->output('blockNameHere');
    // output($name, $language = false)
?&gt;</pre>

## Plugin: seo

Output HTML header with title and meta tags:

<pre>&lt;head&gt;
...
&lt;?php
$beyond->seo->printHead();
// printHead($language = false)
?&gt;
...
&lt;/head&gt;</pre>

## Plugin: mail

Send an mail:

<pre>&lt;?php
$beyond->mail->send('Subject', 'Body', 'to@mail.address');
// send($to, $subject, $body, $kind = 'text', $language = false, $replyTo = false)
?&gt;</pre>

## Plugin: cookiebox

Include API to head first:

<pre>&lt;head&gt;
...
&lt;script src="/beyond/base.php"&gt;&lt;/script&gt;
..
&lt;/head&gt;</pre>

Add cookie box script to header:

<pre>&lt;head&gt;
...
&lt;script src="/beyond/plugins/cookiebox/cookieboxScript.php"&gt;&lt;/script&gt;  
&lt;link href="/beyond/plugins/cookiebox/cookieboxStyle.php" rel="stylesheet" /&gt;
..
&lt;/head&gt;</pre>

Example _matomo_ implementation:

<pre>&lt;script&gt;
document.addEventListener('DOMContentLoaded', function () {
  if (beyond_cookieboxGetCookie('cookiebox_<font color="red">COOKIENAME</font>') === '1') {
    var _paq = _paq || [];
    _paq.push(["setCookieDomain", "<font color="red">YOUR-DOMAIN-NAME</font>"]);
    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    (function() {
      var u="//<font color="red">YOUR-MATMO.URL</font>/";
      _paq.push(['setTrackerUrl', u+'piwik.php']);
      _paq.push(['setSiteId', '<font color="red">YOUR-SITE-ID</font>']);
      var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
      g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
    })();
  }
}, false);
&lt;/script&gt;</pre>
        