<?php

return [
    'statuses' => [
        'pending' => 'Pending review',
        'active' => 'Active',
        'paused' => 'Paused',
        'rejected' => 'Rejected',
        'invited' => 'Invited',
    ],

    'rejection_reasons' => [
        'incomplete_info' => 'Incomplete or unclear company information',
        'ownership_conflict' => 'Ownership or revenue split does not match the hosts on file',
        'duplicate' => 'Duplicate of an existing company',
        'ineligible_hosts' => 'One or more hosts are not eligible to join a company',
        'other' => 'Other (see comment)',
    ],
];
