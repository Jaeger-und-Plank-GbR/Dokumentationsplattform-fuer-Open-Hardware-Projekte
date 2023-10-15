---
title: MediaWiki REL1_38 von localhost zu sandbox.oh-dc.org installieren
author:
- Andreas Plank
description: Diese Dokumentation beschreibt die Neuerstellung einer englischen Server-MediaWiki-Fassung (REL1_38), und wie man ein funktionsfähiges Wiki erstellt, in dem man Projekte offengelegter Herstellung, sog. Open-Source-Hardware-Projekte, in Englisch dokumentieren kann.
tags: MediaWiki Server-Installation, REL1_38
lang: de
---

**Zusammenfassung:** Diese Dokumentation beschreibt die Neuerstellung einer englischen Server-MediaWiki-Fassung (REL1_38), und wie man ein funktionsfähiges Wiki erstellt, in dem man Projekte offengelegter Herstellung, sog. Open-Source-Hardware-Projekte, in Englisch dokumentieren kann.


## Zeitlicher Bedarf

Schätzungsweise dauert es 1 Arbeitstag mit sämtlich allen Einstellungen, Seitenimporten, Server-Einstellungen usw., da meistens noch unerwartete Dinge geschehen im Verlauf der Installation. Hat man schon ein Wiki eingerichtet und will eine Kopie machen, geht es oft schneller, etwa 2 bis 4 Stunden.

## Ursprüngliche Ausgangslage und Installationen

Ausgangslage, technischer Hintergrund: es wird allgemein empfohlen, Erneuerung immer in Einzelschritten durchzuführen, daher wurden aus einer alten Installation die Erneuerungen immer schrittweise auf die nächste beständige Veröffentlichung durcherneuert (z.B. REL1_35 → REL1_36 usw.), was meistens einwandfrei verlief; es gab eine Datenbankfassung, die ursprünglich die Grundlage war.

1. localhost MediaWiki 1.31 (oho-Archiv-Fassung) → MediaWiki REL1_35 (ging mit etwas Schwierigkeiten und Nachforschen der Fehlerprobleme)
2. localhost MediaWiki REL1_35 → MediaWiki REL1_36, ging tadellos
3. localhost MediaWiki REL1_36 → MediaWiki REL1_37, ging tadellos
4. localhost MediaWiki REL1_37 → MediaWiki REL1_38, ging tadellos
5. localhost MediaWiki REL1_38 → MediaWiki REL1_39, ging tadellos (ab REL1_38 aufwärts gab es unlösbare PHP-Probleme aufgrund der Fassungen 7.3/7.4:  7.3.19 (Server) hingegen 7.4.x (localhost))

Theoretischer Hintergrund: REL1_39 benötigt PHP 7.4.x, REL1_38 benötigt PHP 7.3.19+ und ist aber unter einer PHP 7.4.x-Umgebung schwieriger aufzubauen, und für 7.3.x zum Laufen zu bekommen, daher wurde das gesamte MediaWiki-Gefüge auf dem Server selbst aufgebaut, und nur die Datenbank-Fassung von localhost auf den Server überspielt.

## Allgemeines Vorgehen (Planung)

(ZUTUN Vorgehen überprüfen, ob stimmig)

1. Auslieferungsfassung von REL1_38 auf Server stellen

    - MediaWiki-Gefüge vorbereiten
    - Angleichen benötigter Strukturen: images, cache
    - `LocalSettings.php` vorbereiten
    - Erweiterungen
    
        1. Erweiterungen mit `composer.phar` erstellen lassen (mit unbearbeiteter neuer Werksfassung von MediaWiki REL1_38)
        2. offizielle Git Erweiterungen `erneuere_git_Erweiterungen.sh` (die nicht mit composer verwaltet werden)
        3. benutzerdefinierte Extension:HtmlSpecialFunctions (gesondert für Doku-Plattform)
    
    - hinzufügen `/skins/chameleon/layouts/custom.xml` (oder falls andere benutzerdefinierte Gestaltungsvorschriften verwendet, z.B. `iog_custom.xml` o.ä.)
    
2. Apache Konfiguration für Netzbereichsadresse (Domain) einstellen, bereitstellen

    - auch versteckte `.htaccess` bedenken
    - auch an Access-Control-Allow-Origin denken, falls mehrere Wikis miteinander kommunizieren sollen, und sandbox.oh-dc.org anschließend in dem Wiki hinzufügen, von wo aus Daten abrufbar sein dürfen/sollen.

3. MySQL Datenbank erstellen
4. MediaWiki vollumfänglich einstellen, bereitstellen
5. Erstinstallation durchführen – `maintenance`

    - Erstinstallation: `maintenance/install.php` erstellt Grundlegendes `LocalSettings.php`
    - `LocalSettings.php` anpassen an eigene Wünsche
    
6. Erneuerung – `maintenance`

    - Erneuerung: `maintenance/update.php` erstellt die schlußendlich gewünschte Ausstattung


7. Wiki-Seite das erste Mal aufrufen 

    - falls *Fatal exception of type MWException*
    
        - `sudo -u wwwuser php ./maintenance/rebuildLocalisationCache.php --help --conf LocalSettings.php`
        - `sudo -u wwwuser php ./maintenance/rebuildLocalisationCache.php --lang en,de --conf LocalSettings.php`

8. Funktionelle Wiki-Seiten einlesen

    - Wiki-Seiten für Formulare, Vorlagen, MediaWiki-System-Texte (JavaScript), CSS usw. einlesen, um die Formular-Funktionalitäten bereitzustellen

9. Nacharbeiten durchführen

    - Wartungsdienst einrichten (cron für `maintenance/runJobs.php`), der Eigenschaften im Wiki zum gegenwärtigen Stand erneuert



## Allgemeine Abhängigkeiten

Es gibt zwingende Abhängigkeiten und ausgliederbare (und welche zwsichendrin ;-) – zwingende Abhängigkeiten:

- Extension:PageForms
- Extension:SemanticMediaWiki
- Extension:Arrays
- Extension:Variables
- Extension:ParserFunctions
- Extension:HtmlSpecialFunctions
- Extension:HeaderTabs (Tab-Reiter im Formular)
- Extension:CategoryTree
- Extension:Bootstrap (eigentlich nicht zwingend, jedoch ist die Formgestaltung für Bootstrap ausgearbeitet)

  - Skin:Chameleon

Erwünschte Erweiterungen, Abhängigkeiten ausgliederbar z.T. mit Wiki-Code-neuschreiben verbunden:

- Extension:InputBox (Suchen-Formular, erstellen-Formular von Seiten, Kategorie-Seiten mit Textvorlage usw.)
- Extension:SemanticResultFormats (`#ask`-Abfragen)
- Extension:EmbedVideo
- Extension:MultimediaViewer
- Extension:PageExchange
        
## Auslieferungsfassung von REL1_38 auf Server stellen


```bash
# MW_VERZEICHNIS="/run/media/andreas/LINUX-ext4-930GB/andreas/Webseite/oho-legacy-main/oh-dc.org_REL1_38_PHP73"
MW_VERZEICHNIS="/run/media/andreas/LINUX-ext4-930GB/andreas/Webseite/oho-legacy-main/mediawiki-1.38.6"
cd "${MW_VERZEICHNIS}" && cd ..
tar --exclude="./mw-config" --create --gzip --file=sandbox.oh-dc.org_localhost_REL1_38.tar.gz --directory="${MW_VERZEICHNIS}" .
```

- auf Server verschieben, entpacken und weiter anpassen


Rechte setzen

```bash
#  als root
vhost_wiki_path=/pfad/zu/www/htdocs/sandbox.oh-dc.org

cd "${vhost_wiki_path}"

tar -xvf sandbox.oh-dc.org_localhost_REL1_38.tar.gz # entpacken + auspacken berichten (-v verbose)
rm --interactive sandbox.oh-dc.org_localhost_REL1_38.tar.gz # aufräumen


 # chown --recursive root:root "${vhost_wiki_path}"
 chown --recursive www:wwwuser "${vhost_wiki_path}"
 chown --recursive www:wwwuser "${vhost_wiki_path}/images/"
 chown --recursive www:wwwuser "${vhost_wiki_path}/cache/"
```

### Erweiterungen einstellen (composer-verwaltet)

Beschaffe `composer.phar`, siehe Anleitung <https://getcomposer.org/download/>, `sha384`-Vergleich **ist abhängig von der jeweiligen Abassung**, z.B. “Latest: v2.5.8”:

```bash
vhost_wiki_path=/pfad/zu/www/htdocs/sandbox.oh-dc.org
cd "${vhost_wiki_path}"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"


php composer-setup.php
# Composer (version 2.5.8) successfully installed to …
# Use it: php composer.phar
php -r "unlink('composer-setup.php');"
```

Kopiere und verwende `composer.local.json` von oh-dc.org in einer **frischen** MediaWiki-Auslieferungsfassung. Inhalt hiesiger Einstellungen:

```bash
cat composer.local.json
```

```json
{
  "require": {
    "mediawiki/chameleon-skin": "3.4.3",
    "mediawiki/semantic-media-wiki": "~4.0",
    "mediawiki/semantic-result-formats": "4.*",
    "mediawiki/page-forms": "~5.6",
    "mediawiki/bootstrap": "4.*"
  },
  "extra": {
    "merge-plugin": {
      "include": [
        "extensions/*/composer.json",
        "skins/*/composer.json"
      ]
    }
  },
  "config": {
    "allow-plugins": {
      "composer/installers": true,
      "wikimedia/composer-merge-plugin": true
    }
  }
}
```

Erneuerung der Erweiterungen durchführen

```bash
sudo -u wwwuser php composer.phar validate # ./composer.json is valid, but with a few warnings
sudo -u wwwuser php composer.phar update  --dry-run --no-dev # läuft durch
sudo -u wwwuser php composer.phar update  --no-dev
```

Falls es nicht weitergeht, Lösungsversuch: mit neuester unbearbeiteter Werksfassung REL1_38 anfangen und dann Erweiterungen schrittweise hinzufügen (1. composer-Erweiterungen, 2. erneuere_git_Erweiterungen.sh, 3. Rest)

    sudo -u wwwuser php composer.phar update  --dry-run --no-dev

    Your requirements could not be resolved to an installable set of packages.

      Problem 1
        - mediawiki/semantic-media-wiki[4.0.0, 4.0.1, 4.0.2] cannot be installed as that would require removing mediawiki/core[1.0.0+no-version-set]. They all replace mediawiki/semantic-mediawiki and thus cannot coexist.
        - mediawiki/semantic-media-wiki[4.1.0, ..., 4.1.2] require php >=7.4 -> your php version (7.3.27) does not satisfy that requirement.
        - mediawiki/core is present at version 1.0.0+no-version-set and cannot be modified by Composer
        - Root composer.json requires mediawiki/semantic-media-wiki ~4.0 -> satisfiable by mediawiki/semantic-media-wiki[4.0.0, ..., 4.1.2].

    mediawiki/semantic-mediawiki ist wahrscheinlich das alte extension/SemantikMediaWiki


### Erweiterungen einstellen (Git-verwaltet)

```bash
cd /pfad/zu/www/htdocs/sandbox.oh-dc.org
# Skript (erneuere_git_Erweiterungen.sh) enstprechend angleichen für benötigte Erweiterungen, und dann ausführen
erneuere_git_Erweiterungen_sandbox.sh
# ?notfalls Rechte an Erweiterungen angleichen
 chown --recursive www:wwwuser "${vhost_wiki_path}"
```


## Apache Konfiguration für Domain einstellen, bereitstellen


Meistens findet man die Konfiguration unter `/etc/apache2/` dabei ist es nützlich Zwei Verzeichnisse zu pflegen,  Einstellungen:

- eines mit verfügbaren Konfigurationen `/etc/apache2/sites-available/` und
- eines mit angeschalteten Konfigurationen `/etc/apache2/sites-enabled/`

… denn mann kann so leicht Verknüpfungen erstellen oder nach Bedarf wieder die Verknüpfung Löschen, aus dem Ordner verfügbarer Seiten in den Ordner angeschalteter Seiten.


Man bedenke, daß es eine versteckte `.htaccess` gibt (für PHP `$wgArticlePath` usw.) im Wikiordner selbst:

    ```
    RewriteEngine On
    RewriteRule ^wiki/(.*)$ /index.php/$1 [L]
    ```

Beispielkonfiguration:

```apacheconf
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    ServerName sandbox.oh-dc.org
    ServerAlias sandbox.oh-dc.org

    DocumentRoot /pfad/zu/www/htdocs/sandbox.oh-dc.org/
    <Directory /pfad/zu/www/htdocs/sandbox.oh-dc.org/>
        Options +Indexes +FollowSymLinks -MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sandbox.oh-dc.org-error.log

    # Possible values include: debug, info, notice, warn, error, crit,
    # alert, emerg.
    LogLevel warn

    CustomLog ${APACHE_LOG_DIR}/sandbox.oh-dc.org-access.log combined
</VirtualHost>
<VirtualHost *:443>
      ServerAdmin webmaster@localhost
      ServerName sandbox.oh-dc.org

      DocumentRoot /pfad/zu/www/htdocs/sandbox.oh-dc.org/
      <Directory /pfad/zu/www/htdocs/sandbox.oh-dc.org/>
        Options +Indexes +FollowSymLinks -MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
      </Directory>

      <IfModule mod_headers.c>
        # Allow data from sandbox.oh-dc.org to be read by 3dviewer.net 
        SetEnvIf Origin "^https://(3dviewer\.net)[:0-9]*" ORIGIN=$0
        Header set Access-Control-Allow-Origin %{ORIGIN}e env=ORIGIN
        Header set Access-Control-Allow-Credentials "true" env=ORIGIN
        Header merge Vary Origin

      </IfModule>

      ErrorLog ${APACHE_LOG_DIR}/sandbox.oh-dc-error.log

      # Possible values include: debug, info, notice, warn, error, crit,
      # alert, emerg.
      LogLevel warn

      CustomLog ${APACHE_LOG_DIR}/sandbox.oh-cd.org-access.log combined

      SSLEngine on
      SSLCertificateFile /etc/apache2/ssl/sandbox.oh-dc.org.cer
      SSLCertificateKeyFile /etc/apache2/ssl/sandbox.oh-dc.org.key
      SSLCertificateChainFile /etc/apache2/ssl/sandbox.oh-dc.org-ca.cer
</VirtualHost>
```

## MySQL Datenbank erstellen

Datenbank erstellen siehe das Handbuch <https://www.mediawiki.org/wiki/Manual:Installing_MediaWiki/de#MariaDB/MySQL>


## MediaWiki vollumfänglich einstellen, bereitstellen

(0) Voraussetzungen:

- MySQL Datenbank fertig vorbereitet
- MediaWiki: alle Erweiterungen fertig vorbereitet
- MediaWiki: alle Schreib-Lese-Rechte richtig gesetzt
- MediaWiki: Gestaltung (layouts) fertig vorbereitet für Skin Chameleon

(1) Erstinstallation

- erstellt betriebsbereite `LocalSettings.php` in Grundausstattung (vom Installations-Skript)

(2) Folgearbeiten, Nachgang:

- MediaWiki: `LocalSettings.php` fertig vorbereiten, Zusätzliche Einstellungen, Erweiterungen einstellen usw.
- LocalSettings.php: für Semantik MediaWiki `$smwgConfigFileDir` vielleicht auf `./cache` umstellen



(1) MediaWiki mit allererstem Installationsdurchlauf durchführen:

```bash
#  als root
vhost_wiki_path=/pfad/zu/www/htdocs/sandbox.oh-dc.org
cd "${vhost_wiki_path}"
# Hilfe anzeigen Installation:
sudo -u wwwuser php ./maintenance/install.php --help --conf LocalSettings.php
  
sudo -u wwwuser php maintenance/install.php \
  --dbname=sandbox_ohdc_wiki_en  \
  --dbserver="localhost"  \
  --installdbuser=root  \
  --installdbpass=mysqlrootpassword  \
  --dbuser=wiki_user_for_mysql  \
  --dbpass=vutha2aePhaedeim6obu  \
  --server="http://sandbox.oh-dc.org/"  \
  --scriptpath=""  \
  --lang=en  \
  --pass=wikiadminpassword "Open Hardware in Development Cooperation (Sandbox)" "Wikiadmin"
  
    # PHP 7.3.27-1~deb10u1 is installed.
    # Warning: Could not find APCu or WinCache. Object caching is not enabled.
    # Found GD graphics library built-in. Image thumbnailing will be enabled if you enable uploads.
    # Found the Git version control software: /usr/bin/git.
    # Using server name "http://localhost".
    # Using server URL "http://sandbox.oh-dc.org/".
    # Warning: Your default directory for uploads (/pfad/zu/www/htdocs/sandbox.oh-dc.org/images/) is not checked for vulnerability to arbitrary script execution during the CLI install.
    # Using the PHP intl extension for Unicode normalization.
    # The environment has been checked. You can install MediaWiki.
    # Setting up database
    # done
    # Creating tables, step one
    # done
    # Creating database user
    # done
    # Creating tables, step two
    # done
    # Populating default interwiki table
    # done
    # Initializing statistics
    # done
    # Generating secret keys
    # done
    # Prevent running unneeded updates
    # done
    # Restoring mediawiki services
    # done
    # Creating administrator user account
    # done
    # Creating main page with default content
    # done
    # Database was successfully set up
    # MediaWiki has been successfully installed. You can now visit <http://sandbox.oh-dc.org/> to view your wiki. If you have questions, check out our frequently asked questions list: <https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:FAQ> or use one of the support forums linked on that page.  
```

(2) Die neuerstellte `LocalSettings.php` abändern nach eigenen Konfigurationswünschen und Extensionen; danach letzten Aktualisierungsschritt: noch einmal mit neuester Konfiguration alles erneuern:

```bash
# Hilfe anzeigen bei Erneuerung
sudo -u wwwuser php ./maintenance/update.php --help --conf LocalSettings.php
# Erneuerung tatsächlich durchführen
sudo -u wwwuser php ./maintenance/update.php --quick --conf LocalSettings.php
# Erfolg: Done in 5.4 s.
```

Man kann jetzt das Wiki versuchen normal aufzurufen im Netzprogramm und es müßte theoretisch grundsätzlich laufen, aber wahrscheinlich wird eine Fehlermeldung kommen (“*Fatal exception of type MWException*”): Nachdem von Einstellungsseite alles stimmig ist und im gesamten Wiki-Datenbank-System alles durcherneuert wurde, ist es wahrscheinlich unumgänglich und notwendig, den Cache-Mechanismus neu aufzubauen, die Lösung war erreicht durch:

```bash
# nur Hilfe und Parameter anzeigen
sudo -u wwwuser php ./maintenance/rebuildLocalisationCache.php --help --conf LocalSettings.php
# tatsächlich ausführen: für die nötigen Sprachen den Cache neu aufbauen
# … oder die Sprachen --lang en,de wählen, für die man den Cache erneuern möchte
sudo -u wwwuser php ./maintenance/rebuildLocalisationCache.php --lang en --conf LocalSettings.php
```

Jetzt sollte das neue Wiki technisch laufen, was noch fehlt sind 

- die MediaWiki-Seiten, -Formulare, -Vorlagen zu importieren für die Funktionalität
- den Wartungsdienst für `runJobs.php` einschalten

## Funktionelle Wiki-Seiten einlesen

Zur Erstellung der Funktionalitäten verwenden wir MediaWiki-XML Exportdateien, die wir importieren, siehe im Ordner [./wikipages/](./wikipages/),  die Liste der wichtigsten Seiten ist in Anliegen 1 aufgelistet ([issues/1](../../issues/1#issuecomment-1700800545)). 

Über Special:Import kann man die Seiten einfach einlesen lassen (man muß ein “interwiki prefix” angeben, z.B. woher die Seiten ursprünglich sind):

- Sandbox-MediaWiki-System-Pages-20230912095415.xml
- Sandbox-Forms-Templates-Properties-20230912095628.xml
- Sandbox-Category-Examples-20230912095126.xml

Damit alle Formulare einwandfrei laufen und auch Eigenschaften (properties) richtig arbeiten, sind noch Nacharbeiten nötig: Das Wichtigste ist den Wartungsdienst häufig laufen zu lassen, damit semantische Änderungen von Eigenschaften in Vorlagen, auf Projektseiten, oder auf Eigenschaftsseiten schneller richtig dargestellt und eingepflegt werden. Dies ist vor allem für die Vorlagenentwicklung wichtig, oder wenn neue Projekt-Kategorien hinzugefügt werden, damit neue Projekt-Kategorien auch im Formular erneut richtig eingelesen werden usw..

## Nacharbeiten

### Sandbox-Grund-Fassung absichern 

Falls man eine Sicherung der Grund-Installation wünscht, kann man zu diesem Zeitpunkt sich eine Sicherung erstellen lassen, z.B.:

```bash
MW_VERZEICHNIS="/pfad/zu/www/htdocs/sandbox.oh-dc.org"
cd "${MW_VERZEICHNIS}" && cd ..

tar --exclude="./mw-config" --create --gzip --file=sandbox.oh-dc.org_REL1_38_PHP73.tar.gz --directory="${MW_VERZEICHNIS}" .
# ls -l /pfad/zu/www/htdocs/sandbox.oh-dc.org/sandbox.oh-dc.org*tar.gz
```

Oder um eine Sicherung zu einem späteren Zeitpunkt, einschließlich der Datenbank-Sicherung durchführen, lese man <https://www.mediawiki.org/wiki/Manual:Backing_up_a_wiki>.

### Cron einstellen (`runJobs.php`)

Allgemeine Dokumentation:

- https://www.mediawiki.org/wiki/Manual:Job_queue
- https://www.mediawiki.org/wiki/Manual:RunJobs.php
- https://www.mediawiki.org/wiki/Manual:Cache
- https://www.mediawiki.org/wiki/Category:Performance_tuning

Siehe Hilfe zum Erstellen https://www.generateit.net/cron-job/
```bash
# alle 5 Minuten aber bei Stunde:01, Stunde:06 usw.
#   echo $(seq ersteZahl SchrittweiteZahl letzteZahl)
#   echo $(seq 1 5 59) | sed --regexp-extended "s@ +@,@g"
#   echo $(seq 3 5 60) | sed --regexp-extended "s@ +@,@g"
# vi /etc/crontab 
1,6,11,16,21,26,31,36,41,46,51,56 * * * * root /usr/bin/nice /usr/bin/php /pfad/zu/www/htdocs/sandbox.oh-dc.org/maintenance/runJobs.php   --maxtime=3600 > /var/log/sandbox.oh-dc.org_runJobs.log 2>&1 > /dev/null 2>&1

# `systemctl restart cron.service` erscheint unnötig falls RELOAD selbsttätig durchgeführt wird. 
# Abfragen des Status beispielsweise, zeigt, ob das cron-Kommando im Zeitplan eingetragen ist oder nicht
systemctl status cron.service
```


### Gestaltung für Skin Chameleon

Ausgangslage: das Wiki und auch die Vorlagen und Formulare wurde für die Benutzeroberfläche (skin) Chameleon entworfen und getestet. Theoretisch können die Formulare und Vorlagen auch mit anderen Benutzeroberflächen arbeiten, jedoch benötigt dies nachbesserungen oder eine neue CSS Gestaltung, z.B. der Ergebnis-Vorschau-Kästen der Projekte usw..

Allgemeine Hintergrundinformationen zur Fassung Chameleon `3.4.3`:

- Einstiegsseite <https://github.com/ProfessionalWiki/chameleon/blob/3.4.3/docs/index.md>
- gestaltbare Komponenten <https://github.com/ProfessionalWiki/chameleon/blob/3.4.3/docs/components.md>
- CSS-Klassen entstammen Bootstrap in Fassung 4.0 (nicht die Extension, sondern bootstrap selbst), siehe 

    - https://getbootstrap.com/docs/4.0/getting-started/introduction/
    - CSS notation https://getbootstrap.com/docs/4.0/utilities/spacing/#notation
    - CSS Klassen für Grid https://getbootstrap.com/docs/4.0/layout/grid/


#### Skin:Chameleon 3.4.3 Fehlerbehebung (Anliegen/Issue #295 ~ `undefined method User::isLoggedIn()`)


Beim bestimmen, daß `type="PersonalTools"` innerhalb `type="NavbarHorizontal"` sein soll als Gestaltungsvorschrift in `/skins/chameleon/layouts/iog_custom.xml` (was aber funktionieren sollte):

```xml
    <component type="NavbarHorizontal" >
      <component type="PersonalTools"  />
    </component>
```

… kommt `Original exception: [47a6b657827cf365791f4455] ... Error: Call to undefined method User::isLoggedIn()`:

- siehe “User::isLoggedIn was deprecated in MediaWiki 1.36” (<https://github.com/ProfessionalWiki/chameleon/issues/295>)
- Lösung: https://github.com/ProfessionalWiki/chameleon/pull/297 (oder Skin:Chameleon 4.0, was aber PHP 7.4+ benötigt)

    - https://github.com/ProfessionalWiki/chameleon/pull/297/files

Fehlerbehebung händisch vornehmen:

```bash
cd skins/chameleon

cp src/Components/NavbarHorizontal/PersonalTools.php \
  src/Components/NavbarHorizontal/PersonalTools_original.php
sed --in-place "s@user->isLoggedIn@user->isRegistered@g"  \
  src/Components/NavbarHorizontal/PersonalTools.php

cp tests/phpunit/Unit/Components/NavbarHorizontal/PersonalToolsTest.php \
  tests/phpunit/Unit/Components/NavbarHorizontal/PersonalToolsTest_original.php
sed --in-place "s@'UserIsLoggedIn'@'UserIsRegistered'@g"  \
  tests/phpunit/Unit/Components/NavbarHorizontal/PersonalToolsTest.php

cp tests/phpunit/Util/MockupFactory.php \
  tests/phpunit/Util/MockupFactory_original.php
  
sed --in-place "s@'isLoggedIn'@'isRegistered'@g; s@'UserIsLoggedIn'@'UserIsRegistered'@g" \
  tests/phpunit/Util/MockupFactory_original.php
```

### Bildervorschau wird nicht erzeugt (Access-Control-Allow-Origin)

Falls man fremde Bild-Daten austauschen möchte, muß man dafür sorgen, daß dies auch erlaubt wird. Hier ist das Beispiel zum Freischalten von Bilddaten, die von 3dviewer.net verarbeitet werden und im eigenen Wiki angezeigt werden dürfen können sollen ;-)

(1) a: Auf Server-Wiki-Ebene in der Server-Konfiguration von Apache, den Daten-Ursprung (=origin) 3dviewer.net gestatten:

```apacheconf
<IfModule mod_headers.c>
  # define external resources that are allowed to read resources from sandbox.oh-dc.org
  SetEnvIf Origin "https://(3dviewer\.net)$" ORIGIN=$0
  Header set Access-Control-Allow-Origin %{ORIGIN}e env=ORIGIN
  Header set Access-Control-Allow-Credentials "true" env=ORIGIN
  Header merge Vary Origin
</IfModule>
```

(1) b: Auf Ebene des Wikis in den <code>LocalSettings.php</code> selbst:

```php
$wgCrossSiteAJAXdomains = [
    '3dviewer.net'
];
```

(2) Die „fremde“ Seite: Auf dem *Fremdserver* 3dviewer.net muß wahrscheinlich folgendes frei eingestellt sein:

```apacheconf
<IfModule mod_headers.c>
  Header set Access-Control-Allow-Origin "*"
</IfModule>
```

… oder will man auf dem fremden (Bild/Dateien-)Daten-Server nur bestimmte Netzadressen (URLs) gestatten, muß man den genau gestatteten Ursprung (=origin) angeben, der erlaubt sein soll, mit dem Daten vermittelt werden dürfen – beispielsweise:

```apacheconf
<IfModule mod_headers.c>
  # define external origins that are allowed to read from 3dviewer.net as a resource
  SetEnvIf Origin "https://(sandbox\.oh-dc\.org)$" ORIGIN=$0
  Header set Access-Control-Allow-Origin %{ORIGIN}e env=ORIGIN
  Header set Access-Control-Allow-Credentials "true" env=ORIGIN
  Header merge Vary Origin
</IfModule>
```

Siehe auch

- Dokumentation allgemein: https://www.freecodecamp.org/news/access-control-allow-origin-header-explained/
- allgemeines einschalten https://enable-cors.org/server_apache.html (Obacht! Man sollte besser nur bestimmte domains konfigurieren, anstatt für alle domains zu öffnen)
- Entscheidungs-Fluß-Diagramm für CORS ([cors_server_flowchart_(html5rocks.com).png](./Bilder/cors_server_flowchart_(html5rocks.com).png), https://www.html5rocks.com/static/images/cors_server_flowchart.png)

Testen der Server-Antwort einer Daten-oder-Bild-Abfrage aus eigener oder fremder Quelle vermittels `curl`

```bash
curl --verbose --silent --output /dev/null \
  https://eigenes-wiki.org/wiki/Seitenname_oder_Bild-Pfad-der-zu-prüfenden-Bilddatei.jpg
```


### Zugriffrechte beschränken

Hilfreiche Seiten:

- https://www.mediawiki.org/wiki/Manual:Preventing_access

Zum Beispiel in `LocalSettings.php` die Eigenschaft `$wgGroupPermissions['*']['read']=false;` Leserechte für unangemeldete ausschalten

### Benennung der Ankuntftsseite 

Siehe `MediaWiki:Mainpage` (derzeit: home)

## Allgemeines

### Nützliche Befehle für’s Netz

```bash
wget --server-response 'http://…'
wget -S 'http://…' # Kurzfassung
```

### Nutzererstellung in der Befehlszeile

Üblicherweise kann man vermittels Special:CreateAccount ein neues Benutzerkonto erstellen und über Special:UserRights die Benutzerrechte verändern, hier wird beschrieben, wie man gleiches in der Befehlszeile erledigen kann.

Allgemein gibt es hierfür `maintenance/createAndPromote.php "Benutzer Name" "Testpasswort"`&nbsp;– in diesem angewendeten Grundbefehl wird ein Normalnutzer erstellt (`user`). Wählt man Ermöglichungen hinzu (`--bureaucrat` oder `--sysop`), kann man ihn zum Bürokraten oder/und hochstufen, Einzelheiten siehe `maintenance/createAndPromote.php --help`

Es ist sinnvoll auch gleich die e-Brief-Adresse einzustellen, da meistens Seiten nur bearbeitet werden können, wenn der e-Brief bestätigt ist, und ohne irgendeine e-Brief-Adresse kann es sein, daß man sich überhaupt gar nicht erfolgreich anmelden kann.

```bash
cd /pfad/zu/www/htdocs/sandbox.oh-dc.org
sudo -u wwwuser php maintenance/createAndPromote.php "Benutzer Name" "Testpasswort" # Normalnutzer erstellen
```

Nutzern ermöglichen System-Verwalter und Bürokrat zu sein:
```bash
cd /pfad/zu/www/htdocs/sandbox.oh-dc.org
# Create a new user account and/or grant it additional rights
sudo -u wwwuser php maintenance/createAndPromote.php --help

# Bürokraten, Systemverwalter erstellen
sudo -u wwwuser php ./maintenance/createAndPromote.php --bureaucrat --sysop  "NutzernameFürSystemverwalter" "geheimesPasswort"
sudo -u wwwuser php ./maintenance/resetUserEmail.php --no-reset-password  "NutzernameFürSystemverwalter" "herr.mustermann@irgendwo-im-netz.de"
```

Dann in Preferences → Change password ist zu empfehlen 😉

Hilfreiche Wiki-Seiten zur Benutzerverwaltung:

- Special:ListUsers
- Special:ListGroupRights
- Special:CreateAccount
- Special:UserRights

