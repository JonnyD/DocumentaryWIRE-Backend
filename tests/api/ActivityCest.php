<?php 

class ActivityCest
{
    public function _before(ApiTester $I)
    {
    }

    public function listForWidget(ApiTester $I)
    {
        $I->sendGET('api/v1/activity?show=widget');

        $expectedResponse = [
            [
                'type' => 'watchlist',
                'parent' => [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-1',
                        'documentaryTitle' => 'Documentary 1',
                        'documentarySummary' => 'Storyline',
                        'documentaryPoster' => 'http://localhost:8000/uploads/posters/poster.jpg'
                    ],
                    'name' => 'John Smith',
                    'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                    'username' => 'user1',
                ],
                'child' => [
                    [
                        'type' => 'watchlist',
                        'data' => [
                            'documentarySlug' => 'documentary-2',
                            'documentaryTitle' => 'Documentary 2',
                            'documentarySummary' => 'Storyline',
                            'documentaryPoster' => 'http://localhost:8000/uploads/posters/poster.jpg'
                        ],
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                        'username' => 'user1'
                    ],
                    [
                        'type' => 'watchlist',
                        'data' => [
                            'documentarySlug' => 'documentary-3',
                            'documentaryTitle' => 'Documentary 3',
                            'documentarySummary' => 'Storyline',
                            'documentaryPoster' => 'http://localhost:8000/uploads/posters/poster.jpg'
                        ],
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                        'username' => 'user1'
                    ],
                ]
            ],
            [
                'type' => 'joined',
                'parent' => [
                    'type' => 'joined',
                    'name' => 'John Smith',
                    'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                    'username' => 'user1'
                ],
                'child' => [
                    [
                        'type' => 'joined',
                        'name' => 'Sarah McCarthy',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                        'username' => 'user2'
                    ],
                    [
                        'type' => 'joined',
                        'name' => 'Andrew Walsh',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                        'username' => 'user3'
                    ]
                ]
            ],
            [
                'type' => 'watchlist',
                'parent' => [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-1',
                        'documentaryTitle' => 'Documentary 1',
                        'documentarySummary' => 'Storyline',
                        'documentaryPoster' => 'http://localhost:8000/uploads/posters/poster.jpg'
                    ],
                    'name' => 'John Smith',
                    'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                    'username' => 'user1'
                ],
                'child' => [
                    [
                        'type' => 'watchlist',
                        'data' => [
                            'documentarySlug' => 'documentary-2',
                            'documentaryTitle' => 'Documentary 2',
                            'documentarySummary' => 'Storyline',
                            'documentaryPoster' => 'http://localhost:8000/uploads/posters/poster.jpg'
                        ],
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                        'username' => 'user1'
                    ],
                    [
                        'type' => 'watchlist',
                        'data' => [
                            'documentarySlug' => 'documentary-3',
                            'documentaryTitle' => 'Documentary 3',
                            'documentarySummary' => 'Storyline',
                            'documentaryPoster' => 'http://localhost:8000/uploads/posters/poster.jpg'
                        ],
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                        'username' => 'user1'
                    ]
                ]
            ],
            [
                'type' => 'comment',
                'parent' => [
                    'type' => 'comment',
                    'data' => [
                        'commentText' => 'This is a comment 1',
                        'documentaryTitle' => 'Documentary 1',
                        'documentarySlug' => 'documentary-1',
                        'documentaryPoster' => 'http://localhost:8000/uploads/posters/poster.jpg'
                    ],
                    'name' => 'John Smith',
                    'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                    'username' => 'user1'
                ],
                'child' => []
            ],
            [
                'type' => 'joined',
                'parent' => [
                    'type' => 'joined',
                    'data' => [],
                    'name' => 'Anne Keating',
                    'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                    'username' => 'user4'
                ],
                'child' => [
                    [
                        'type' => 'joined',
                        'data' => [],
                        'name' => 'Jerry Carroll',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                        'username' => 'user5'
                    ],
                    [
                        'type' => 'joined',
                        'data' => [],
                        'name' => 'Sarah Kirwin',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg',
                        'username' => 'user6'
                    ]
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listActivity(ApiTester $I)
    {
        $I->sendGET('api/v1/activity');

        $expectedResponse = [
            'items' => [
                [
                    'type' => 'watchlist',
                    'component' => 'documentary',
                    'groupNumber' => 5,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'component' => 'documentary',
                    'groupNumber' => 5,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'component' => 'documentary',
                    'groupNumber' => 5,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'joined',
                    'component' => 'user',
                    'groupNumber' => 4,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'joined',
                    'component' => 'user',
                    'groupNumber' => 4,
                    'user' => [
                        'username' => 'user2',
                        'name' => 'Sarah McCarthy',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'joined',
                    'component' => 'user',
                    'groupNumber' => 4,
                    'user' => [
                        'username' => 'user3',
                        'name' => 'Andrew Walsh',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'component' => 'documentary',
                    'groupNumber' => 3,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'component' => 'documentary',
                    'groupNumber' => 3,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'comment',
                    'component' => 'documentary',
                    'groupNumber' => 2,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'joined',
                    'component' => 'user',
                    'groupNumber' => 1,
                    'user' => [
                        'username' => 'user4',
                        'name' => 'Anne Keating',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'joined',
                    'component' => 'user',
                    'groupNumber' => 1,
                    'user' => [
                        'username' => 'user5',
                        'name' => 'Jerry Carroll',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'joined',
                    'component' => 'user',
                    'groupNumber' => 1,
                    'user' => [
                        'username' => 'user6',
                        'name' => 'Sarah Kirwin',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
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
                    'component' => 'documentary',
                    'groupNumber' => 5,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'component' => 'documentary',
                    'groupNumber' => 5,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'component' => 'documentary',
                    'groupNumber' => 5,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'joined',
                    'component' => 'user',
                    'groupNumber' => 4,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'component' => 'documentary',
                    'groupNumber' => 3,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'component' => 'documentary',
                    'groupNumber' => 3,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'component' => 'documentary',
                    'groupNumber' => 3,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ],
                [
                    'type' => 'comment',
                    'component' => 'documentary',
                    'groupNumber' => 2,
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith',
                        'avatar' => 'http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg'
                    ]
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

}
