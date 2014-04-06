###############################################
Advertisements
###############################################

Adds slides / advertisements to any Silverstripe
website page using the jQuery cycle extension.


This functionality is typically used on a HomePage
with around five slides providing the visitor with
key information and / or recent news.


Developer
-----------------------------------------------
Nicolaas Francken [at] sunnysideup.co.nz


Requirements
-----------------------------------------------
see composer.json
HIGHLY RECOMMENDED: dataobjectsorter
 - http://sunny.svnrepository.com/svn/sunny-side-up-general/dataobjectsorter OR
 - https://github.com/sunnysideup/silverstripe-dataobjectsorter


Documentation
-----------------------------------------------
Please contact author for more details.

Any bug reports and/or feature requests will be
looked at in detail

We are also very happy to provide personalised support
for this module in exchange for a small donation.

also see:
- http://jquery.malsup.com/cycle/options.html
- http://jquery.malsup.com/cycle/browser.html



Installation Instructions
-----------------------------------------------
1. Find out how to add modules to SS and add module as per usual.

2. Review configs and add entries to mysite/_config/config.yml
(or similar) as necessary.
In the _config/ folder of this module
you should to find some examples of config options (if any).

3. add

<% include AdvertisementsHolder %> to your templates...

4. theme CSS and make sure to set a width and a height for the slides!

5. create your own js code.  If your project is called "mysite" then the location of the file should be:

mysite/javascript/AdvertisementsExecutive.js
