<?php

namespace FWHist\Modules\Mitglieder\API;

use FWHist\Services\API\AutoCompleteAPI;

// Beispiel für Mandanten-Autocomplete (nur Name)
class MitgliederAutocompleteAPI extends AutoCompleteAPI {
    public function __construct() {
        parent::__construct(
            'mi_mitglieder',
            ['mi_org_name', 'mi_name'],
            'mi_id',
            ['mi_org_name', 'mi_name']
            );
    }
}