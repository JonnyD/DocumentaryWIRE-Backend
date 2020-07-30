<?php

class ActivityCest
{
    public function _before(ApiTester $I)
    {
    }

    public function listForWidget(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;
        /** @var \App\Entity\User $user1 */
        $user1 = $I->grabEntityFromRepository($userClass, [
           'username' => "user1"
        ]);
        /** @var \App\Entity\User $user2 */
        $user2 = $I->grabEntityFromRepository($userClass, [
            'username' => "user2"
        ]);
        /** @var \App\Entity\User $user3 */
        $user3 = $I->grabEntityFromRepository($userClass, [
            'username' => "user3"
        ]);
        /** @var \App\Entity\User $user4 */
        $user4 = $I->grabEntityFromRepository($userClass, [
            'username' => "user4"
        ]);
        /** @var \App\Entity\User $user5 */
        $user5 = $I->grabEntityFromRepository($userClass, [
            'username' => "user5"
        ]);
        /** @var \App\Entity\User $user6 */
        $user6 = $I->grabEntityFromRepository($userClass, [
            'username' => "user6"
        ]);

        $activityClass = \App\Entity\Activity::class;
        /** @var App\Entity\Activity $activity1 */
        $activity1 = $I->grabEntityFromRepository($activityClass, [
           'type' => 'watchlist',
            'component' => 'documentary',
            'groupNumber' => 5,
            'user' => $user1->getId(),
            'createdAt' => "2020-01-20 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity2 */
        $activity2 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'watchlist',
            'component' => 'documentary',
            'groupNumber' => 5,
            'user' => $user1->getId(),
            'createdAt' => "2020-01-19 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity3 */
        $activity3 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'watchlist',
            'component' => 'documentary',
            'groupNumber' => 5,
            'user' => $user1->getId(),
            'createdAt' => "2020-01-18 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity4 */
        $activity4 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'joined',
            'component' => 'user',
            'groupNumber' => 4,
            'user' => $user1->getId(),
            'createdAt' => "2020-01-17 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity5 */
        $activity5 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'joined',
            'component' => 'user',
            'groupNumber' => 4,
            'user' => $user2->getId(),
            'createdAt' => "2020-01-16 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity6 */
        $activity6 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'joined',
            'component' => 'user',
            'groupNumber' => 4,
            'user' => $user3->getId(),
            'createdAt' => "2020-01-15 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity7 */
        $activity7 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'watchlist',
            'component' => 'documentary',
            'groupNumber' => 3,
            'user' => $user4->getId(),
            'createdAt' => "2020-01-14 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity8 */
        $activity8 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'watchlist',
            'component' => 'documentary',
            'groupNumber' => 3,
            'user' => $user4->getId(),
            'createdAt' => "2020-01-13 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity9 */
        $activity9 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'watchlist',
            'component' => 'documentary',
            'groupNumber' => 3,
            'user' => $user4->getId(),
            'createdAt' => "2020-01-12 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity10 */
        $activity10 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'comment',
            'component' => 'documentary',
            'groupNumber' => 2,
            'user' => $user5->getId(),
            'createdAt' => "2020-01-11 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity11 */
        $activity11 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'joined',
            'component' => 'user',
            'groupNumber' => 1,
            'user' => $user4->getId(),
            'createdAt' => "2020-01-10 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity12 */
        $activity12 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'joined',
            'component' => 'user',
            'groupNumber' => 1,
            'user' => $user5->getId(),
            'createdAt' => "2020-01-09 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity13 */
        $activity13 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'joined',
            'component' => 'user',
            'groupNumber' => 1,
            'user' => $user6->getId(),
            'createdAt' => "2020-01-08 00:00:00"
        ]);


        $I->sendGET('api/v1/activity?show=widget');

        $expectedResponse = [
            [
                "type" => "watchlist",
                "created" => [
                    "date" => "2020-01-18 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "parent" => [
                    "id" => $activity1->getId(),
                    "objectId" => $activity1->getObjectId(),
                    "type" => "watchlist",
                    "data" => [
                        "documentaryId" => $activity1->getObjectId(),
                        "documentarySlug" => "documentary-1",
                        "documentaryTitle" => "Documentary 1",
                        "documentarySummary" => "Storyline",
                        "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                    ],
                    "component" => "documentary",
                    "groupNumber" => 5,
                    "user" => [
                        "name" => "John Smith",
                        "username" => "user1",
                        "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                    ],
                    "createdAt" => [
                        "date" => "2020-01-20 00:00:00.000000",
                        "timezone_type" => 3,
                        "timezone" => "Europe/Berlin"
                    ]
                ],
                "child" => [
                    [
                        "id" => $activity2->getId(),
                        "objectId" => $activity2->getObjectId(),
                        "type" => "watchlist",
                        "data" => [
                            "documentaryId" => $activity2->getObjectId(),
                            "documentarySlug" => "documentary-2",
                            "documentaryTitle" => "Documentary 2",
                            "documentarySummary" => "Storyline",
                            "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                        ],
                        "component" => "documentary",
                        "groupNumber" => 5,
                        "user" => [
                            "name" => "John Smith",
                            "username" => "user1",
                            "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                        ],
                        "createdAt" => [
                            "date" => "2020-01-19 00:00:00.000000",
                            "timezone_type" => 3,
                            "timezone" => "Europe/Berlin"
                        ]
                    ],
                    [
                        "id" => $activity3->getId(),
                        "objectId" => $activity3->getObjectId(),
                        "type" => "watchlist",
                        "data" => [
                            "documentaryId" => $activity3->getObjectId(),
                            "documentarySlug" => "documentary-3",
                            "documentaryTitle" => "Documentary 3",
                            "documentarySummary" => "Storyline",
                            "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                        ],
                        "component" => "documentary",
                        "groupNumber" => 5,
                        "user" => [
                            "name" => "John Smith",
                            "username" => "user1",
                            "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                        ],
                        "createdAt" => [
                            "date" => "2020-01-18 00:00:00.000000",
                            "timezone_type" => 3,
                            "timezone" => "Europe/Berlin"
                        ]
                    ]
                ]
            ],
            [
                "type" => "joined",
                "created" => [
                    "date" => "2020-01-15 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "parent" => [
                    "id" => $activity4->getId(),
                    "objectId" => $activity4->getObjectId(),
                    "type" => "joined",
                    "data" => [],
                    "component" => "user",
                    "groupNumber" => 4,
                    "user" => [
                        "name" => "John Smith",
                        "username" => "user1",
                        "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                    ],
                    "createdAt" => [
                        "date" => "2020-01-17 00:00:00.000000",
                        "timezone_type" => 3,
                        "timezone" => "Europe/Berlin"
                    ]
                ],
                "child" => [
                    [
                        "id" => $activity5->getId(),
                        "objectId" => $activity5->getObjectId(),
                        "type" => "joined",
                        "data" => [],
                        "component" => "user",
                        "groupNumber" => 4,
                        "user" => [
                            "name" => "Sarah McCarthy",
                            "username" => "user2",
                            "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                        ],
                        "createdAt" => [
                            "date" => "2020-01-16 00:00:00.000000",
                            "timezone_type" => 3,
                            "timezone" => "Europe/Berlin"
                        ]
                    ],
                    [
                        "id" => $activity6->getId(),
                        "objectId" => $activity6->getObjectId(),
                        "type" => "joined",
                        "data" => [],
                        "component" => "user",
                        "groupNumber" => 4,
                        "user" => [
                            "name" => "Andrew Walsh",
                            "username" => "user3",
                            "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                        ],
                        "createdAt" => [
                            "date" => "2020-01-15 00:00:00.000000",
                            "timezone_type" => 3,
                            "timezone" => "Europe/Berlin"
                        ]
                    ]
                ]
            ],
            [
                "type" => "watchlist",
                "created" => [
                    "date" => "2020-01-12 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "parent" => [
                    "id" => $activity7->getId(),
                    "objectId" => $activity7->getObjectId(),
                    "type" => "watchlist",
                    "data" => [
                        "documentaryId" => $activity7->getObjectId(),
                        "documentarySlug" => "documentary-1",
                        "documentaryTitle" => "Documentary 1",
                        "documentarySummary" => "Storyline",
                        "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                    ],
                    "component" => "documentary",
                    "groupNumber" => 3,
                    "user" => [
                        "name" => "Anne Keating",
                        "username" => "user4",
                        "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                    ],
                    "createdAt" => [
                        "date" => "2020-01-14 00:00:00.000000",
                        "timezone_type" => 3,
                        "timezone" => "Europe/Berlin"
                    ]
                ],
                "child" => [
                    [
                        "id" => $activity8->getId(),
                        "objectId" => $activity8->getObjectId(),
                        "type" => "watchlist",
                        "data" => [
                            "documentaryId" => $activity8->getObjectId(),
                            "documentarySlug" => "documentary-2",
                            "documentaryTitle" => "Documentary 2",
                            "documentarySummary" => "Storyline",
                            "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                        ],
                        "component" => "documentary",
                        "groupNumber" => 3,
                        "user" => [
                            "name" => "Anne Keating",
                            "username" => "user4",
                            "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                        ],
                        "createdAt" => [
                            "date" => "2020-01-13 00:00:00.000000",
                            "timezone_type" => 3,
                            "timezone" => "Europe/Berlin"
                        ]
                    ],
                    [
                        "id" => $activity9->getId(),
                        "objectId" => $activity9->getObjectId(),
                        "type" => "watchlist",
                        "data" => [
                            "documentaryId" => $activity9->getObjectId(),
                            "documentarySlug" => "documentary-3",
                            "documentaryTitle" => "Documentary 3",
                            "documentarySummary" => "Storyline",
                            "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                        ],
                        "component" => "documentary",
                        "groupNumber" => 3,
                        "user" => [
                            "name" => "Anne Keating",
                            "username" => "user4",
                            "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                        ],
                        "createdAt" => [
                            "date" => "2020-01-12 00:00:00.000000",
                            "timezone_type" => 3,
                            "timezone" => "Europe/Berlin"
                        ]
                    ]
                ]
            ],
            [
                "type" => "comment",
                "created" => [
                    "date" => "2020-01-11 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "parent" => [
                    "id" => $activity10->getId(),
                    "objectId" => $activity10->getObjectId(),
                    "type" => "comment",
                    "data" => [
                        "commentId" => $activity10->getObjectId(),
                        "commentText" => "This is a comment 1",
                        "documentaryTitle" => "Documentary 1",
                        "documentarySlug" => "documentary-1",
                        "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                    ],
                    "component" => "documentary",
                    "groupNumber" => 2,
                    "user" => [
                        "name" => "Jerry Carroll",
                        "username" => "user5",
                        "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                    ],
                    "createdAt" => [
                        "date" => "2020-01-11 00:00:00.000000",
                        "timezone_type" => 3,
                        "timezone" => "Europe/Berlin"
                    ]
                ]
            ],
            [
                "type" => "joined",
                "created" => [
                    "date" => "2020-01-08 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "parent" => [
                    "id" => $activity11->getId(),
                    "objectId" => $activity11->getObjectId(),
                    "type" => "joined",
                    "data" => [],
                    "component" => "user",
                    "groupNumber" => 1,
                    "user" => [
                        "name" => "Anne Keating",
                        "username" => "user4",
                        "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                    ],
                    "createdAt" => [
                        "date" => "2020-01-10 00:00:00.000000",
                        "timezone_type" => 3,
                        "timezone" => "Europe/Berlin"
                    ]
                ],
                "child" => [
                    [
                        "id" => $activity12->getId(),
                        "objectId" => $activity12->getObjectId(),
                        "type" => "joined",
                        "data" => [],
                        "component" => "user",
                        "groupNumber" => 1,
                        "user" => [
                            "name" => "Jerry Carroll",
                            "username" => "user5",
                            "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                        ],
                        "createdAt" => [
                            "date" => "2020-01-09 00:00:00.000000",
                            "timezone_type" => 3,
                            "timezone" => "Europe/Berlin"
                        ]
                    ],
                    [
                        "id" => $activity13->getId(),
                        "objectId" => $activity13->getObjectId(),
                        "type" => "joined",
                        "data" => [],
                        "component" => "user",
                        "groupNumber" => 1,
                        "user" => [
                            "name" => "Sarah Kirwin",
                            "username" => "user6",
                            "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                        ],
                        "createdAt" => [
                            "date" => "2020-01-08 00:00:00.000000",
                            "timezone_type" => 3,
                            "timezone" => "Europe/Berlin"
                        ]
                    ]
                ]
            ]
        ];

        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listActivity(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;
        /** @var \App\Entity\User $user1 */
        $user1 = $I->grabEntityFromRepository($userClass, [
            'username' => "user1"
        ]);
        /** @var \App\Entity\User $user2 */
        $user2 = $I->grabEntityFromRepository($userClass, [
            'username' => "user2"
        ]);
        /** @var \App\Entity\User $user3 */
        $user3 = $I->grabEntityFromRepository($userClass, [
            'username' => "user3"
        ]);
        /** @var \App\Entity\User $user4 */
        $user4 = $I->grabEntityFromRepository($userClass, [
            'username' => "user4"
        ]);
        /** @var \App\Entity\User $user5 */
        $user5 = $I->grabEntityFromRepository($userClass, [
            'username' => "user5"
        ]);
        /** @var \App\Entity\User $user6 */
        $user6 = $I->grabEntityFromRepository($userClass, [
            'username' => "user6"
        ]);

        $activityClass = \App\Entity\Activity::class;
        /** @var App\Entity\Activity $activity1 */
        $activity1 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'watchlist',
            'component' => 'documentary',
            'groupNumber' => 5,
            'user' => $user1->getId(),
            'createdAt' => "2020-01-20 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity2 */
        $activity2 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'watchlist',
            'component' => 'documentary',
            'groupNumber' => 5,
            'user' => $user1->getId(),
            'createdAt' => "2020-01-19 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity3 */
        $activity3 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'watchlist',
            'component' => 'documentary',
            'groupNumber' => 5,
            'user' => $user1->getId(),
            'createdAt' => "2020-01-18 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity4 */
        $activity4 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'joined',
            'component' => 'user',
            'groupNumber' => 4,
            'user' => $user1->getId(),
            'createdAt' => "2020-01-17 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity5 */
        $activity5 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'joined',
            'component' => 'user',
            'groupNumber' => 4,
            'user' => $user2->getId(),
            'createdAt' => "2020-01-16 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity6 */
        $activity6 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'joined',
            'component' => 'user',
            'groupNumber' => 4,
            'user' => $user3->getId(),
            'createdAt' => "2020-01-15 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity7 */
        $activity7 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'watchlist',
            'component' => 'documentary',
            'groupNumber' => 3,
            'user' => $user4->getId(),
            'createdAt' => "2020-01-14 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity8 */
        $activity8 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'watchlist',
            'component' => 'documentary',
            'groupNumber' => 3,
            'user' => $user4->getId(),
            'createdAt' => "2020-01-13 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity9 */
        $activity9 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'watchlist',
            'component' => 'documentary',
            'groupNumber' => 3,
            'user' => $user4->getId(),
            'createdAt' => "2020-01-12 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity10 */
        $activity10 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'comment',
            'component' => 'documentary',
            'groupNumber' => 2,
            'user' => $user5->getId(),
            'createdAt' => "2020-01-11 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity11 */
        $activity11 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'joined',
            'component' => 'user',
            'groupNumber' => 1,
            'user' => $user4->getId(),
            'createdAt' => "2020-01-10 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity12 */
        $activity12 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'joined',
            'component' => 'user',
            'groupNumber' => 1,
            'user' => $user5->getId(),
            'createdAt' => "2020-01-09 00:00:00"
        ]);

        /** @var App\Entity\Activity $activity13 */
        $activity13 = $I->grabEntityFromRepository($activityClass, [
            'type' => 'joined',
            'component' => 'user',
            'groupNumber' => 1,
            'user' => $user6->getId(),
            'createdAt' => "2020-01-08 00:00:00"
        ]);

        $I->sendGET('api/v1/activity');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            "items" => [
            [
                "id" => $activity1->getId(),
                "objectId" => $activity1->getObjectId(),
                "type" => "watchlist",
                "data" => [
                    "documentaryId" => $activity1->getObjectId(),
                    "documentarySlug" => "documentary-1",
                    "documentaryTitle" => "Documentary 1",
                    "documentarySummary" => "Storyline",
                    "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                ],
                "component" => "documentary",
                "groupNumber" => 5,
                "user" => [
                    "name" => "John Smith",
                    "username" => "user1",
                    "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                ],
                "createdAt" => [
                    "date" => "2020-01-20 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ]
            ],
            [
                "id" => $activity2->getId(),
                "objectId" => $activity2->getObjectId(),
                "type" => "watchlist",
                "data" => [
                    "documentaryId" => $activity2->getObjectId(),
                    "documentarySlug" => "documentary-2",
                    "documentaryTitle" => "Documentary 2",
                    "documentarySummary" => "Storyline",
                    "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                ],
                "component" => "documentary",
                "groupNumber" => 5,
                "user" => [
                    "name" => "John Smith",
                    "username" => "user1",
                    "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                ],
                "createdAt" => [
                    "date" => "2020-01-19 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ]
            ],
            [
                "id" => $activity3->getId(),
                "objectId" => $activity3->getObjectId(),
                "type" => "watchlist",
                "data" => [
                    "documentaryId" => $activity3->getObjectId(),
                    "documentarySlug" => "documentary-3",
                    "documentaryTitle" => "Documentary 3",
                    "documentarySummary" => "Storyline",
                    "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                ],
                "component" => "documentary",
                "groupNumber" => 5,
                "user" => [
                    "name" => "John Smith",
                    "username" => "user1",
                    "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                ],
                "createdAt" => [
                    "date" => "2020-01-18 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ]
            ],
            [
                "id" => $activity4->getId(),
                "objectId" => $activity4->getObjectId(),
                "type" => "joined",
                "data" => [],
                "component" => "user",
                "groupNumber" => 4,
                "user" => [
                    "name" => "John Smith",
                    "username" => "user1",
                    "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                ],
                "createdAt" => [
                    "date" => "2020-01-17 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ]
            ],
            [
                "id" => $activity5->getId(),
                "objectId" => $activity5->getObjectId(),
                "type" => "joined",
                "data" => [],
                "component" => "user",
                "groupNumber" => 4,
                "user" => [
                    "name" => "Sarah McCarthy",
                    "username" => "user2",
                    "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                ],
                "createdAt" => [
                    "date" => "2020-01-16 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ]
            ],
            [
                "id" => $activity6->getId(),
                "objectId" => $activity6->getObjectId(),
                "type" => "joined",
                "data" => [],
                "component" => "user",
                "groupNumber" => 4,
                "user" => [
                    "name" => "Andrew Walsh",
                    "username" => "user3",
                    "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                ],
                "createdAt" => [
                    "date" => "2020-01-15 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ]
            ],
            [
                "id" => $activity7->getId(),
                "objectId" => $activity7->getObjectId(),
                "type" => "watchlist",
                "data" => [
                    "documentaryId" => $activity7->getObjectId(),
                    "documentarySlug" => "documentary-1",
                    "documentaryTitle" => "Documentary 1",
                    "documentarySummary" => "Storyline",
                    "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                ],
                "component" => "documentary",
                "groupNumber" => 3,
                "user" => [
                    "name" => "Anne Keating",
                    "username" => "user4",
                    "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                ],
                "createdAt" => [
                    "date" => "2020-01-14 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ]
            ],
            [
                "id" => $activity8->getId(),
                "objectId" => $activity8->getObjectId(),
                "type" => "watchlist",
                "data" => [
                    "documentaryId" => $activity8->getObjectId(),
                    "documentarySlug" => "documentary-2",
                    "documentaryTitle" => "Documentary 2",
                    "documentarySummary" => "Storyline",
                    "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                ],
                "component" => "documentary",
                "groupNumber" => 3,
                "user" => [
                    "name" => "Anne Keating",
                    "username" => "user4",
                    "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                ],
                "createdAt" => [
                    "date" => "2020-01-13 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ]
            ],
            [
                "id" => $activity9->getId(),
                "objectId" => $activity9->getObjectId(),
                "type" => "watchlist",
                "data" => [
                    "documentaryId" => $activity9->getObjectId(),
                    "documentarySlug" => "documentary-3",
                    "documentaryTitle" => "Documentary 3",
                    "documentarySummary" => "Storyline",
                    "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                ],
                "component" => "documentary",
                "groupNumber" => 3,
                "user" => [
                    "name" => "Anne Keating",
                    "username" => "user4",
                    "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                ],
                "createdAt" => [
                    "date" => "2020-01-12 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ]
            ],
            [
                "id" => $activity10->getId(),
                "objectId" => $activity10->getObjectId(),
                "type" => "comment",
                "data" => [
                    "commentId" => $activity10->getObjectId(),
                    "commentText" => "This is a comment 1",
                    "documentaryTitle" => "Documentary 1",
                    "documentarySlug" => "documentary-1",
                    "documentaryPoster" => "http://localhost:8000/uploads/posters/poster.jpg"
                ],
                "component" => "documentary",
                "groupNumber" => 2,
                "user" => [
                    "name" => "Jerry Carroll",
                    "username" => "user5",
                    "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                ],
                "createdAt" => [
                    "date" => "2020-01-11 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ]
            ],
            [
                "id" => $activity11->getId(),
                "objectId" => $activity11->getObjectId(),
                "type" => "joined",
                "data" => [],
                "component" => "user",
                "groupNumber" => 1,
                "user" => [
                    "name" => "Anne Keating",
                    "username" => "user4",
                    "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                ],
                "createdAt" => [
                    "date" => "2020-01-10 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ]
            ],
            [
                "id" => $activity12->getId(),
                "objectId" => $activity12->getObjectId(),
                "type" => "joined",
                "data" => [],
                "component" => "user",
                "groupNumber" => 1,
                "user" => [
                    "name" => "Jerry Carroll",
                    "username" => "user5",
                    "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg"
                ],
                "createdAt" => [
                    "date" => "2020-01-09 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ]
            ]
        ]
        ];

        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listActivityForUser(ApiTester $I)
    {
        $username = 'user1';
        $I->sendGET('api/v1/activity?user=' . $username);

        $expectedResponse = [
            'items' => [
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-1',
                        'documentaryTitle' => 'Documentary 1',
                        'documentarySummary' => 'Storyline'
                    ],
                    'component' => 'documentary',
                    'groupNumber' => 5,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-2',
                        'documentaryTitle' => 'Documentary 2',
                        'documentarySummary' => 'Storyline'
                    ],
                    'component' => 'documentary',
                    'groupNumber' => 5,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-3',
                        'documentaryTitle' => 'Documentary 3',
                        'documentarySummary' => 'Storyline'
                    ],
                    'component' => 'documentary',
                    'groupNumber' => 5,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith'
                    ]
                ],
                [
                    'type' => 'joined',
                    'data' => [],
                    'component' => 'user',
                    'groupNumber' => 4,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith'
                    ]
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

}
