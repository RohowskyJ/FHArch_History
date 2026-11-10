<?php

class OrtsList
{
    public function getColumns(): array
    {
        return [
            ['title' => 'ID', 'field' => 'id', 'width' => 80],
            ['title' => 'Name', 'field' => 'name', 'widthGrow' => 2],
            ['title' => 'Bundesland', 'field' => 'st_abkzg', 'widthGrow' => 1],
            ['title' => 'Beschreibung', 'field' => 'description', 'widthGrow' => 3],
        ];
    }
    
    public function getFilters(): array
    {
        return [
            'selectFilters' => [
                'st_abkzg' => [
                    'label' => 'Bundesland',
                    'options' => [
                        'BE' => 'Berlin',
                        'BW' => 'Baden-Württemberg',
                        'BY' => 'Bayern',
                        'HE' => 'Hessen',
                        'NI' => 'Niedersachsen',
                    ],
                ],
            ],
            'textFilters' => ['name', 'description'],
        ];
    }
    
    public function getData(): array
    {
        return [
            ['id' => 1, 'name' => 'Ort A', 'st_abkzg' => 'BE', 'description' => 'Beschreibung A'],
            ['id' => 2, 'name' => 'Ort B', 'st_abkzg' => 'BW', 'description' => 'Beschreibung B'],
            ['id' => 3, 'name' => 'Ort C', 'st_abkzg' => 'BY', 'description' => 'Beschreibung C'],
            ['id' => 4, 'name' => 'Ort D', 'st_abkzg' => 'HE', 'description' => 'Beschreibung D'],
            ['id' => 5, 'name' => 'Ort E', 'st_abkzg' => 'NI', 'description' => 'Beschreibung E'],
            ['id' => 1, 'name' => 'Ort A', 'st_abkzg' => 'BE', 'description' => 'Beschreibung A'],
            ['id' => 2, 'name' => 'Ort B', 'st_abkzg' => 'BW', 'description' => 'Beschreibung B'],
            ['id' => 3, 'name' => 'Ort C', 'st_abkzg' => 'BY', 'description' => 'Beschreibung C'],
            ['id' => 4, 'name' => 'Ort D', 'st_abkzg' => 'HE', 'description' => 'Beschreibung D'],
            ['id' => 5, 'name' => 'Ort E', 'st_abkzg' => 'NI', 'description' => 'Beschreibung E'],
            ['id' => 1, 'name' => 'Ort A', 'st_abkzg' => 'BE', 'description' => 'Beschreibung A'],
            ['id' => 2, 'name' => 'Ort B', 'st_abkzg' => 'BW', 'description' => 'Beschreibung B'],
            ['id' => 3, 'name' => 'Ort C', 'st_abkzg' => 'BY', 'description' => 'Beschreibung C'],
            ['id' => 4, 'name' => 'Ort D', 'st_abkzg' => 'HE', 'description' => 'Beschreibung D'],
            ['id' => 5, 'name' => 'Ort E', 'st_abkzg' => 'NI', 'description' => 'Beschreibung E'],
            ['id' => 1, 'name' => 'Ort A', 'st_abkzg' => 'BE', 'description' => 'Beschreibung A'],
            ['id' => 2, 'name' => 'Ort B', 'st_abkzg' => 'BW', 'description' => 'Beschreibung B'],
            ['id' => 3, 'name' => 'Ort C', 'st_abkzg' => 'BY', 'description' => 'Beschreibung C'],
            ['id' => 4, 'name' => 'Ort D', 'st_abkzg' => 'HE', 'description' => 'Beschreibung D'],
            ['id' => 5, 'name' => 'Ort E', 'st_abkzg' => 'NI', 'description' => 'Beschreibung E'],
            ['id' => 1, 'name' => 'Ort A', 'st_abkzg' => 'BE', 'description' => 'Beschreibung A'],
            ['id' => 2, 'name' => 'Ort B', 'st_abkzg' => 'BW', 'description' => 'Beschreibung B'],
            ['id' => 3, 'name' => 'Ort C', 'st_abkzg' => 'BY', 'description' => 'Beschreibung C'],
            ['id' => 4, 'name' => 'Ort D', 'st_abkzg' => 'HE', 'description' => 'Beschreibung D'],
            ['id' => 5, 'name' => 'Ort E', 'st_abkzg' => 'NI', 'description' => 'Beschreibung E'],
            ['id' => 1, 'name' => 'Ort A', 'st_abkzg' => 'BE', 'description' => 'Beschreibung A'],
            ['id' => 2, 'name' => 'Ort B', 'st_abkzg' => 'BW', 'description' => 'Beschreibung B'],
            ['id' => 3, 'name' => 'Ort C', 'st_abkzg' => 'BY', 'description' => 'Beschreibung C'],
            ['id' => 4, 'name' => 'Ort D', 'st_abkzg' => 'HE', 'description' => 'Beschreibung D'],
            ['id' => 5, 'name' => 'Ort E', 'st_abkzg' => 'NI', 'description' => 'Beschreibung E'],
            ['id' => 1, 'name' => 'Ort A', 'st_abkzg' => 'BE', 'description' => 'Beschreibung A'],
            ['id' => 2, 'name' => 'Ort B', 'st_abkzg' => 'BW', 'description' => 'Beschreibung B'],
            ['id' => 3, 'name' => 'Ort C', 'st_abkzg' => 'BY', 'description' => 'Beschreibung C'],
            ['id' => 4, 'name' => 'Ort D', 'st_abkzg' => 'HE', 'description' => 'Beschreibung D'],
            ['id' => 5, 'name' => 'Ort E', 'st_abkzg' => 'NI', 'description' => 'Beschreibung E'],
            ['id' => 1, 'name' => 'Ort A', 'st_abkzg' => 'BE', 'description' => 'Beschreibung A'],
            ['id' => 2, 'name' => 'Ort B', 'st_abkzg' => 'BW', 'description' => 'Beschreibung B'],
            ['id' => 3, 'name' => 'Ort C', 'st_abkzg' => 'BY', 'description' => 'Beschreibung C'],
            ['id' => 4, 'name' => 'Ort D', 'st_abkzg' => 'HE', 'description' => 'Beschreibung D'],
            ['id' => 5, 'name' => 'Ort E', 'st_abkzg' => 'NI', 'description' => 'Beschreibung E'],
            ['id' => 1, 'name' => 'Ort A', 'st_abkzg' => 'BE', 'description' => 'Beschreibung A'],
            ['id' => 2, 'name' => 'Ort B', 'st_abkzg' => 'BW', 'description' => 'Beschreibung B'],
            ['id' => 3, 'name' => 'Ort C', 'st_abkzg' => 'BY', 'description' => 'Beschreibung C'],
            ['id' => 4, 'name' => 'Ort D', 'st_abkzg' => 'HE', 'description' => 'Beschreibung D'],
            ['id' => 5, 'name' => 'Ort E', 'st_abkzg' => 'NI', 'description' => 'Beschreibung E'],
            ['id' => 1, 'name' => 'Ort A', 'st_abkzg' => 'BE', 'description' => 'Beschreibung A'],
            ['id' => 2, 'name' => 'Ort B', 'st_abkzg' => 'BW', 'description' => 'Beschreibung B'],
            ['id' => 3, 'name' => 'Ort C', 'st_abkzg' => 'BY', 'description' => 'Beschreibung C'],
            ['id' => 4, 'name' => 'Ort D', 'st_abkzg' => 'HE', 'description' => 'Beschreibung D'],
            ['id' => 5, 'name' => 'Ort E', 'st_abkzg' => 'NI', 'description' => 'Beschreibung E'],
        ];
    }
}