<?php


namespace Fharch\Core\Modulea\Mandanten\API;

// Beispiel für Mandanten-Autocomplete mit Urheber-Kennzeichen
class MandantenUrheberAutocompleteAPI extends AutocompleteAPI {
    public function __construct() {
        parent::__construct(
            'fv_mandant',
            ['ma_name'],
            'ma_id',
            ['ma_name'],
            "ei_urh_kennz IS NOT NULL AND ei_urh_kennz != ''"
            );
    }
}
