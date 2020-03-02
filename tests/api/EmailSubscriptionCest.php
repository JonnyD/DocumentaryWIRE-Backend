<?php 

class EmailSubscriptionCest
{
    public function _before(ApiTester $I)
    {
    }

    public function listEmailSubscriptionsAsGuest(ApiTester $I)
    {
        $I->sendGET('api/v1/email');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseContainsJson();
        $expectedResponse = [
            'error' => 'access_denied',
            'error_description' => 'OAuth2 authentication required'
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listEmailSubscriptions(ApiTester $I)
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

        $I->sendGET('api/v1/email');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'items' => [
                [
                    'email' => 'user1@email.com',
                    'subscribed' => \App\Enum\YesNo::YES,
                    'subscriptionKey' => '1',
                ],
                [
                    'email' => 'user2@email.com',
                    'subscribed' => \App\Enum\YesNo::YES,
                    'subscriptionKey' => '2',
                ],
                [
                    'email' => 'user3@email.com',
                    'subscribed' => \App\Enum\YesNo::NO,
                    'subscriptionKey' => '3',
                ],
                [
                    'email' => 'user4@email.com',
                    'subscribed' => \App\Enum\YesNo::NO,
                    'subscriptionKey' => '4',
                ],
                [
                    'email' => '1xxx@xxx.com',
                    'subscribed' => \App\Enum\YesNo::YES,
                    'subscriptionKey' => '5',
                ],
                [
                    'email' => '2xxx@xxx.com',
                    'subscribed' => \App\Enum\YesNo::YES,
                    'subscriptionKey' => '6',
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listEmailSubscriptionsThatAreSubscribed(ApiTester $I)
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

        $I->sendGET('api/v1/email?subscribed=yes');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'items' => [
                [
                    'email' => 'user1@email.com',
                    'subscribed' => \App\Enum\YesNo::YES,
                    'subscriptionKey' => '1',
                ],
                [
                    'email' => 'user2@email.com',
                    'subscribed' => \App\Enum\YesNo::YES,
                    'subscriptionKey' => '2',
                ],
                [
                    'email' => '1xxx@xxx.com',
                    'subscribed' => \App\Enum\YesNo::YES,
                    'subscriptionKey' => '5',
                ],
                [
                    'email' => '2xxx@xxx.com',
                    'subscribed' => \App\Enum\YesNo::YES,
                    'subscriptionKey' => '6',
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listEmailSubscriptionsThatAreNotSubscribed(ApiTester $I)
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

        $I->sendGET('api/v1/email?subscribed=no');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'items' => [
                [
                    'email' => 'user3@email.com',
                    'subscribed' => \App\Enum\YesNo::NO,
                    'subscriptionKey' => '3'
                ],
                [
                    'email' => 'user4@email.com',
                    'subscribed' => \App\Enum\YesNo::NO,
                    'subscriptionKey' => '4'
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listEmailsByEmailAddress(ApiTester $I)
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

        $I->sendGET('api/v1/email?subscribed=no&email=user3@email.com');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'items' => [
                [
                    'email' => 'user3@email.com',
                    'subscribed' => \App\Enum\YesNo::NO,
                    'subscriptionKey' => '3'
                ]
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function getEmailById(ApiTester $I)
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

        /** @var \App\Entity\Email $emailSubscription */
        $emailSubscription = $I->grabEntityFromRepository(\App\Entity\Email::class, [
            'email' => 'user1@email.com'
        ]);
        $I->sendGET('api/v1/email/' . $emailSubscription->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'email' => 'user1@email.com',
            'subscribed' => \App\Enum\YesNo::YES,
            'subscriptionKey' => '1'
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function getEmailByFakeId(ApiTester $I)
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

        $I->sendGET('api/v1/email/999999999');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);

        $I->seeResponseContains('Email not found');
    }

    public function createEmail(ApiTester $I)
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

        $data = [
            'email' => 'test@test.com',
            'subscribed' => \App\Enum\YesNo::YES
        ];
        $I->sendPOST('api/v1/email', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'email' => 'test@test.com',
            'subscribed' => \App\Enum\YesNo::YES,
        ];
        $I->seeResponseContainsJson($expectedResponse);
        $subscriptionKey = json_decode($I->grabResponse(), true)['subscriptionKey'];
        $I->assertNotNull($subscriptionKey);
    }

    public function createEmailWhereEmailAlreadyExists(ApiTester $I)
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

        $data = [
            'email' => 'user1@email.com',
            'subscribed' => \App\Enum\YesNo::YES
        ];
        $I->sendPOST('api/v1/email', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Email already exists');
    }

    public function editEmailFakeId(ApiTester $I)
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

        $data = [
            'email' => 'user1@email.com',
            'subscribed' => \App\Enum\YesNo::YES
        ];
        $I->sendPATCH('api/v1/email/99999999999', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('Email does not exist');
    }

    public function editEmail(ApiTester $I)
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

        /** @var \App\Entity\Email $emailSubscription */
        $emailSubscription = $I->grabEntityFromRepository(\App\Entity\Email::class, [
            'email' => 'user1@email.com'
        ]);

        $data = [
            'email' => 'user1@email.com',
            'subscribed' => \App\Enum\YesNo::NO
        ];
        $I->sendPATCH('api/v1/email/' . $emailSubscription->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'email' => 'user1@email.com',
            'subscribed' => \App\Enum\YesNo::NO,
            'subscriptionKey' => '1'
        ];
        $I->seeResponseContainsJson($expectedResponse);

        $data = [
            'email' => 'user1@email.com',
            'subscribed' => \App\Enum\YesNo::YES
        ];
        $I->sendPATCH('api/v1/email/' . $emailSubscription->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }

    public function unsubscribe(ApiTester $I)
    {
        $I->sendGET('api/v1/email/unsubscribe?email=user1@email.com&subscription_key=1');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContains('Email Unsubscribed');
    }

    public function unsubscribeEmailNotFound(ApiTester $I)
    {
        $I->sendGET('api/v1/email/unsubscribe');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('Email not found');

        $I->sendGET('api/v1/email/unsubscribe?email=xxxxxxxxxx');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('Email not found');
    }

    public function unsubscribeSubscriptionKeyNotFound(ApiTester $I)
    {
        $I->sendGET('api/v1/email/unsubscribe?email=user1@email.com');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('Subscription key not found');

        $I->sendGET('api/v1/email/unsubscribe?email=user1@email.com&subscription_key=xxxxxxxxx');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('Subscription key not found');
    }
}
