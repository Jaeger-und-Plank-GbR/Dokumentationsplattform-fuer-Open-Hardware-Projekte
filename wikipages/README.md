# Häufig gestellte Fragen (HGF) / Frequently Asekd Questions (FAQ)

## Wie kommt die Funktionalität der Projektverwaltung ins Wiki?

Das MediaWiki-System ist funktionell erweitert um 

- Formulare, Vorlagen und semantische Eigenschaten (Erweiterungen PageForms und SemanticMediaWiki)
- Systemseiten
- Extension:HtmlSpecialFunctions

Die Hauptfunktionalitäten liegen in den Vorlagen und Formularen, und Extension:HtmlSpecialFunctions enthält einige Funktionshelfer, z.B. bei CSV-Stücklisten o.a.. Derzeit können die nötigen MediaWiki-Seiten über `Special:Import` importiert werden:

- Sandbox-Category-Examples-20230912095126.xml 
- Sandbox-Forms-Templates-Properties-20230912095628.xml
- Sandbox-MediaWiki-System-Pages-20230912095415.xml

Wichtig nach dem Import ist, daß der Wartungsdienst (`runJobs.php`) richtig und regelmäßig läuft, damit die neuen Eigenschaften ordentlich im Wiki zur Funktion kommen.

ZUTUN: Theoretisch könnte man auch eine JSON-Pakt-Definition mit allen Seitenabhängigkeiten erstellen und dann mit der [Extension:PageExchange](https://www.mediawiki.org/wiki/Extension:Page_Exchange) in ein Wiki importieren.

## Wie ändere ich die Projekt-Kategorien?

Die Projekt-Kategorien sind verschachtelt aufgebaut, die definierte Oberkategorie im Englischen Wiki ist Projects. Die Verschachtelung kann beliebig tief aufgebaut werden, z.B.:

```
+---------------------------------+
0-Ebene 
↓   1-Unterebene
↓   ↓   2-Unterebene
↓   ↓   ↓
↓   ↓   ↓
+---------------------------------+
Projects
    Agriculture, forest‎
    Business, industry‎
    Computer, electronics‎
    Environmental technologies‎
    Health‎
        Drinking water treatment
        Recycling‎
        Waste water
    Mobility, Logistics‎
    Renewable energies‎
```

Beim Aufbau muß eine Unterkategorie auf die nächst höhere Kategorie verweisen, und die nächsthöhere Kategorie auf die nächsthöhere usw. – jenachdem, wieviele Stufen man haben will, der letzte Verweis *muß* auf die Hauptkategorie “category: Projects” verweisen, denn von ihr aus wird ein Kategorienbaum erzeugt, der dann auch im Erstellungsformular angezeigt eingebunden wird. Seitenbeispiel:

```
+-- Seite category:Projects ------+
|   (dies ist die Hauptkategorie) | ←.
|                                 |   \
|                                 |   ↑
|                                 |   ↑
+---------------------------------+   ↑
                                      ↑
+-- Seite category:Health‎ --------+   /
|                    .→→→→→→→→→→→ | →
|                   /             |
| [[category: Projects]]          | ←.
+---------------------------------+   \
                                      ↑
+-- Seite category:Waste water  --+   ↑
|                                 |   ↑
|                                 |   /
| [[category: Health]]            | →
+---------------------------------+
```

## Wie wird die programm-technische Dokumenation zusammengehalten?

Die `[[category: Project management]]` sollte alle nötigen technischen Seiten zusammenfassen, die für die Funktionalität wichtig sind, das sind vorrangig:

- Vorlagen (templates)
- Formulare (forms)
- Eigenschaften (Attribute, properties)

Neue Vorlagen sollten auch innerhalb `[[category: Project management]]` dokumentiert werden.
