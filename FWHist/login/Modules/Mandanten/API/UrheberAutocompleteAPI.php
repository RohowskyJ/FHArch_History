<?php

declare(strict_types=1);

namespace FWHist\Modules\Mandanten\API;

use FWHist\Services\API\AutoCompleteAPI;

// Beispiel für Mandanten-Autocomplete mit Urheber-Kennzeichen
class UrheberAutocompleteAPI extends AutoCompleteAPI {
    public function __construct() {
        parent::__construct(
            'ma_mandanten',
            ['ei_name'],
            'ei_id',
            ['ei_name'],
            "ei_media IS NOT NULL AND ei_media != ''"
           // "ei_urh_kurzz IS NOT NULL AND ei_urh_kurzz != ''"
            );
    }
}
