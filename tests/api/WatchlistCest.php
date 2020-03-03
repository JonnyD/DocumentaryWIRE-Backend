<?php

class WatchlistCest
{
    public function _before(ApiTester $I)
    {
    }

    public function listWatchlists(ApiTester $I)
    {
        $I->sendGET('api/v1/watchlist');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseContains('Not authorized');
    }

    public function listWatchlistsAsAdmin(ApiTester $I)
    {
        $username = 'user1';
        $password = 'password';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => $password
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

        $I->sendGET('api/v1/watchlist');

        $expectedResponse = [
            'items' => [
                [
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith'
                    ],
                    'documentary' => [
                        'title' => 'Documentary 1',
                        'slug' => 'documentary-1',
                        'summary' => 'Storyline'
                    ]
                ],
                [
                    'user' => [
                        'username' => 'user1',
                        'name' => 'John Smith'
                    ],
                    'documentary' => [
                        'title' => 'Documentary 2',
                        'slug' => 'documentary-2',
                        'summary' => 'Storyline'
                    ]
                ],
                [
                    'user' => [
                        'username' => 'user2',
                        'name' => 'Sarah McCarthy'
                    ],
                    'documentary' => [
                        'title' => 'Documentary 1',
                        'slug' => 'documentary-1',
                        'summary' => 'Storyline'
                    ]
                ],
                [
                    'user' => [
                        'username' => 'user3',
                        'name' => 'Andrew Walsh'
                    ],
                    'documentary' => [
                        'title' => 'Documentary 4',
                        'slug' => 'documentary-4',
                        'summary' => 'Storyline'
                    ]
                ],
                [
                    'user' => [
                        'username' => 'user3',
                        'name' => 'Andrew Walsh'
                    ],
                    'documentary' => [
                        'title' => 'Documentary 1',
                        'slug' => 'documentary-1',
                        'summary' => 'Storyline'
                    ]
                ],
                [
                    'user' => [
                        'username' => 'user4',
                        'name' => 'Anne Keating'
                    ],
                    'documentary' => [
                        'title' => 'Documentary 3',
                        'slug' => 'documentary-3',
                        'summary' => 'Storyline'
                    ]
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listWatchlistsForUserAsAdmin(ApiTester $I)
    {
        $username = 'user1';
        $password = 'password';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => $password
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

        $I->sendGET('api/v1/watchlist?user=user3');

        $expectedResponse = [
            'items' => [
                [
                    'user' => [
                        'username' => 'user3',
                        'name' => 'Andrew Walsh'
                    ],
                    'documentary' => [
                        'title' => 'Documentary 4',
                        'slug' => 'documentary-4',
                        'summary' => 'Storyline'
                    ]
                ],
                [
                    'user' => [
                        'username' => 'user3',
                        'name' => 'Andrew Walsh'
                    ],
                    'documentary' => [
                        'title' => 'Documentary 1',
                        'slug' => 'documentary-1',
                        'summary' => 'Storyline'
                    ]
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listUsersWatchlists(ApiTester $I)
    {
        $username = 'user3';
        $password = 'password';

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => $password
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

        $I->sendGET('api/v1/watchlist');

        $expectedResponse = [
            'items' => [
                [
                    'user' => [
                        'username' => 'user3',
                        'name' => 'Andrew Walsh'
                    ],
                    'documentary' => [
                        'title' => 'Documentary 4',
                        'slug' => 'documentary-4',
                        'summary' => 'Storyline'
                    ]
                ],
                [
                    'user' => [
                        'username' => 'user3',
                        'name' => 'Andrew Walsh'
                    ],
                    'documentary' => [
                        'title' => 'Documentary 1',
                        'slug' => 'documentary-1',
                        'summary' => 'Storyline'
                    ]
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }
}
