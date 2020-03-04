<?php 

class CommunityCest
{
    public function _before(ApiTester $I)
    {
    }

    public function listActivityAsGuest(ApiTester $I)
    {
        $I->sendGET('api/v1/community');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'items' => [
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-1',
                        'documentaryTitle' => 'Documentary 1',
                        'documentarySummary' => 'Storyline',
                    ],
                    'user' => [
                        'name' => 'John Smith',
                        'username' => 'user1'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-2',
                        'documentaryTitle' => 'Documentary 2',
                        'documentarySummary' => 'Storyline',
                    ],
                    'user' => [
                        'name' => 'John Smith',
                        'username' => 'user1'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-3',
                        'documentaryTitle' => 'Documentary 3',
                        'documentarySummary' => 'Storyline',
                    ],
                    'user' => [
                        'name' => 'John Smith',
                        'username' => 'user1'
                    ]
                ],
                [
                    'type' => 'joined',
                    'data' => [],
                    'user' => [
                        'name' => 'John Smith',
                        'username' => 'user1'
                    ]
                ],
                [
                    'type' => 'joined',
                    'data' => [],
                    'user' => [
                        'name' => 'Sarah McCarthy',
                        'username' => 'user2'
                    ]
                ],
                [
                    'type' => 'joined',
                    'data' => [],
                    'user' => [
                        'name' => 'Andrew Walsh',
                        'username' => 'user3'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-1',
                        'documentaryTitle' => 'Documentary 1',
                        'documentarySummary' => 'Storyline'
                    ],
                    'user' => [
                        'name' => 'Anne Keating',
                        'username' => 'user4'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-2',
                        'documentaryTitle' => 'Documentary 2',
                        'documentarySummary' => 'Storyline'
                    ],
                    'user' => [
                        'name' => 'Anne Keating',
                        'username' => 'user4'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-3',
                        'documentaryTitle' => 'Documentary 3',
                        'documentarySummary' => 'Storyline'
                    ],
                    'user' => [
                        'name' => 'Anne Keating',
                        'username' => 'user4'
                    ]
                ],
                [
                    'type' => 'comment',
                    'data' => [
                        'commentText' => 'This is a comment 1',
                        'documentaryTitle' => 'Documentary 1',
                        'documentarySlug' => 'documentary-1',
                    ],
                    'user' => [
                        'name' => 'Jerry Carroll',
                        'username' => 'user5'
                    ]
                ],
                [
                    'type' => 'joined',
                    'data' => [],
                    'user' => [
                        'name' => 'Anne Keating',
                        'username' => 'user4'
                    ]
                ],
                [
                    'type' => 'joined',
                    'data' => [],
                    'user' => [
                        'name' => 'Jerry Carroll',
                        'username' => 'user5'
                    ]
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listActivityWithTypeAsGuest(ApiTester $I)
    {
        $I->sendGET('api/v1/community?type=' . \App\Enum\ActivityType::WATCHLIST);

        $expectedResponse = 'Not Authorized to view types';
        $I->seeResponseContains($expectedResponse);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
    }

    public function listActivityWithTypeAsAdmin(ApiTester $I)
    {
        $username = 'user1';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => 'password'
        ];
        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('access_token');
        $I->seeResponseContains('expires_in');
        $I->seeResponseContains('token_type');
        $I->seeResponseContains('scope');
        $I->seeResponseContains('refresh_token');

        $response = json_decode($I->grabResponse(), true);
        $accessToken = $response['access_token'];
        $I->amBearerAuthenticated($accessToken);

        $I->sendGET('api/v1/community?type=' . \App\Enum\ActivityType::WATCHLIST);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'items' => [
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-1',
                        'documentaryTitle' => 'Documentary 1',
                        'documentarySummary' => 'Storyline'
                    ],
                    'user' => [
                        'name' => 'John Smith',
                        'username' => 'user1'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-2',
                        'documentaryTitle' => 'Documentary 2',
                        'documentarySummary' => 'Storyline'
                    ],
                    'user' => [
                        'name' => 'John Smith',
                        'username' => 'user1'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-3',
                        'documentaryTitle' => 'Documentary 3',
                        'documentarySummary' => 'Storyline'
                    ],
                    'user' => [
                        'name' => 'John Smith',
                        'username' => 'user1'
                    ]
                ],
                [

                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-1',
                        'documentaryTitle' => 'Documentary 1',
                        'documentarySummary' => 'Storyline'
                    ],
                    'user' => [
                        'name' => 'Anne Keating',
                        'username' => 'user4'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-2',
                        'documentaryTitle' => 'Documentary 2',
                        'documentarySummary' => 'Storyline'
                    ],
                    'user' => [
                        'name' => 'Anne Keating',
                        'username' => 'user4'
                    ]
                ],
                [
                    'type' => 'watchlist',
                    'data' => [
                        'documentarySlug' => 'documentary-3',
                        'documentaryTitle' => 'Documentary 3',
                        'documentarySummary' => 'Storyline'
                    ],
                    'user' => [
                        'name' => 'Anne Keating',
                        'username' => 'user4'
                    ]
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listActivityWithNonExistingStatusAsAdmin(ApiTester $I)
    {
        $username = 'user1';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => 'password'
        ];
        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('access_token');
        $I->seeResponseContains('expires_in');
        $I->seeResponseContains('token_type');
        $I->seeResponseContains('scope');
        $I->seeResponseContains('refresh_token');

        $response = json_decode($I->grabResponse(), true);
        $accessToken = $response['access_token'];
        $I->amBearerAuthenticated($accessToken);

        $I->sendGET('api/v1/community?type=xxxxxxxxx');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('Type xxxxxxxxx does not exist');
    }

}
