# Zweig `ais-chat-hamburg`: Erweiterungen für AIS.chat (Hamburg)

Lokal erprobt in Moodle 5.2.4 mit AIS.chat (frühere telli-API, OpenAI-kompatibel) über den Telli-Anschluss des AI Managers.
Die drei Zweige gehören zusammen und bauen auf diesen Ständen von github.com/bycs-lp auf:

| Plugin | Fork / Zweig | Basis |
|---|---|---|
| local_ai_manager | https://github.com/alku008/moodle-local_ai_manager/tree/ais-chat-hamburg | 410612a |
| block_ai_chat | https://github.com/alku008/moodle-block_ai_chat/tree/ais-chat-hamburg | b018f40 |
| block_ai_control | https://github.com/alku008/moodle-block_ai_control/tree/ais-chat-hamburg | 526c26c |

`tiny_ai` (und optional `mod_aichat`) bleiben unverändert und werden aus github.com/bycs-lp installiert.

## Neu

1. **Wissensdateien für Personas** (block_ai_chat): bis zu 5 Dateien je Persona (txt, md, ipynb, py, html, csv, docx, pdf).
   Moodle zerlegt sie in Abschnitte und berechnet Embeddings über `POST /v1/embeddings` der AIS.chat-API (Modell `BAAI/bge-m3`).
   Bei jeder Chat-Frage gehen nur die 4 passendsten Abschnitte mit der Persona-Anweisung an die KI (Kosinus-Ähnlichkeit + Wortvergleich +
   Bonus für genannte Aufgabennummern; ohne Embedding-Modell nur Wortvergleich). PDF über den mitgelieferten PHP-Leser
   smalot/pdfparser 2.13.1 (LGPL-3.0, `thirdparty/pdfparser`, kein Programm auf dem Server nötig). Neue Tabelle `block_ai_chat_wissen`.
   **Zwei Orte, klar beschriftet:** an der Persona („🌐 gilt in allen Kursen mit dieser Persona“) und im KI-Kontrollzentrum
   (Knopf „📚 Dateien hochladen und verwalten“, „📍 gilt nur in diesem Kurs, für jede Persona“). Bei einer Frage gehen die besten
   Abschnitte aus beiden mit.
2. **📎 Datei im Chat anhängen** (block_ai_chat), auch für Lernende: Text-, Code- und Notebook-Dateien werden im Browser gelesen und mit
   der Frage geschickt; im Verlauf steht nur „📎 Dateiname“. Grenzen prüft auch der Server.
3. **Einstellungen der Administration** (Website-Administration → Plugins → Blöcke → KI-Chat): Wissensdateien pro Kurs und pro Persona, MB je Datei,
   Embedding-Modell (mit Statusanzeige), Dateien im Chat an/aus, Anhänge pro Person und Tag, Zeichen je Anhang.
4. **Kontrollzentrum** (block_ai_control): Ablaufdatum-Fehler (01.01.1970) behoben; Anfragen-Limit pro Kurs für Lernende (bis zur Vorgabe
   der Administration); KI-Modell pro Kurs; Einstellung, welche Verbindungen Lehrkräfte wählen dürfen.
5. **✦-Knopf und Chatfenster** (block_ai_chat): Knopf über der rechten Seitenleiste (z-index 1045); im Fenster Persona-Name als Titel,
   Kurzbeschreibung darunter, Menü mit ⋮, KI-Hinweise klein unter der Eingabe und einmal ausführlich beim ersten Öffnen.
6. **Lernhilfen (17.11.2026):** Startvorschläge und gestufte Hilfe im Chat, Quellenzeile „📚 Aus den Kursunterlagen“, Seitenkontext,
   Wissensdateien aus Kursinhalten erzeugen, Wissensdateien reisen mit Kurssicherungen; Personas anlegen nur mit Bearbeitungsrecht.
7. **Kontrollzentrum (17.11.2026):** Persona für den Kurs, Themen-Auswertung der letzten 7 Tage, Zeitplan für Lernende, aus während Tests.
8. **AI Manager** (local_ai_manager): neuer Haken `connector_instance_override` (Modell pro Kurs); aufgebrauchtes Kurs-Limit (Code 429)
   sperrt den Chat mit Hinweis, statt ihn auszublenden.

Ausführliche Begründungen und Code-Auszüge: Dokument „KI-Plugins der ByCS: Erfahrungen und Änderungsvorschläge“ (Abschnitte 1–10).

## Installation (Testsystem)

1. Die drei Zweige als ZIP herunterladen (Code → Download ZIP) oder klonen, Ordner umbenennen und ablegen:
   `local/ai_manager`, `blocks/ai_chat`, `blocks/ai_control`; dazu `tiny_ai` aus bycs-lp in `lib/editor/tiny/plugins/ai`.
2. Website-Administration → Mitteilungen → Upgrade.
3. Telli-Anschluss: Basis-URL der AIS.chat-API (bis vor `/v1`), globaler API-Schlüssel, Modelle. Für die Wissensdateien muss der Schlüssel
   zusätzlich ein Embedding-Modell freigegeben haben (Name in der KI-Chat-Einstellung „Embedding-Modell“, Standard `BAAI/bge-m3`).
4. KI-Tools-Administration: Mandant einschalten, Verbindungen anlegen, Zwecke für beide Rollen zuordnen.

## Vor dem Einsatz: bitte `grunt amd` ausführen

Lokal stand kein Node.js zur Verfügung. Die Build-Dateien der neuen bzw. geänderten JavaScript-Module sind deshalb **von Hand erstellt**:
block_ai_chat `amd/build/{anhang,hinweis,vorschlaege}.min.js` (unveränderte Kopien von `amd/src`), `amd/build/{title,boxes}.min.js`
(von Hand übertragen, Sourcemaps veraltet); block_ai_control `amd/build/ai_control_config.min.js` (Korrektur Ablaufdatum).
Sie laufen, aber `grunt amd` (bzw. die CI) sollte sie neu erzeugen.

## Sicherheit und Datenschutz (geprüft 17.11.2026)

**Rechte**
- Kurs-Wissensdateien und „Aus den Kursinhalten erzeugen“ (`blocks/ai_chat/kurswissen.php`): `require_login`, `moodle/course:update`, sesskey.
- Einstellungen im Kontrollzentrum (`blocks/ai_control/kurslimit.php`): `require_login`, `require_sesskey`, `block/ai_control:control` im
  Blockkontext; Persona für den Kurs zusätzlich `block/ai_chat:edit`. Personas anlegen, kopieren, bearbeiten, löschen nur mit `block/ai_chat:edit`
  (vorher konnte jede Person mit Chat-Zugang Personas anlegen).
- Wissensdateien sind **nicht** über pluginfile.php abrufbar (kein Datei-Callback): Lernende sehen nur, was die KI daraus zitiert.
- Ausgaben werden escaped (`s()`, FORMAT_PLAIN, im JavaScript nur `textContent`), SQL nur mit Platzhaltern.

**Was an externe Dienste geht**
- Chat-Fragen gehen wie bisher über den AI Manager (Protokoll, Limits, Mandant).
- **Neu:** Für die Suche in den Wissensdateien wird die Frage an `/v1/embeddings` des Telli-Anschlusses geschickt (globale Basis-URL und
  globaler Schlüssel von `aitool_telli`). Das geschieht erst, **nachdem der AI Manager die Chat-Anfrage zulässt** (Mandant, Sperren,
  Kontrollzentrum, Limits). Embedding-Aufrufe erscheinen nicht im Protokoll und zählen nicht zum Kontingent (bei AIS.chat kostenlos bzw. sehr günstig).
  Zeitlimit 8 s je Frage; ist der Dienst nicht erreichbar, wird 5 Minuten lang nur per Wortvergleich gesucht.
- Wissensdateien der Kurse/Personas und angehängte Dateien im Chat gehen als Text an das Sprachmodell.
  **Bitte in die Datenschutzhinweise aufnehmen** (Privacy-Provider meldet: Embedding-Dienst, Tabelle `block_ai_chat_quellen`, Dateien).

**Datenschutz-API**
- block_ai_chat hat jetzt einen vollständigen Privacy-Provider (Export/Löschen der Quellen-Zeilen je Person); `block_ai_chat_wissen`
  enthält nur Lehrmaterial. Eine geplante Aufgabe löscht nachts Quellen-Zeilen, deren Protokolleintrag gelöscht wurde.
- Kontrollzentrum: speichert keine personenbezogenen Daten (nur Kurseinstellungen). Die Themen-Auswertung zeigt nur Anzahlen und
  Abschnittstitel, Themen erst ab 5 fragenden Personen.

**Grenzen gegen Überlastung**
- Wissensdateien: Anzahl und MB je Datei einstellbar (Standard 5 Dateien, 5 MB), höchstens 400 Abschnitte je Kurs bzw. Persona,
  DOCX entpackt höchstens 30 MB, PDF mit erhöhtem Speicher-/Zeitlimit.
- Chat: jede Nachricht höchstens „Zeichen je Anhang“ + 2000 Zeichen (Server-Prüfung), Anhänge pro Person und Tag.

**Bekannte Grenzen – bitte bewerten**
- *Kurs-Regeln hängen am Kontext der Anfrage* (wie beim Original-Kontrollzentrum): Kurs-Limit, Zeitplan und „aus während Tests“ gelten
  für Anfragen aus dem Kurs. Wer einen Webservice mit dem Kontext eines anderen Kurses aufruft, umgeht sie; die Limits der
  Administration im AI Manager bleiben die harte Obergrenze.
- *Aus während Tests* wird auf dem Server geprüft: offener Testversuch der Person im Kurs, in den letzten 3 Stunden bearbeitet (oder Anfrage
  von der Testseite). Ein nicht abgegebener Versuch sperrt den Chat im Kurs bis zu 3 Stunden.
- *KI-Modell pro Kurs*: Lehrkräfte sehen die Auswahl nur, wenn die Administration Verbindungen ausdrücklich freigibt
  (Einstellung „kursmodelle“, Standard: keine). Eine freigegebene Verbindung gilt dann auch für Lernende – nur günstige Modelle freigeben.
- *Prompt-Injection*: Kursdateien, Seitentitel und angehängte Dateien gelangen in die Anfrage an die KI (Kursdateien als System-Anweisung).
  Lehrkräfte sind für die Inhalte verantwortlich; Lernende können keine Wissensdateien hochladen.
- *Zeitplan* nutzt die Zeitzone des Servers.
- *Sprache*: Die neuen Texte sind überwiegend fest auf Deutsch geschrieben (Einstellungen mit Sprachstrings de/en). Für eine
  Veröffentlichung im Moodle-Verzeichnis müssten sie in Sprachstrings umgezogen werden.
- *Versionsnummern* liegen im Nummernraum des Herstellers (block_ai_chat 2026111703, block_ai_control 2025120402, local_ai_manager 2026102307).
  Bei einem späteren Wechsel zurück auf bycs-lp: Datenbank-Sicherung, Upgrade-Schritte prüfen.
- *KI-Hinweis*: Der ausführliche Hinweis erscheint einmal je Browser, danach eine kleine Zeile unter der Eingabe – bitte prüfen, ob das der
  Transparenzpflicht (Art. 50 AI Act) in Ihrer Umsetzung genügt.
