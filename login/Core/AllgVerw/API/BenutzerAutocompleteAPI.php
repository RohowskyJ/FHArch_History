<?php
namespace Fharch\Core\AllgVerw\API;

// Beispiel für Benutzer-Autocomplete
class BenutzerAutocompleteAPI extends AutocompleteAPI {
    public function __construct() {
        parent::__construct(
            'fv_ben_dat',
            ['fd_name'],
            'be_id',
            ['fd_name', 'fd_vname']
            );
    }
}
