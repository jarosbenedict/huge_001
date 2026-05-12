# HUGE Framework Aufgabe #001

### Aus welchen Bausteinen besteht das Framework?
Das Framework besteht aus public, config, controller, model, view, core, vendor und der Datenbank, die Datenbank huge die Tabellen users und notes enthält.

### Wozu dient der public Ordner?
Der public Ordner dient als öffentlicher Einstiegspunkt der App und enthält die index.php sowie CSS-, JavaScript- und Bilddateien.

### Beschreibe folgende Bausteine: Config, Model, Controller, Core, View
Der Config Bereich enthält Konfigurationsdateien wie Datenbankeinstellungen, URLs und allgemeine Framework-Einstellungen.

Das Model verarbeitet die Datenlogik und kommuniziert mit der Datenbank über SQL-Abfragen.

Der Controller verarbeitet Benutzeranfragen, ruft Models auf und entscheidet, welche View angezeigt wird.

Der Core enthält die Kernlogik des Frameworks wie Routing, Basisklassen, Sessions und Framework-Funktionen.

Die View enthält die Darstellung der Anwendung und erzeugt das HTML für den Browser.

### Beschreibe den vollständigen Routing Prozess des Frameworks anhand eines Beispiels (zB: Index Seite)
Beim Routing wird zuerst die public/index.php geladen, dann analysiert der Router die URL, lädt den passenden Controller, dieser verwendet gegebenenfalls ein Model und gibt anschließend eine View an den Browser zurück.

### Wie sieht der Konstruktor in PHP Klassen aus?
__construct(), wird beim Erzeugen eines Objekts automatisch ausgeführt.

### Wozu dient die „Variable“ $this?
Gleich wie in Java

### Welche Vorteile hat die Verwendung von OOP in PHP?
Gleich wie in Java.

### Welche Datenkapselungsmethoden gibt es in PHP?
public, protectd, private

### Wie sehen abstrakte Klassen in PHP aus?
abstract, sind Vorlagen für andere Klassen.

# Login-Test
![](aufgaben_imgs/demo_12345678.png)