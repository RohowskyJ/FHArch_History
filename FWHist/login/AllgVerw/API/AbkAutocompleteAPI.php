<?php
declare(strict_types=1);

namespace FWHist\AllgVerw\API;

use FWHist\Services\API\AutoCompleteAPI;

/**
 * Autocomplete API für Abkürzungen
 */
class AbkAutocompleteAPI extends AutoCompleteAPI {
    public function __construct() {
        $group = trim((string)($_GET['group'] ?? ''));
        $validGroups = ['MA_F', 'MU_F', 'MA_G', 'MU_G', 'PA_R', 'ORG'];
        $additionalCondition = null;

        if ($group !== '') {
            $additionalCondition = in_array($group, $validGroups, true)
                ? "ab_grp = '" . $group . "'"
                : '1 = 0';
        }

        parent::__construct(
            'hi_abk',
            ['ab_bezeichn', 'ab_abk'],
            'ab_abk',
            ['ab_bezeichn'],
            $additionalCondition
        );
    }
}
