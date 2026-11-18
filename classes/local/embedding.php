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

namespace local_ai_manager\local;

/**
 * Local extension (AIS.chat Hamburg): embeddings through the purpose "embedding" of the current tenant.
 *
 * Callers must check the user's access themselves before (e.g. with ai_manager_utils::get_ai_config() for the purpose they serve).
 * Requests are not logged and not counted against the users' request limits; they only need the tenant to be enabled and a tool
 * configured for the purpose "embedding" (role of the current user, falling back to the extended role, e.g. in cron).
 *
 * @package    local_ai_manager
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class embedding {

    /**
     * The connector configured for embeddings, or null.
     *
     * @return \local_ai_manager\base_connector|null
     */
    public static function connector(): ?\local_ai_manager\base_connector {
        global $USER;
        if (!in_array('embedding', \local_ai_manager\plugininfo\aipurpose::get_enabled_plugins())) {
            return null;
        }
        try {
            if (!\core\di::get(config_manager::class)->is_tenant_enabled()) {
                return null;
            }
            $factory = \core\di::get(connector_factory::class);
            $rollen = [];
            if (!empty($USER->id) && !isguestuser()) {
                $rollen[] = (new userinfo($USER->id))->get_role();
            }
            $rollen[] = userinfo::ROLE_EXTENDED;
            foreach (array_unique($rollen) as $rolle) {
                $instanz = $factory->get_connector_instance_by_purpose('embedding', $rolle);
                if ($instanz && $instanz->is_enabled()) {
                    $klasse = '\\aitool_' . $instanz->get_connector() . '\\connector';
                    return new $klasse($instanz);
                }
            }
        } catch (\Throwable $e) {
            debugging('local_ai_manager embeddings: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
        return null;
    }

    /**
     * Is an embedding tool configured for the current tenant?
     *
     * @return bool
     */
    public static function available(): bool {
        return self::connector() !== null;
    }

    /**
     * Name of the configured embedding model (to tell whether stored vectors are comparable), or '' if none.
     *
     * @return string
     */
    public static function model_name(): string {
        $connector = self::connector();
        return $connector ? (string)$connector->get_instance()->get_model_name() : '';
    }

    /**
     * Create embeddings.
     *
     * @param string[] $texts the texts
     * @param int $timeout timeout in seconds
     * @return array|null vectors (same order as $texts) or null
     */
    public static function embed(array $texts, int $timeout = 10): ?array {
        $connector = self::connector();
        return $connector && $texts ? $connector->embed(array_values($texts), $timeout) : null;
    }
}
