<?php

namespace Fharch\Core\Modules\Mandanten\API;

use Fharch\Core\Services\AutoCompleteAPI;

// Beispiel für Mandanten-Autocomplete (nur Name)
class MandantenAutocompleteAPI extends AutocompleteAPI {
    public function __construct() {
        parent::__construct(
            'fv_mandant',
            ['ma_name'],
            'ma_id',
            ['ma_name']
            );
    }
}

 