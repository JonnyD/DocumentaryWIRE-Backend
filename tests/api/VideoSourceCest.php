<?php 

class VideoSourceCest
{
    public function _before(ApiTester $I)
    {
    }

    public function getVideoSourcesAsGuest(ApiTester $I)
    {
        $I->sendGET('api/v1/video-source');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'name' => 'Youtube',
            'embedAllowed' => 'no',
            'status' => 'enabled'
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function getVideoSourcesAsAdmin(ApiTester $I)
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

        $I->sendGET('api/v1/video-source');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            [
                'name' => '56',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Blip',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Daily Motion',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Disclose',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Forum Network',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Krishnatube',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'megavideo',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'MySpace',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Novamov',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'PBS',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Rutube',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Snagfilms',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'stagevu',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Tudou',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Veoh',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Viddler',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Vimeo',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'youtube-playlist',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ],
            [
                'name' => 'Youtube',
                'embedAllowed' => 'no',
                'status' => 'enabled'
            ],
            [
                'name' => 'ZShare',
                'embedAllowed' => 'no',
                'status' => 'disabled'
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function getWrongVideoSourceAsGuest(ApiTester $I)
    {
        $I->sendGET('api/v1/video-source/9999999999999');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('Video source not found');
    }

    public function getVideoSourceThatIsEnabledAsGuest(ApiTester $I)
    {
        /** @var \App\Entity\VideoSource $videoSource */
        $videoSource = $I->grabEntityFromRepository(\App\Entity\VideoSource::class, [
            'name' => 'Youtube'
        ]);

        $I->sendGET('api/v1/video-source/' . $videoSource->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'name' => 'Youtube',
            'embedAllowed' => 'no',
            'status' => 'enabled'
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function getVideoSourceThatIsEnabledAsAdmin(ApiTester $I)
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

        /** @var \App\Entity\VideoSource $videoSource */
        $videoSource = $I->grabEntityFromRepository(\App\Entity\VideoSource::class, [
            'name' => 'Vimeo'
        ]);

        $I->sendGET('api/v1/video-source/' . $videoSource->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseContains('Not authorized');
    }

    public function editVideoSourceAsGuest(ApiTester $I)
    {
        /** @var \App\Entity\VideoSource $videoSource */
        $videoSource = $I->grabEntityFromRepository(\App\Entity\VideoSource::class, [
            'name' => 'Youtube'
        ]);

        $I->sendPATCH('api/v1/video-source/' . $videoSource->getId());
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $expectedResponse = [
            'error' => 'access_denied',
            'error_description' => 'OAuth2 authentication required'
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function editVideoSourceWithVideoSourceThatDoesNotExistAsAdmin(ApiTester $I)
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

        $I->sendPATCH('api/v1/video-source/9999999999');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('Video source not found');
    }

    public function editVideoSourcesAsAdmin(ApiTester $I)
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

        /** @var \App\Entity\VideoSource $videoSource */
        $videoSource = $I->grabEntityFromRepository(\App\Entity\VideoSource::class, [
            'name' => 'Youtube'
        ]);

        $data = [
            'name' => 'Youtube',
            'embedAllowed' => 'no',
            'status' => \App\Enum\VideoSourceStatus::DISABLED
        ];
        $I->sendPATCH('api/v1/video-source/' . $videoSource->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            'name' => 'Youtube',
            'embedAllowed' => 'no',
            'status' => \App\Enum\VideoSourceStatus::DISABLED
        ];
        $I->seeResponseContainsJson($expectedResponse);

        $data = [
            'name' => 'Youtube',
            'embedAllowed' => 'no',
            'status' => \App\Enum\VideoSourceStatus::ENABLED
        ];
        $I->sendPATCH('api/v1/video-source/' . $videoSource->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

    }
}
