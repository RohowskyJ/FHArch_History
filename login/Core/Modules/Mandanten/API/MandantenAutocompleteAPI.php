<?php

namespace Fharch\Core\Modules\Mandanten\API;

use Fharch\Core\Services\API\AutocompleteAPI;

// Beispiel für Mandanten-Autocomplete (nur Name)
class MandantenAutocompleteAPI extends AutocompleteAPI {
    public function __construct() {
        parent::__construct(
            'fv_mandant',
            ['ei_org_name', 'ei_name'],
            'ei_id',
            ['ei_org_name', 'ei_name']
            );
    }
}

 