<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "ffpi_node_updates"
 *
 * Auto generated by Extension Builder 2016-12-23
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Freifunk knoten Benachrichtigung',
    'description' => 'Knoten Benachrichtigung bei ausfällen',
    'category' => 'plugin',
    'author' => 'Kevin Quiatkowski',
    'author_email' => 'kevin@pinneberg.freifunk.net',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '2.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
            'scheduler' => ''
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
