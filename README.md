# Content migration bundle

The content-migration-bundle can be used to import/export a complete site structure including content to/from a Contao
installation including content to/from a Contao installation. Developed to import the demo content of our
[Contao Themes](https://contao-themes.net) into an existing Contao installation.

The import is model based and does not consider if all extensions and their fields are available in the importing instance.
Fields that are not available are simply ignored during the import! There will also be no error message.
All pages and elements will be assigned a new ID.

In the news section of your Contao installation, you can also import news from a TYPO3 tt_news table, including a link
to the image files.

Have fun with it.

## Functions

- Import and export of a complete Contao page structure including content
- Import of news from a TYPO3 database (tt_news)

## Install

Install via Contao Manager or Composer

## Credits
- Import and export Icons made by [Pixel perfect](https://www.flaticon.com/authors/pixel-perfect) from [www.flaticon.com](https://www.flaticon.com/)
- tt_news import inspired by [chrisdee](https://github.com/chrizdee/tt_news_2_contao)


---- german

Das content-migration-bundle kann für den Import/Export einer kompletten Seitenstruktur
einschließlich Inhalte in/aus eine/r Contao Installation verwendet werden. Entwickelt
wurde es, um die Demo Inhalte unserer [Contao Themes](https://contao-themes.net) in eine bestehende
Contao Installation zu importieren.

Der Import ist Model gestützt und berücksichtigt nicht, ob in der importierenden Instanz auch alle Erweiterungen
und deren Felder verfügbar sind. Nicht vorhandene Felder werden beim Import einfach ignoriert! Es wird auch keine
Fehlermeldung geben. Alle Seiten und Elemente werden mit einer neuen Kennung (ID) versehen.

Im Newsbereich deiner Contao Installation kannst du außerdem Nachrichten aus einer TYPO3 tt_news Tabelle, inklusive Verknüpfung
der Bilddateien, importieren.

Viel Spaß dabei.

## Funktionen

- Import und Export einer kompletten Contao Seitenstruktur inkl. Inhalte
- Import von News aus einer TYPO3 Datenbank (tt_news)

## Dokumentation

[Deutsche Handbuch](https://pdir.de/docs/de/contao/extensions/content-migration/)
