<?php
declare(strict_types=1);

namespace Fharch\Core\AllgVerw\API;

use Fharch\Core\Services\API\AutocompleteAPI;

// Beispiel für Bundesland-Autocomplete
class BdldAutocompleteAPI extends AutocompleteAPI {
    public function __construct() {
        parent::__construct(
            'fv_bundesld',
            ['bl_name', 'bl_blabkz'],
            'bl_blabkz',
            ['bl_name', 'bl_blabkz']
            );
    }
}
