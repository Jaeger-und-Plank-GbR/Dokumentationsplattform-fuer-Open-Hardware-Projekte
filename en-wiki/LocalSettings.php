<?php
# This file is for MediaWiki 1.38.6 testwiki.oh-docu.org
# Remark: if you use Apache, take also care of hidden .htaccess in 
# the Wiki’s root directory (read https://www.mediawiki.org/wiki/Manual:Short_URL/Apache)

# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
        exit;
}

$CONFIG_DOCUWIKI_DOMAIN="testwiki.oh-docu.org"; // in apache testwiki.oh-docu.org is defined
$CONFIG_DOCUWIKI_HTTPPREFIX="https";
$CONFIG_BASE_PATH="/some/root-path/to/Dokumentationsplattform-fuer-Open-Hardware-Projekte"; // Wurzelpfad zu diesem Wiki OHNE scriptpath (vermutlich)
$CONFIG_DOCUWIKI_ARTICLEPATH="/wiki/$1";
$CONFIG_DOCUWIKI_SCRIPTPATH="/en-wiki"; /* wgScriptPath="/en" oder /de /fr usw. wäre als scriptpath sicher geeigneter, wenn man mehrsprachige Wikis aufsetzen will */
$CONFIG_DOCUWIKI_DATABASE="testwiki_oh_docu_wiki_en";
$CONFIG_DOCUWIKI_LANGUANGE="en";

/* falls Wiki über ln --symbolic im htdocs Verzeichnis, dann besser den MW_INSTALL_PATH setzen  */
putenv ("MW_INSTALL_PATH=$CONFIG_BASE_PATH" );
if(getenv( 'MW_INSTALL_PATH' ) ) { 
  $IP = getenv( 'MW_INSTALL_PATH' );
} else {// __FILE__ resolves symlinks but getcwd() does not
  // $GLOBALS['IP'] = dirname( __FILE__ );
  $IP = getcwd( );
}


/* DEBUG section
 * 
 * Think also of using $wgHooks onBeforePageDisplay (?on the end of LocalSettings.php?), 
 * because it keeps all layout in order aso.., See also https://doc.wikimedia.org/mediawiki-core/master/php/classOutputPage.html
 * Example:
  
  $wgHooks['BeforePageDisplay'][] ='onBeforePageDisplay';
  function onBeforePageDisplay( OutputPage &$out, Skin &$skin )
  {    
      $out->addWikiTextAsContent(
         "<pre>DEBUG\n" 
        . print_r($_SESSION, true) . "\n<hr>\n"
        . print_r($_COOKIE, true) . "\n<hr>\n"
        . print_r($wgCookieDomain, true)
      );
      return true;
  };
 */
$CONFIG_SHOW_DEBUG = true;
if ($CONFIG_SHOW_DEBUG) {
  $wgShowExceptionDetails = true;
  $wgShowDBErrorBacktrace = true;
  $wgShowSQLErrors = true;
} else {
  error_reporting( E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
  // echo "Huhu … ;-)";
  ini_set('display_startup_errors', 1);
  ini_set('display_errors', 0 );
//   ini_set('display_errors', E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED );
}

# Site language code, should be one of the list in ./languages/data/Names.php
$wgLanguageCode = $CONFIG_DOCUWIKI_LANGUANGE;

$wgFetchCommonsDescriptions = true;

$wgShowHostNames = true;

$wgSitename = "Testwiki for Open Hardware Documentation";

$wgScriptPath = "${CONFIG_DOCUWIKI_SCRIPTPATH}";

## To enable image uploads, make sure the 'images' directory
## is writable, then set this to true:
$wgEnableUploads = true;
$wgMaxImageArea = 2.5e7; # Pixels 25.000.000    Size: 5000×5000 (https://www.mediawiki.org/wiki/Manual:$wgMaxImageArea)

$wgArticlePath = "${CONFIG_DOCUWIKI_ARTICLEPATH}";
// $wgArticlePath = '/wiki/$1';

$wgUsePathInfo = true; // AP: see perhaps also if hidden .htaccess are neccessary

## The protocol and server name to use in fully-qualified URLs
// $wgServer = "https://testwiki.oh-docu.org";
$wgServer = "${CONFIG_DOCUWIKI_HTTPPREFIX}://${CONFIG_DOCUWIKI_DOMAIN}";

## The URL path to static resources (images, scripts, etc.)
$wgResourceBasePath = $wgScriptPath;

## The URL path to the logo.  Make sure you change this from the default,
## or else you'll overwrite your logo when you upgrade!

$wgLogos = [
        '1x' => "$wgResourceBasePath/resources/assets/change-your-logo.svg",
        'icon' => "$wgResourceBasePath/resources/assets/change-your-logo-icon.svg",
];


## UPO means: this is also a user preference option

$wgEnableEmail = true;
$wgEnableUserEmail = true; # UPO

$wgEmergencyContact = "some-mail-address-of-wiki-manager@here-on-earth.org";
$wgPasswordSender = "some-mail-address-of-wiki-manager@here-on-earth.org";

$wgEnotifUserTalk = false; # UPO
$wgEnotifWatchlist = false; # UPO
$wgEmailAuthentication = true; /* $wgEmailAuthentication true  Standard */
$wgEmailConfirmToEdit = false; /* AP Voreinstellung false ~ Require users to confirm email address before they can edit, true to enable.*/
// $wgEmailAuthentication = true; 

## Database settings
$wgDBtype = "mysql";
$wgDBserver = "localhost";
$wgDBprefix = ""; # MySQL specific 
$wgDBname = $CONFIG_DOCUWIKI_DATABASE;
$wgDBuser = "wiki_user_for_mysql"; # 
$wgDBpassword = "vutha2aePhaedeim6obu";

$wgCookieDomain = ".oh-docu.org";
$wgSecureLogin = true; // forces users to authenticate using https


/* CrossSiteAJAXdomains for api requests. 
 
 - see https://www.mediawiki.org/wiki/Manual:CORS (cross-origin resource sharing)
 - https://www.freecodecamp.org/news/access-control-allow-origin-header-explained/
 - see also in the Apache sub-domain settings!
$wgCrossSiteAJAXdomains = [
    '*.some.other.wiki'
];
*/

$wgAllowDisplayTitle = true;
// $wgRestrictDisplayTitle = false; // AP keep default, if a page title should be named completely different then move the page and create a redirect


# MySQL table options to use during installation or update
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=binary";

## Shared memory, cache settings
  /* Set $wgCacheDirectory to a writable directory on the web serve
  * to make your wiki go slightly faster. The directory should not
  * be publically accessible from the web. 
  */
  $wgCacheDirectory = "$IP/cache"; /* AP verändert: cache sollte für jedes Wiki lokal sein (auch für SMW $smwgConfigFileDir genutzt) */
  $wgMainCacheType = CACHE_ACCEL; // $wgMainCacheType = CACHE_NONE;
  $wgMessageCacheType = CACHE_ACCEL;
  $wgSessionCacheType = CACHE_DB; // AP try to solve login problems (https://www.mediawiki.org/wiki/Manual:How_to_debug/Login_problems)
  $wgUseLocalMessageCache = true; /* AP: Voreinstellung false, obachte maintenance/rebuildLocalisationCache.php */ 
$wgMemCachedServers = [];
  $wgParserCacheType = CACHE_DB; /* Voreinstellung CACHE_ANYTHING */
  $wgUseGzip = true; /* Manual:$wgUseGzip ~ Use GZip to store cached pages. (default: false) */
  $wgEnableSidebarCache = true;
  $wgMiserMode = true; /* Manual:$wgMiserMode ~ Miser mode is a mode intended to reduce load on server farms running a large number of wikis. */
  $wgRevisionCacheExpiry = 3*24*3600; /* Manual:$wgRevisionCacheExpiry ~ (default: 86400 * 7 = 604800) */
  $wgParserCacheExpireTime = 14*24*3600; /* Manual:$wgParserCacheExpireTime Expiration time (in seconds) of cached parser information. Defaults to 24 hours. */


# InstantCommons allows wiki to use images from https://commons.wikimedia.org
$wgUseInstantCommons = false;

# Periodically send a pingback to https://www.mediawiki.org/ with basic data
# about this MediaWiki instance. The Wikimedia Foundation shares this data
# with MediaWiki developers to help guide future development efforts.
$wgPingback = false;

## If you use ImageMagick (or any other shell command) on a
## Linux server, this will need to be set to the name of an
## available UTF-8 locale
$wgShellLocale = "C.UTF-8";

$wgSecretKey = "ooheRahs0quo1hoogh9daem7ieK9Ohtouj6uuNgoomaveaWoodiejaiChaey6Pe4";

# Site upgrade key. Must be set to a string (default provided) to turn on the
# web installer while LocalSettings.php is in place
$wgUpgradeKey = "ao3you0riuzie4aiNgae"; # ???


# Enabled skins.
$wgDefaultSkin = 'chameleon'; /* names, ie 'vector', 'monobook' #$wgDefaultSkin = "vector"; */
// wfLoadSkin( 'MinervaNeue' );
wfLoadExtension( 'Bootstrap' );
wfLoadSkin( 'chameleon' );
 $egChameleonLayoutFile= __DIR__ . '/skins/chameleon/layouts/standard.xml';
 # $egChameleonLayoutFile= __DIR__ . '/skins/chameleon/layouts/iog_custom.xml'; // based on navhead something
  ## Default skin: you can change the default skin. Use the internal symbolic
  //  ingenieure-ohne-grenzen.org blue
    $egChameleonExternalStyleVariables = [
        'blue' => 'rgb(0 169 224)',
        'primary' => 'rgb(0 169 224)',
    ];

# The following skins were automatically enabled:
// wfLoadSkin( 'MonoBook' ); /* AP: entfernt, nutzt keiner mehr ;-) */
wfLoadSkin( 'Timeless' );
wfLoadSkin( 'Vector' );


# End of automatically generated settings.
# Add more configuration options below.
$wgNamespacesWithSubpages[NS_MAIN] = true;
$wgExternalLinkTarget = '_blank';
$wgFileExtensions = array('png', 'gif', 'jpg', 'jpeg', 'doc',
    'xls', 'mpp', 'pdf', 'ppt', 'tiff', 'bmp', 'docx', 'xlsx',
    'pptx', 'ps', 'odt', 'ods', 'odp', 'odg', 'csv', 'zip', 'tar');

$extCAD = array('scad', 'f3d', 'smt', 'FCStd', '3D-pdf', '3dxml', 'ai', 'asm', 'bip', 'bmp', 'catpart', 'cgr', 'dae', 'dft', 'dgn', 'dwf', 'dwg', 'dxb', 'dxf', 'easm', 'edrw', 'eprt', 'hcg', 'hsf', 'iam', 'idw', 'ifc', 'iges', 'igs', 'ipn', 'ipt', 'it', 'jpg', 'jt', 'model', 'mts', 'nastran', 'obj', 'par', 'pdf', 'plt', 'png', 'prt', 'psd', 'psm', 'sat', 'sev', 'skp', 'step', 'stl', 'svg', 'tif', 'u3d', 'vda', 'vrml', 'wrl', 'x_b', 'x_t', 'xgl', 'xml', 'xmt_bin', 'xmt_txt', 'xps', 'zgl');

$wgFileExtensions = array_merge($wgFileExtensions, $extCAD);

#Default user options:
$wgDefaultUserOptions['riched_disable']               = false;
$wgDefaultUserOptions['riched_start_disabled']        = true;
$wgDefaultUserOptions['riched_use_toggle']            = true; 
$wgDefaultUserOptions['riched_use_popup']             = false;
$wgDefaultUserOptions['riched_toggle_remember_state'] = true;
$wgDefaultUserOptions['riched_link_paste_text']       = true;
$wgDefaultUserOptions['searchlimit']                  = 50; /* searchlimit 20 default */

// wfLoadExtension('WYSIWYG'); // AP: nicht in en.oho.wiki
wfLoadExtension('WikiEditor');

$wgDefaultUserOptions['usebetatoolbar'] = 1;
$wgDefaultUserOptions['usebetatoolbar-cgd'] = 1; // Enable dialogs for inserting links, tables and more


wfLoadExtension( 'SemanticMediaWiki' );
  enableSemantics( parse_url( $wgServer, PHP_URL_HOST ) );
  $smwgQueryResultCacheType = CACHE_ANYTHING;
  $smwgRemoteReqFeatures = SMW_REMOTE_REQ_SEND_RESPONSE;
  $smwgEnabledQueryDependencyLinksStore = true; /* Help:$smwgEnabledQueryDependencyLinksStore 
    Allows to enable tracking and storing of dependencies of embedded queries  */
  
  // ZUTUN if elif else smw-config
  $smwgConfigFileDir=$wgCacheDirectory; // AP verändert (SMW 3.0.1+ https://www.semantic-mediawiki.org/wiki/Help:Configuration)
  // $smwgEnabledCompatibilityMode = true; // AP war früher an, veraltet in SMW 2.4.0 bis 3.1.2
  // $smwgEnabledFulltextSearch = true;    // AP war früher an, Voreinstellung: false 
  $smwgQMaxInlineLimit = 20000;
  $smwgQMaxLimit = 20000;
  $smwgQMaxSize = 50;
  $smwgQMaxDepth = 50;
  $smwgFieldTypeFeatures = SMW_FIELDT_CHAR_NOCASE | SMW_FIELDT_CHAR_LONG;
  $smwgPropertyListLimit['subproperty'] = 50;
  $smwgPropertyListLimit['redirect'] = 50;
  
wfLoadExtension( 'HeaderTabs' ); // AP für Formulare statt {{#mwIncludeHTML:newprojecttabs.html}}

wfLoadExtension( 'Gadgets' ); /* AP: added for technical Gadgets (WikiEditor help aso.) */

wfLoadExtension( 'InputBox' ); 

wfLoadExtension('PageForms');
//   $wgPageFormsRenameEditTabs = true; /* "edit with form" tab to "edit" */
  $wgPageFormsRenameMainEditTab = true; /* renames only the "edit" tab to "edit source"  */

wfLoadExtension( 'SemanticResultFormats' );
  # $srfgFormats = [ 'listwidget' ];

wfLoadExtension( 'ParserFunctions' ); /* AP: ZUTUN stimmt das noch */
  $wgPFEnableStringFunctions = true; /* AP: ZUTUN stimmt das noch */
  $wgPFStringLengthLimit = 10000;    /* AP: ZUTUN stimmt das noch */

wfLoadExtension( 'EmbedVideo' );

// wfLoadExtension( 'MsUpload' );

wfLoadExtension( 'CategoryTree' );
  $wgCategoryTreeMaxDepth = 10;
wfLoadExtension( 'Nuke' );

wfLoadExtension( 'Arrays' );

$wgAllowSiteCSSOnRestrictedPages = true;
# $wgUseCategoryBrowser = true; 
 /* $wgUseCategoryBrowser @deprecated MW 1.38 Enable/Disable experimental breadcrumb (or dmoz-style) category browsing. 
  * This configuration parameter was removed in MediaWiki 1.38 and replaced with Extension:CategoryExplorer.
  */

$wgVerifyMimeType = false;

// wfLoadExtension('PipeEscape'); /* AP: ZUTUN kann das entfernt werden, um Abhängigkeiten zu verringern */

wfLoadExtension('HtmlSpecialFunctions'); /* AP: eigene geschriebene Erweiterung verschiedene Listenausgaben und anderes */
/* Error: Call to undefined method User::getRights() unter MW 1.38.6
 * https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/core/+/REL1_34/RELEASE-NOTES-1.34
 * User::getRights() and User::$mRights have been deprecated. Use PermissionManager::getUserPermissions() instead.
 * */
  $wgHtmlIncludeBlocks = array('certificationrequest', 'certificationadmin', 'certificationreview'); // AP 2023-05-23 12:14:36: wgHtmlIncludeBlocks in HtmlSpecialFunctions::mwIncludeHTML


wfLoadExtension( 'Interwiki' );
// To grant sysops permissions to edit interwiki data
  $wgGroupPermissions['sysop']['interwiki'] = true;

wfLoadExtension( 'Variables' );

// wfLoadExtension( 'ContactPage' );

// wfLoadExtension( 'AJAXPoll' );

wfLoadExtension( 'CookieWarning' );
  $wgCookieWarningEnabled = true;
  $wgCookieWarningMoreUrl = '';
  $wgCookieWarningGeoIPLookup = '';
  $wgCookieWarningGeoIPServiceURL = '';

wfLoadExtension( 'WikiSEO' );

wfLoadExtension( 'PageExchange' ); // AP für Export von Paketen zur Komplettbenutzung (Formulare, Vorlagen, CSS, Erweiterungsabhängigkeiten usw.)
  // Both hold a set of URLs; the first holds JSON URLs, …, one per line. The first variable would be populated in the following way:
  // $wgPageExchangePackageFiles[] = 'https://example.com/my-package-file.json';

  // while the second holds URLs of pages that themselves hold a set of JSON URLs ...while the second would be populated like this:
  // $wgPageExchangeFileDirectories[] = 'https://example.com/all-my-package-files.txt';
  $wgGroupPermissions['bureaucrat']['pageexchange'] = true;
  $wgGroupPermissions['sysop']['pageexchange']      = true;

wfLoadExtension( 'MultimediaViewer' ); 
$wgGalleryOptions = [
  'imagesPerRow' => 0, // Default number of images per-row in the gallery. 0: Adapt to screensize
  'imageWidth' => 180, // Width of the cells containing images in galleries (in "px")
  'imageHeight' => 180, // Height of the cells containing images in galleries (in "px")
  'captionLength' => true, // Length of caption to truncate (in characters) in special pages or when the showfilename parameter is used
                           // A value of 'true' will truncate the filename to one line using CSS.
                           // Veraltet seit 1.28. Default value of 25 before 1.28.
  'showBytes' => true, // Show the filesize in bytes in categories
    'mode' => 'packed', // One of "traditional", "nolines", "packed", "packed-hover", "packed-overlay", "slideshow" (1.28+)
];


// TODO ZUTUN weiter bereinigen
wfLoadExtension( 'CommentStreams' ); # adds NS_COMMENTSTREAMS
wfLoadExtension( 'DiscussionThreading' );
  $wgSectionThreadingOn = True;
  
wfLoadExtension( 'Moderation' );
  $wgModerationEnable = false; /* was true */
  $wgModerationEmail = 'some-email-for-moderation@address.org';
  $wgModerationNotificationEnable = true;
  $wgModerationNotificationNewOnly = false;

wfLoadExtension( 'SyntaxHighlight_GeSHi' );

wfLoadExtension( 'MassEditRegex' );
  $wgGroupPermissions['masseditregexeditor']['masseditregex'] = true;
  $wgGroupPermissions['sysop']['masseditregex'] = true;

/* AP: gesetzte Gruppenrechte siehe Special:ListGroupRights
 * ZUTUN: Rechtemodell überdenken, Vorschlag: 
 * - Rechte gründlich überdenken, manches ist anfangs True dann später aber False gesetzt: ZUTUN Dopplungen herausnehmen
 * - Bürokraten-Ebene (Nutzerverwaltung, aber keine Systemarbeiten), 
 * - Moderatoren-Redaktionelle-Ebene (inhaltliche Aufgaben)
 * - Hauptverwalter (sysop: alle Rechte, Systemarbeiten)
 * ZUTUN: Nutzer aus PartnerWiki (cdata) erlauben anzumelden
 */

/* values that should be TRUE for all users */
$wgGroupPermissions['*']['editmyprivateinfo'] = true;
$wgGroupPermissions['*']['read']              = true; # AP war für IOG false;
/* values that should be FALSE for all users */
$wgGroupPermissions['*']['comment']           = false;
$wgGroupPermissions['*']['createaccount']     = false; # AP geändert: false (2023-06-08 19:18:45)
$wgGroupPermissions['*']['createpage']        = false;
$wgGroupPermissions['*']['edit']              = false;
$wgGroupPermissions['*']['viewedittab']       = false; # Extension:PageForms
$wgGroupPermissions['*']['writeapi']          = false;

/* AP hinzugefügt (2023-06-08 18:31:14)
$wgGroupPermissions['*']['autocreateaccount']    = true; 
$wgGroupPermissions['user']['autocreateaccount'] = true; 
  Test ob Benutzerwechsel dann auch vorhandenes Konto selbsttätig auf das hiesige Wiki überträgen wird
  aber es fehlen noch irgendwelche Einstellungen
*/

$wgGroupPermissions['autoconfirmed']['commentlinks'] = false;


// AP values with FALSE $wgGroupPermissions['user']
$wgGroupPermissions['user']['createclass']   = false; # Extension:PageForms
$wgGroupPermissions['user']['multipageedit'] = false; # Extension:PageForms
$wgGroupPermissions['user']['createaccount'] = false;

// AP values with TRUE $wgGroupPermissions['user']
$wgGroupPermissions['user']['read']                = true;
$wgGroupPermissions['user']['edit']                = true;
$wgGroupPermissions['user']['createpage']          = true;
$wgGroupPermissions['user']['comment']             = true;
$wgGroupPermissions['user']['upload']              = true;
$wgGroupPermissions['user']['createtalk']          = true;
$wgGroupPermissions['user']['editmyoptions']       = true;
$wgGroupPermissions['user']['editmyprivateinfo']   = true;
$wgGroupPermissions['user']['editmywatchlist']     = true;
$wgGroupPermissions['user']['viewedittab']         = true; # Extension:PageForms
$wgGroupPermissions['user']['viewmyprivateinfo']   = true;
$wgGroupPermissions['user']['viewmywatchlist']     = true;
$wgGroupPermissions['user']['writeapi']            = true;

$wgGroupPermissions['bureaucrat']['createaccount'] = true;
$wgGroupPermissions['sysop']['createaccount']      = true;

/* New Rights for Moderation */
$newrights = array();
$newrights = array_merge($newrights, array('skip-moderation', 'skip-move-moderation', 'moderation', 'moderation-checkuser'));

foreach ($newrights as $this_right) {
        $wgGroupPermissions['*'][$this_right] = false;
        $wgGroupPermissions['user'][$this_right] = false;
        $wgGroupPermissions['sysop'][$this_right] = true;
}

$wgGroupPermissions['sysop']['upload_by_url'] = true;

/* New Certification Rights */
$wgAvailableRights[] = 'certificationrequest';
$wgAvailableRights[] = 'certificationadmin';
$wgAvailableRights[] = 'certificationreview';

$wgGroupPermissions['*']['certificationrequest'] = false;
$wgGroupPermissions['*']['certificationadmin']   = false;
$wgGroupPermissions['*']['certificationreview']  = false;
$wgGroupPermissions['*']['skip-moderation']      = false;
$wgGroupPermissions['*']['skipcaptcha']          = false;

$wgGroupPermissions['user']['certificationrequest'] = true;
$wgGroupPermissions['user']['certificationadmin'] = false;
$wgGroupPermissions['user']['certificationreview'] = false;
$wgGroupPermissions['user']['skip-moderation'] = false;
$wgGroupPermissions['user']['skipcaptcha'] = false;
$wgGroupPermissions['certification_applicant']['certificationrequest'] = true;
$wgGroupPermissions['certification_applicant']['certificationadmin'] = false;
$wgGroupPermissions['certification_applicant']['certificationreview'] = false;
$wgGroupPermissions['certification_applicant']['skip-moderation'] = true;
$wgGroupPermissions['certification_applicant']['skipcaptcha'] = true;
$wgGroupPermissions['certification_reviewer']['certificationrequest'] = true;
$wgGroupPermissions['certification_reviewer']['certificationadmin'] = false;
$wgGroupPermissions['certification_reviewer']['certificationreview'] = true;
$wgGroupPermissions['certification_reviewer']['skip-moderation'] = true;
$wgGroupPermissions['certification_reviewer']['skipcaptcha'] = true;
$wgGroupPermissions['certification_body']['certificationrequest'] = true;
$wgGroupPermissions['certification_body']['certificationadmin'] = true;
$wgGroupPermissions['certification_body']['certificationreview'] = true;
$wgGroupPermissions['certification_body']['skip-moderation'] = true;
$wgGroupPermissions['certification_body']['skipcaptcha'] = true;
$wgGroupPermissions['sysop']['certificationrequest'] = true;
$wgGroupPermissions['sysop']['certificationadmin'] = true;
$wgGroupPermissions['sysop']['certificationreview'] = true;
$wgGroupPermissions['sysop']['skip-moderation'] = true;
$wgGroupPermissions['sysop']['skipcaptcha'] = true;

$wgGroupPermissions['sysop']['skip-move-moderation'] = true;
$wgGroupPermissions['sysop']['moderation'] = true;
$wgGroupPermissions['sysop']['moderation-checkuser'] = true;

$wgHtmlIncludeBlocks = array('certificationrequest', 'certificationadmin', 'certificationreview');


$wgAddGroups['certification_body'] = array('certification_reviewer', 'certification_applicant');


/* TEMPORARY DEBUG (AP 2023-07-06 17:09:37 issue of user login among shared partner wikis) */
/* 
$wgHooks['BeforePageDisplay'][] ='onBeforePageDisplay';
function onBeforePageDisplay( OutputPage &$out, Skin &$skin )
{    
    $out->addWikiTextAsContent(
       "<pre>TEMPORARY DEBUG\n" 
      . print_r($_SESSION, true) . "<hr>"
      . print_r($_COOKIE, true) . "<hr>"
      // . "No output of wgCookieDomain, wgCrossSiteAJAXdomains"
      // . "wgCookieDomain:\n"
      // . print_r($wgCookieDomain, true) . "<hr>"
      // . "wgCrossSiteAJAXdomains:\n"
      // . print_r($wgCrossSiteAJAXdomains, true)
      // . "<!-- closing pre seems not neccessary -->"
    );
    return true;
};
*/


// $wgAdvancedSearchHighlighting = true;

// at last define name spaces (new namespaces must be defined in extensions above)
$wgNamespacesToBeSearchedDefault [NS_TALK]           = true;
$wgNamespacesToBeSearchedDefault [NS_HELP]           = true;
$wgNamespacesToBeSearchedDefault [NS_COMMENTSTREAMS] = true;  /* @require extension CommentStreams */

$wgContentNamespaces = [NS_MAIN, NS_HELP, NS_COMMENTSTREAMS];
