<?php 

class SubscriptionCest
{
    public function _before(ApiTester $I)
    {
    }

    public function getSubscriptionSubscriptionDoesNotExistAsGuest(ApiTester $I)
    {
        $I->sendGET('api/v1/subscription/9999999');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('Subscription not found');
    }

    public function getSubscriptionAsGuest(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;
        $subscriptionClass = \App\Entity\Subscription::class;

        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);
        /** @var \App\Entity\User $userFrom */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user2'
        ]);

        /** @var \App\Entity\Subscription $subscription */
        $subscription = $I->grabEntityFromRepository($subscriptionClass, [
            'userFrom' => $userFrom,
            'userTo' => $userTo
        ]);

        $I->sendGET('api/v1/subscription/' . $subscription->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseContains('Not authorized');
    }

    public function getSubscriptionAsOwner(ApiTester $I)
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
        $subscriptionClass = \App\Entity\Subscription::class;

        /** @var \App\Entity\User $userFrom */
        $userFrom = $I->grabEntityFromRepository($userClass, [
            'username' => $username
        ]);
        /** @var \App\Entity\User $userFrom */
        $userTo = $I->grabEntityFromRepository($userClass, [
            'username' => 'user1'
        ]);

        /** @var \App\Entity\Subscription $subscription */
        $subscription = $I->grabEntityFromRepository($subscriptionClass, [
            'userFrom' => $userFrom,
            'userTo' => $userTo
        ]);

        $I->sendGET('api/v1/subscription/' . $subscription->getId());
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


    public function getSubscriptionAsAdmin(ApiTester $I)
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
        $subscriptionClass = \App\Entity\Subscription::class;

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

        /** @var \App\Entity\Subscription $subscription */
        $subscription = $I->grabEntityFromRepository($subscriptionClass, [
            'userFrom' => $userFrom,
            'userTo' => $userTo
        ]);

        $I->sendGET('api/v1/subscription/' . $subscription->getId());
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

    public function createSubscriptionAsGuest(ApiTester $I)
    {
        $data = [];

        $I->sendPOST('api/v1/subscription', $data);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);

        $expectedResponse = [
            'error' => 'access_denied',
            'error_description' => 'OAuth2 authentication required'
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function createSubscriptionAsOwner(ApiTester $I)
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

        $I->sendPOST('api/v1/subscription', json_encode($data));
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

    public function createSubscriptionAsNotOwner(ApiTester $I)
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

        $I->sendPOST('api/v1/subscription', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContains('Unauthorized');
    }

    public function createSubscriptionAsAdmin(ApiTester $I)
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

        $I->sendPOST('api/v1/subscription', json_encode($data));
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
}
