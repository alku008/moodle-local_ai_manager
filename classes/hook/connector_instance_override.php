<?php
// Lokale Erweiterung (15.11.2026, nicht vom Hersteller): erlaubt anderen Plugins, für eine Anfrage eine andere KI-Verbindung
// (Instanz) des Mandanten zu wählen – z. B. das KI-Kontrollzentrum ein Modell pro Kurs. Wird in manager::perform_request nach
// additional_user_restriction ausgelöst. Patch: ki-plugins-bayern/patches/.

namespace local_ai_manager\hook;

use context;
use local_ai_manager\base_purpose;
use local_ai_manager\local\userinfo;

#[\core\attribute\label('Allows plugins to choose a different connector instance of the tenant for a request (e.g. per course).')]
#[\core\attribute\tags('local_ai_manager')]
class connector_instance_override {
    /** @var int|null id of the connector instance to use instead of the purpose default, null = keep default */
    private ?int $instanceid = null;

    public function __construct(
        private readonly userinfo $userinfo,
        private readonly ?context $context,
        private readonly base_purpose $purpose,
    ) {
    }

    public function get_userinfo(): userinfo {
        return $this->userinfo;
    }

    public function get_context(): ?context {
        return $this->context;
    }

    public function get_purpose(): base_purpose {
        return $this->purpose;
    }

    public function set_instanceid(?int $instanceid): void {
        $this->instanceid = $instanceid;
    }

    public function get_instanceid(): ?int {
        return $this->instanceid;
    }
}
