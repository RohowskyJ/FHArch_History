<?php

declare(strict_types=1);

namespace FWHist\Auth\API;

use FWHist\Services\API\AutoCompleteAPI;

// Beispiel für Benutzer-Autocomplete
class BenutzerAutocompleteAPI extends AutoCompleteAPI {
    public function __construct() {
        parent::__construct(
            'fv_ben_dat',
            ['fd_name'],
            'be_id',
            ['fd_name', 'fd_vname']
            );
    }
}
