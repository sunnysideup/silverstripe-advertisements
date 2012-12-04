###############################################
Advertisements Module
Pre 0.1 proof of concept
###############################################

Developer
-----------------------------------------------
Nicolaas Francken [at] sunnysideup.co.nz

Requirements
-----------------------------------------------
SilverStripe 2.3.0 or greater.
HIGHLY RECOMMENDED:
http://sunny.svnrepository.com/svn/sunny-side-up-general/dataobjectsorter
OR https://github.com/sunnysideup/silverstripe-dataobjectsorter

Documentation
-----------------------------------------------
see:
- http://silverstripe-webdevelopment.com/modules/advertisements
- http://jquery.malsup.com/cycle/options.html
- http://jquery.malsup.com/cycle/browser.html

Installation Instructions
-----------------------------------------------
1. Find out how to add modules to SS and add module as per usual.

2. copy configurations from this module's _config.php file
into mysite/_config.php file and edit settings as required.
NB. the idea is not to edit the module at all, but instead customise
it from your mysite folder, so that you can upgrade the module without redoing the settings.

3. add

<% include AdvertisementsHolder %> to your templates...

4. theme CSS and make sure to set a width and a height for the slides!

5. create your own js code.  If your project is called "mysite" then the location of the file should be:

mysite/javascript/AdvertisementsExecutive.js
