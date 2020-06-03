<?php 

class FollowCest
{
    public function _before(ApiTester $I)
    {
    }

    public function getFollowDoesNotExistAsGuest(ApiTester $I)
    {
        $I->sendGET('api/v1/follow/9999999');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('Follow not found');
    }

    public function getFollowAsGuest(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;
        $followClass = \App\Entity\Follow::class;

        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);
        /** @var \App\Entity\User $userFrom */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user2'
        ]);

        /** @var \App\Entity\Follow $follow */
        $follow = $I->grabEntityFromRepository($followClass, [
            'userFrom' => $userFrom,
            'userTo' => $userTo
        ]);

        $I->sendGET('api/v1/follow/' . $follow->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseContains('Not authorized');
    }

    public function getFollowAsOwner(ApiTester $I)
    {
        $username = 'user2';
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

        $userClass = \App\Entity\User::class;
        $followClass = \App\Entity\Follow::class;

        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => $username
        ]);
        /** @var \App\Entity\User $userFrom */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);

        /** @var \App\Entity\Follow $follow */
        $follow = $I->grabEntityFromRepository($followClass, [
            'userFrom' => $userFrom,
            'userTo' => $userTo
        ]);

        $I->sendGET('api/v1/follow/' . $follow->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'userFrom' => [
                'username' => 'user2'
            ],
            'userTo' => [
                'username' => 'user1'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }


    public function getFollowAsAdmin(ApiTester $I)
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

        $userClass = \App\Entity\User::class;
        $followClass = \App\Entity\Follow::class;

        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => 'user2'
        ]);
        /** @var \App\Entity\User $userFrom */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);

        $I->assertEquals('user2', $userFrom->getUsername());
        $I->assertEquals('user1', $userTo->getUsername());

        /** @var \App\Entity\Follow $follow */
        $follow = $I->grabEntityFromRepository($followClass, [
            'userFrom' => $userFrom,
            'userTo' => $userTo
        ]);

        $I->sendGET('api/v1/follow/' . $follow->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'userFrom' => [
                'username' => 'user2'
            ],
            'userTo' => [
                'username' => 'user1'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function createFollowAsGuest(ApiTester $I)
    {
        $data = [];

        $I->sendPOST('api/v1/follow', $data);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);

        $expectedResponse = [
            'error' => 'access_denied',
            'error_description' => 'OAuth2 authentication required'
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function createFollowAsOwner(ApiTester $I)
    {
        $username = 'user2';
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

        $userClass = \App\Entity\User::class;
        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => $username
        ]);
        /** @var \App\Entity\User $userTo */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user5'
        ]);

        $data = [
            'userFrom' => $userFrom->getId(),
            'userTo' => $userTo->getId()
        ];

        $I->sendPOST('api/v1/follow', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'userFrom' => [
                'username' => 'user2'
            ],
            'userTo' => [
                'username' => 'user5'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function createFollowAsNotOwner(ApiTester $I)
    {
        $username = 'user2';
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

        $userClass = \App\Entity\User::class;
        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => 'user5'
        ]);
        /** @var \App\Entity\User $userTo */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);

        $data = [
            'userFrom' => $userFrom->getId(),
            'userTo' => $userTo->getId()
        ];

        $I->sendPOST('api/v1/follow', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContains('Unauthorized');
    }

    public function createFollowAsAdmin(ApiTester $I)
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

        $userClass = \App\Entity\User::class;
        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => 'user6'
        ]);
        /** @var \App\Entity\User $userTo */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);

        $data = [
            'userFrom' => $userFrom->getId(),
            'userTo' => $userTo->getId()
        ];

        $I->sendPOST('api/v1/follow', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'userFrom' => [
                'username' => 'user6'
            ],
            'userTo' => [
                'username' => 'user1'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listFollowsAsGuest(ApiTester $I)
    {
        $I->sendGET('api/v1/follow');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseContains('You must set either a User From or User To');
    }

    public function listFollowWithUserFromId(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;
        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);

        $I->sendGET('api/v1/follow?from=' . $userFrom->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'items' => [
                [
                    'userFrom' => [
                        'username' => 'user1'
                    ],
                    'userTo' => [
                        'username' => 'user2'
                    ]
                ],
                [
                    'userFrom' => [
                        'username' => 'user1'
                    ],
                    'userTo' => [
                        'username' => 'user3'
                    ]
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listFollowWithUserToId(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;
        /** @var \App\Entity\User $userFrom */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user2'
        ]);

        $I->sendGET('api/v1/follow?to=' . $userTo->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'items' => [
                [
                    'userFrom' => [
                        'username' => 'user1'
                    ],
                    'userTo' => [
                        'username' => 'user2'
                    ]
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listFollowWithUserFromAndUserToId(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;
        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);
        /** @var \App\Entity\User $userFrom */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user2'
        ]);

        $I->sendGET('api/v1/follow?from=' . $userFrom->getId() . '&to=' . $userTo->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'items' => [
                [
                    'userFrom' => [
                        'username' => 'user1'
                    ],
                    'userTo' => [
                        'username' => 'user2'
                    ]
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listFollowsAsAdmin(ApiTester $I)
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

        $I->sendGET('api/v1/follow');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'items' => [
                [
                    'userFrom' => [
                        'username' => 'user1'
                    ],
                    'userTo' => [
                        'username' => 'user2'
                    ]
                ],
                [
                    'userFrom' => [
                        'username' => 'user1'
                    ],
                    'userTo' => [
                        'username' => 'user3'
                    ]
                ],
                [
                    'userFrom' => [
                        'username' => 'user2'
                    ],
                    'userTo' => [
                        'username' => 'user1'
                    ]
                ],
                [
                    'userFrom' => [
                        'username' => 'user3'
                    ],
                    'userTo' => [
                        'username' => 'user4'
                    ]
                ],
                [
                    'userFrom' => [
                        'username' => 'user2'
                    ],
                    'userTo' => [
                        'username' => 'user5'
                    ]
                ],
                [
                    'userFrom' => [
                        'username' => 'user6'
                    ],
                    'userTo' => [
                        'username' => 'user1'
                    ]
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function removeFollowAsGuest(ApiTester $I)
    {
        $I->sendDELETE('api/v1/follow/1');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $expectedResponse = [
            'error' => 'access_denied',
            'error_description' => 'OAuth2 authentication required'
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function removeFollowWhichDoesNotExistAsLoggedInUser(ApiTester $I)
    {
        $username = 'user2';
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

        //@TODO check first is owner

        $I->sendDELETE('api/v1/follow/999999999');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('Follow does not exist');
    }

    public function removeFollowWithWrongUser(ApiTester $I)
    {
        $username = 'user2';
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

        $userClass = \App\Entity\User::class;
        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);
        /** @var \App\Entity\User $userTo */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user3'
        ]);

        $followClass = \App\Entity\Follow::class;
        /** @var \App\Entity\Follow $follow */
        $follow = $I->grabEntityFromRepository($followClass, [
            'userFrom' => $userFrom,
            'userTo' => $userTo
        ]);

        $I->sendDELETE('api/v1/follow/' . $follow->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseContains('You are not allowed to change this follow');
    }

    public function removeFollowWithCorrectUser(ApiTester $I)
    {
        $username = 'user2';
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

        $userClass = \App\Entity\User::class;
        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => 'user2'
        ]);
        /** @var \App\Entity\User $userTo */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);

        $followClass = \App\Entity\Follow::class;
        /** @var \App\Entity\Follow $follow */
        $follow = $I->grabEntityFromRepository($followClass, [
            'userFrom' => $userFrom,
            'userTo' => $userTo
        ]);

        $I->sendDELETE('api/v1/follow/' . $follow->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContains('Deleted');

        $data = [
            'userFrom' => $userFrom->getId(),
            'userTo' => $userTo->getId()
        ];
        $I->sendPOST('api/v1/follow', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'userFrom' => [
                'username' => 'user2'
            ],
            'userTo' => [
                'username' => 'user1'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function removeFollowWithWrongUserAsAdmin(ApiTester $I)
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

        $userClass = \App\Entity\User::class;
        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => 'user2'
        ]);
        /** @var \App\Entity\User $userTo */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);

        $followClass = \App\Entity\Follow::class;
        /** @var \App\Entity\Follow $follow */
        $follow = $I->grabEntityFromRepository($followClass, [
            'userFrom' => $userFrom,
            'userTo' => $userTo
        ]);

        $I->sendDELETE('api/v1/follow/' . $follow->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContains('Deleted');

        $data = [
            'userFrom' => $userFrom->getId(),
            'userTo' => $userTo->getId()
        ];
        $I->sendPOST('api/v1/follow', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'userFrom' => [
                'username' => 'user2'
            ],
            'userTo' => [
                'username' => 'user1'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }
}
