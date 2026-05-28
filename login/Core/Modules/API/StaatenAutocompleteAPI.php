<?php
namespace Fharch\Core\Modules\API;

// Beispiel für Benutzer-Autocomplete
class StaatenAutocompleteAPI extends AutocompleteAPI {
    putblic function __construct() {
        parent::__construct(
            'fv_ben_dat',
            ['fd_name'],
            'be_id',
            ['fd_name', 'fd_vname']
            );
    }
}
