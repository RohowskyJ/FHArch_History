<?php
declare(strict_types=1);

namespace Fharch\Core\AllgVerw\API;

use Fharch\Core\Services\API\AutocompleteAPI;

/**
 * Autocomplete API für Staaten
 */
class StaatenAutocompleteAPI extends AutocompleteAPI {
    public function __construct() {
        parent::__construct(
            'fv_staaten',
            ['st_name', 'st_abkzg'],
            'st_abkzg',
            ['st_name', 'st_abkzg']
        );
    }
}
