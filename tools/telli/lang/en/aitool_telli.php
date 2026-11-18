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
 * Lang strings for aitool_telli - EN.
 *
 * @package    aitool_telli
 * @copyright  2025 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['adddescription'] = 'The Telli API provides different AI models for German schools.';
$string['apikey'] = 'API key';
$string['availablemodels'] = 'Available models';
$string['availablemodelssetting'] = 'Available models';
$string['availablemodelssettingdesc'] = 'Enter the names of the models that should be available for selection by the tenant manager. One model per line. Names must be identical to returned "name" attribute of the model description returned by the /v1/models endpoint. Add a "#VISION" at the end of the name to declare it as vision model. Add a "#IMGGEN" at the end of the name to declare it as image generation model. Please note that changing this setting will only affect the select field in the instance edit form, it will not affect already configured AI tools.';
$string['baseurl'] = 'Base URL';
$string['baseurlsetting'] = 'Base URL for the API';
$string['baseurlsettingdesc'] = 'Insert the base URL of the Telli API here, stopping before "/v1..."';
$string['disabledmodelssetting'] = 'Disabled models';
$string['disabledmodelssettingdesc'] = 'Select the models that should not be available for selection when creating or editing a connector instance. Already configured instances using a disabled model will continue to work.';
$string['err_apiresult'] = 'An error occurred while trying to connect to the API.';
$string['err_contentfilter'] = 'Your request was rejected as a result of the content filter of the external tool. Your prompt probably requests something that is not allowed.';
$string['err_modelnotavailable'] = 'The currently configured model is not available (anymore).';
$string['err_noendpoint'] = 'No endpoint is configured. Please contact your administrator.';
$string['err_noimagedata'] = 'The external AI system could not generate an image. Your prompt probably requests something that is not allowed.';
$string['err_retrievingmodels'] = 'There was an error while trying to retrieve the list of models from the Telli API. Error code: {$a->code}.';
$string['eurocents'] = 'Cents';
$string['getconsumptiontask'] = 'Get Telli API consumption data';
$string['globalapikeysetting'] = 'Global API key';
$string['globalapikeysettingdesc'] = 'If this setting is set, this API key will be used for all requests. The endpoint field will then also be locked to the configured base URL for the API (or, if no base URL is configured, the endpoint already stored in the instance), so the tenant manager cannot point the connector to a self-chosen endpoint. If this setting is not set, the tenant manager will be able to insert own API keys.';
$string['managementpage'] = 'Management page';
$string['managementpagelink'] = 'Open management page';
$string['managementpagelinkdesc'] = 'On the management page you will have access to the current usage as well as the list of available models.';
$string['pluginname'] = 'Telli';
$string['privacy:metadata'] = 'The local ai_manager tool subplugin "Telli API" does not store any personal data.';
$string['retentionperiodsetting'] = 'Consumption data retention period';
$string['retentionperiodsettingdesc'] = 'Period for which consumption data should be kept in the database. Older data will be automatically deleted by the scheduled task. Default is 2 years.';
$string['retrieveinformation'] = 'Retrieve information';
$string['sincetime'] = 'Time since that consumption statistics should be calculated';
$string['statuscode'] = 'Status code';
$string['usage'] = 'Usage';
$string['wholeconsumption'] = 'Whole consumption';
// Local extension (AIS.chat Hamburg): import models of the API key.
$string['modelleuebernehmen'] = 'Import models from the API';
$string['modelleuebernehmen_anlegen'] = 'create connection';
$string['modelleuebernehmen_art'] = 'Type';
$string['modelleuebernehmen_bild'] = 'Image generation';
$string['modelleuebernehmen_bilderlesen'] = 'reads images';
$string['modelleuebernehmen_desc'] = 'Reads the models the global API key may use (GET /v1/models) and adds them to the model list of the AI manager; optionally creates one connection per text model and offers it in the AI control center.';
$string['modelleuebernehmen_eintragen'] = 'add';
$string['modelleuebernehmen_fehler'] = 'The model list could not be retrieved: {$a}';
$string['modelleuebernehmen_freigeben'] = 'Offer new connections to teachers in the AI control center ("AI model for this course")';
$string['modelleuebernehmen_info'] = 'The list shows the models your API key may use. New embedding connections are assigned to the purpose "Embedding" if nothing is set there yet. Otherwise existing entries and connections stay unchanged; which connection is used for which purpose is still set in the tenant configuration.';
$string['modelleuebernehmen_keine'] = 'The API returned no text or image models for this key.';
$string['modelleuebernehmen_keinzugang'] = 'Please enter the base URL and the global API key above first.';
$string['modelleuebernehmen_liste'] = 'Model list';
$string['modelleuebernehmen_modell'] = 'Model';
$string['modelleuebernehmen_ok'] = '{$a->modelle} model(s) added, {$a->verbindungen} connection(s) created.';
$string['modelleuebernehmen_verbindung'] = 'Connection';
$string['modelleuebernehmen_vorhanden'] = 'available';
$string['modelleuebernehmen_embedding'] = 'Embedding (search in knowledge files)';
