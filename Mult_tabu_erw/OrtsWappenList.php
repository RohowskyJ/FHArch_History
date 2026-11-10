<?php

class OrtsWappenList
{
    public function getColumns(): array
    {
        return [
            ['title' => 'ID', 'field' => 'id', 'width' => 80],
            ['title' => 'Name', 'field' => 'name', 'widthGrow' => 2],
            ['title' => 'Status', 'field' => 'status', 'widthGrow' => 1],
            ['title' => 'Beschreibung', 'field' => 'description', 'widthGrow' => 3],
        ];
    }
    
    public function getFilters(): array
    {
        return [
            'selectFilters' => [
                'status' => [
                    'label' => 'Status',
                    'options' => [
                        'aktiv' => 'aktiv',
                        'archiv' => 'archiv',
                        'in_pruefung' => 'in Prüfung',
                    ],
                ],
            ],
            'textFilters' => ['name', 'description'],
        ];
    }
    
    public function getData(): array
    {
        return [
            ['id' => 1, 'name' => 'Wappen A', 'status' => 'aktiv', 'description' => 'Beschreibung A'],
            ['id' => 2, 'name' => 'Wappen B', 'status' => 'archiv', 'description' => 'Beschreibung B'],
            ['id' => 3, 'name' => 'Wappen C', 'status' => 'in_pruefung', 'description' => 'Beschreibung C'],
        ];
    }
}