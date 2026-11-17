<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Local extension (AIS.chat Hamburg, 17.11.2026): import the models the API key may use (GET /v1/models) into the model list of
 * the AI manager, optionally create a connection per text model and offer it in the AI control center.
 *
 * @package    aitool_telli
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');

use core\output\html_writer;

require_admin();
$url = new moodle_url('/local/ai_manager/tools/telli/modelle_uebernehmen.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('modelleuebernehmen', 'aitool_telli'));
$PAGE->set_heading(get_string('modelleuebernehmen', 'aitool_telli'));

$basis = rtrim((string)get_config('aitool_telli', 'baseurl'), '/');
$schluessel = (string)get_config('aitool_telli', 'globalapikey');
$tenant = \core\di::get(\local_ai_manager\local\tenant_factory::class)->get()->get_identifier();

/**
 * Models of the API: name => [displayname, art (text|image), vision, mimetypes].
 */
$apimodelle = [];
$fehler = '';
if ($basis === '' || $schluessel === '') {
    $fehler = get_string('modelleuebernehmen_keinzugang', 'aitool_telli');
} else {
    try {
        $api = \core\di::get(\aitool_telli\local\apihandler::class);
        $api->init($schluessel, $basis);
        foreach ((array)json_decode($api->get_models_info(), true) as $m) {
            $art = $m['priceMetadata']['type'] ?? 'text';
            if (empty($m['name']) || !empty($m['isDeleted']) || !in_array($art, ['text', 'image'])) {
                continue;   // Embedding-Modelle, gelöschte Modelle
            }
            $formate = array_values(array_filter((array)($m['supportedImageFormats'] ?? []), 'is_string'));
            $apimodelle[(string)$m['name']] = [
                'displayname' => (string)($m['displayName'] ?? $m['name']),
                'art' => $art,
                'vision' => $art === 'text' && $formate ? 1 : 0,
                'mimetypes' => implode(',', array_map(fn($f) => strpos($f, '/') === false ? 'image/' . strtolower($f) : $f, $formate)),
            ];
        }
    } catch (\Throwable $e) {
        $fehler = get_string('modelleuebernehmen_fehler', 'aitool_telli', s($e->getMessage()));
    }
}

/** Existing connection (telli, this tenant) for a model id. */
$verbindung = function (int $modelid) use ($DB, $tenant): ?string {
    $name = $DB->get_field_select('local_ai_manager_instance', 'name', 'connector = ? AND tenant = ? AND model = ?',
        ['telli', $tenant, (string)$modelid], IGNORE_MULTIPLE);
    return $name ?: null;
};

if ($apimodelle && optional_param('uebernehmen', 0, PARAM_BOOL) && confirm_sesskey()) {
    $modelle = optional_param_array('modell', [], PARAM_RAW);
    $verbinden = optional_param_array('verbindung', [], PARAM_RAW);
    $freigeben = optional_param('freigeben', 0, PARAM_BOOL);
    $neu = $verbindungen = 0;
    $freigabe = array_filter(explode(',', (string)get_config('block_ai_control', 'kursmodelle')), 'strlen');
    foreach ($apimodelle as $name => $info) {
        $schl = md5($name);
        if (empty($modelle[$schl]) && empty($verbinden[$schl])) {
            continue;
        }
        $modell = $DB->get_record('local_ai_manager_model', ['name' => $name]);
        if (!$modell) {
            $modell = (object)['name' => $name, 'displayname' => $info['displayname'], 'description' => 'AIS.chat',
                'mimetypes' => $info['mimetypes'], 'textgeneration' => $info['art'] === 'text' ? 1 : 0, 'vision' => $info['vision'],
                'imggen' => $info['art'] === 'image' ? 1 : 0, 'tts' => 0, 'stt' => 0, 'deprecated' => 0,
                'timecreated' => time(), 'timemodified' => time()];
            $modell->id = $DB->insert_record('local_ai_manager_model', $modell);
            $neu++;
        }
        if (!$DB->record_exists('local_ai_manager_model_connector', ['modelid' => $modell->id, 'connector' => 'telli'])) {
            $DB->insert_record('local_ai_manager_model_connector', ['modelid' => $modell->id, 'connector' => 'telli',
                'timecreated' => time(), 'timemodified' => time()]);
        }
        if (!empty($verbinden[$schl]) && $info['art'] === 'text' && !$verbindung((int)$modell->id)) {
            $instanz = new \aitool_telli\instance();
            $instanz->set_name('AIS.chat – ' . $info['displayname']);
            $instanz->set_tenant($tenant);
            $instanz->set_connector('telli');
            $instanz->set_endpoint($basis . '/v1/chat/completions');
            $instanz->set_useglobalapikey(true);
            $instanz->set_apikey('');
            $instanz->set_model_id((int)$modell->id);
            $instanz->set_customfield1('0.7');
            $instanz->store();
            $verbindungen++;
            if ($freigeben) {
                $freigabe[] = (string)$instanz->get_id();
            }
        }
    }
    if ($freigeben && core_plugin_manager::instance()->get_plugin_info('block_ai_control')) {
        set_config('kursmodelle', implode(',', array_unique($freigabe)), 'block_ai_control');
    }
    \cache_helper::purge_all();
    redirect($url, get_string('modelleuebernehmen_ok', 'aitool_telli', (object)['modelle' => $neu, 'verbindungen' => $verbindungen]),
        null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo html_writer::div(get_string('modelleuebernehmen_info', 'aitool_telli'), 'alert alert-info');
if ($fehler !== '') {
    echo $OUTPUT->notification($fehler, 'error');
    echo $OUTPUT->footer();
    exit;
}
if (!$apimodelle) {
    echo $OUTPUT->notification(get_string('modelleuebernehmen_keine', 'aitool_telli'), 'warning');
    echo $OUTPUT->footer();
    exit;
}
$zeilen = '';
foreach ($apimodelle as $name => $info) {
    $schl = md5($name);
    $modell = $DB->get_record('local_ai_manager_model', ['name' => $name]);
    $vorhanden = $modell && $DB->record_exists('local_ai_manager_model_connector', ['modelid' => $modell->id, 'connector' => 'telli']);
    $vbname = $modell ? $verbindung((int)$modell->id) : null;
    $zeilen .= '<tr><td><strong>' . s($info['displayname']) . '</strong><br><code>' . s($name) . '</code></td>'
        . '<td>' . ($info['art'] === 'image' ? get_string('modelleuebernehmen_bild', 'aitool_telli') : 'Text'
            . ($info['vision'] ? ' + ' . get_string('modelleuebernehmen_bilderlesen', 'aitool_telli') : '')) . '</td>'
        . '<td>' . ($vorhanden ? '✅ ' . get_string('modelleuebernehmen_vorhanden', 'aitool_telli')
            : '<label><input type="checkbox" name="modell[' . $schl . ']" value="1" checked> ' . get_string('modelleuebernehmen_eintragen', 'aitool_telli') . '</label>')
        . '</td><td>' . ($info['art'] !== 'text' ? '–' : ($vbname ? '✅ ' . s($vbname)
            : '<label><input type="checkbox" name="verbindung[' . $schl . ']" value="1"> ' . get_string('modelleuebernehmen_anlegen', 'aitool_telli') . '</label>'))
        . '</td></tr>';
}
echo '<form method="post" action="' . $url->out() . '"><input type="hidden" name="sesskey" value="' . sesskey() . '">'
    . '<input type="hidden" name="uebernehmen" value="1">'
    . '<table class="table table-striped"><thead><tr><th>' . get_string('modelleuebernehmen_modell', 'aitool_telli') . '</th><th>'
    . get_string('modelleuebernehmen_art', 'aitool_telli') . '</th><th>' . get_string('modelleuebernehmen_liste', 'aitool_telli') . '</th><th>'
    . get_string('modelleuebernehmen_verbindung', 'aitool_telli') . '</th></tr></thead><tbody>' . $zeilen . '</tbody></table>'
    . (core_plugin_manager::instance()->get_plugin_info('block_ai_control')
        ? '<p><label><input type="checkbox" name="freigeben" value="1"> ' . get_string('modelleuebernehmen_freigeben', 'aitool_telli') . '</label></p>' : '')
    . '<button type="submit" class="btn btn-primary">' . get_string('modelleuebernehmen', 'aitool_telli') . '</button></form>';
echo $OUTPUT->footer();
