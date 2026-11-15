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
   Bonus für genannte Aufgabennummern; ohne Embedding-Modell nur Wortvergleich). PDF über `pdftotext` oder den mitgelieferten PHP-Leser
   smalot/pdfparser 2.13.1 (LGPL-3.0, `thirdparty/pdfparser`). Neue Tabelle `block_ai_chat_personachunks`.
2. **📎 Datei im Chat anhängen** (block_ai_chat), auch für Lernende: Text-, Code- und Notebook-Dateien werden im Browser gelesen und mit
   der Frage geschickt; im Verlauf steht nur „📎 Dateiname“. Grenzen prüft auch der Server.
3. **Einstellungen der Administration** (Website-Administration → Plugins → Blöcke → KI-Chat): Wissensdateien pro Persona, MB je Datei,
   Embedding-Modell (mit Statusanzeige), Dateien im Chat an/aus, Anhänge pro Person und Tag, Zeichen je Anhang.
4. **Kontrollzentrum** (block_ai_control): Ablaufdatum-Fehler (01.01.1970) behoben; Anfragen-Limit pro Kurs für Lernende (bis zur Vorgabe
   der Administration); KI-Modell pro Kurs; Einstellung, welche Verbindungen Lehrkräfte wählen dürfen.
5. **AI Manager** (local_ai_manager): neuer Haken `connector_instance_override` (Modell pro Kurs); aufgebrauchtes Kurs-Limit (Code 429)
   sperrt den Chat mit Hinweis, statt ihn auszublenden.

Ausführliche Begründungen und Code-Auszüge: Dokument „KI-Plugins der ByCS: Erfahrungen und Änderungsvorschläge“ (Abschnitte 1–10).

## Installation (Testsystem)

1. Die drei Zweige als ZIP herunterladen (Code → Download ZIP) oder klonen, Ordner umbenennen und ablegen:
   `local/ai_manager`, `blocks/ai_chat`, `blocks/ai_control`; dazu `tiny_ai` aus bycs-lp in `lib/editor/tiny/plugins/ai`.
2. Website-Administration → Mitteilungen → Upgrade.
3. Telli-Anschluss: Basis-URL der AIS.chat-API (bis vor `/v1`), globaler API-Schlüssel, Modelle. Für die Wissensdateien muss der Schlüssel
   zusätzlich ein Embedding-Modell freigegeben haben (Name in der KI-Chat-Einstellung „Embedding-Modell“, Standard `BAAI/bge-m3`).
4. KI-Tools-Administration: Mandant einschalten, Verbindungen anlegen, Zwecke für beide Rollen zuordnen.

Hinweis: `amd/build/anhang.min.js` ist eine unveränderte Kopie von `amd/src/anhang.js` (lokal ohne Node gebaut); `grunt amd` erzeugt
eine minifizierte Fassung.
