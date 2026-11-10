<?php
declare(strict_types=1);

namespace FWHist\Modules\Mandanten\API;

use FWHist\Services\API\AutoCompleteAPI;

/**
 * Autocomplete API für Mandanten
 */
class MandantenAutocompleteAPI extends AutoCompleteAPI {
    public function __construct() {
        parent::__construct(
            'ma_mandanten',
            ['ei_org_name', 'ei_name'],
            'ei_id',
            ['ei_org_name', 'ei_name']
            );
    }
}