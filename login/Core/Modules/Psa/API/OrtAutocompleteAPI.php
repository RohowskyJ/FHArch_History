<?php

namespace Fharch\Core\Modules\Psa\API;

// Beispiel für Orts-Autocomplete mit gewichteter Suche
class OrtAutocompleteAPI extends AutocompleteAPI {
    public function __construct() {
        parent::__construct(
            'pso_ort_ref',
            ['fw_fw_name', 'fw_gd_name', 'fw_ab_name', 'fw_bz_name'],
            'ort_id', // Annahme für ID-Feld
            ['fw_fw_name', 'fw_gd_name', 'fw_ab_name', 'fw_bz_name']
            );
    }
    
    protected function queryDatabase(PDO $pdo, string $term): array {
        // Gewichtete Suche: Priorität auf fw_fw_name, dann fw_gd_name, etc.
        $sql = "SELECT ort_id, fw_fw_name, fw_gd_name, fw_ab_name, fw_bz_name,
                CASE
                    WHEN fw_fw_name LIKE :term1 THEN 1
                    WHEN fw_gd_name LIKE :term2 THEN 2
                    WHEN fw_ab_name LIKE :term3 THEN 3
                    WHEN fw_bz_name LIKE :term4 THEN 4
                    ELSE 5
                END as priority
                FROM pso_ort_ref
                WHERE fw_fw_name LIKE :term1 OR fw_gd_name LIKE :term2 OR fw_ab_name LIKE :term3 OR fw_bz_name LIKE :term4
                ORDER BY priority ASC, fw_fw_name ASC
                LIMIT 10";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':term1', $term . '%', \PDO::PARAM_STR);
        $stmt->bindValue(':term2', $term . '%', \PDO::PARAM_STR);
        $stmt->bindValue(':term3', $term . '%', \PDO::PARAM_STR);
        $stmt->bindValue(':term4', $term . '%', \PDO::PARAM_STR);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $label = $row['fw_fw_name'] . ' (' . $row['fw_gd_name'] . ', ' . $row['fw_ab_name'] . ', ' . $row['fw_bz_name'] . ')';
            $results[] = [
                'id' => $row['ort_id'],
                'label' => $label,
                'value' => $row['fw_fw_name']
            ];
        }
        
        return $results;
    }
}