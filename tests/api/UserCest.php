<?php

class UserCest
{
    public function _before(ApiTester $I)
    {
    }

    public function registerUser(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;

        $registerUser = [
            'username' => 'codeception_username',
            'email' => 'codeception_email@jonnydevine.com',
            'password' => 'codeception',
            'name' => 'Codeception Name'
        ];

        $I->dontSeeInRepository($userClass, $registerUser);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/api/v1/user', $registerUser);

        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $expectedResponse = [
            'username' => $registerUser['username']
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function registerExistingUser(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;

        $registerUser = [
            'username' => 'codeception_username',
            'email' => 'codeception_email@jonnydevine.com',
            'name' => 'Codeception Name'
        ];

        $I->seeInRepository($userClass, $registerUser);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/api/v1/user', $registerUser);

        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();

        $I->seeResponseContains("USERNAME_IS_ALREADY_IN_USE");
        $I->seeResponseContains("EMAIL_IS_ALREADY_IN_USE");
    }

    public function registerButAlreadyLoggedIn(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;
        $clientClass = \App\Entity\Client::class;

        $username = 'user2';
        $password = 'password';

        $I->seeInRepository($userClass, [
            'username' => $username,
            'enabled' => true
        ]);

        $I->seeInRepository($clientClass, [
            'randomId' => '5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s'
        ]);

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => $password
        ];
        $I->sendPOST('/oauth/v2/token', $logInDetails);
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

        $registerUser = [
            'username' => 'codeception_username',
            'email' => 'codeception_email@jonnydevine.com',
            'name' => 'Codeception Name'
        ];

        $I->sendPOST('/api/v1/user', $registerUser);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();

        $I->seeResponseContains("Already Logged In");
    }

    public function registerInvalidEmail(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;

        $registerUser = [
            'username' => 'codeception_username_2',
            'email' => 'codeception_email',
            'password' => 'codeception_2',
            'name' => 'Codeception Name 2'
        ];

        $I->dontSeeInRepository($userClass, $registerUser);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/api/v1/user', $registerUser);

        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();

        $I->seeResponseContains("Not an email address");
    }

    public function meLoggedInDisabled(ApiTester $I)
    {
        $username = 'user4';
        $password = 'password';

        $I->seeInRepository(\App\Entity\User::class, [
            'username' => $username,
            'enabled' => false
        ]);

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

        $resposne = json_decode($I->grabResponse(), true);
        $accessToken = $resposne['access_token'];
        $I->amBearerAuthenticated($accessToken);
        $I->sendGet('/api/v1/user/me');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $expectedResponse = [
            'error' => 'access_denied',
            'error_description' => 'User account is disabled.'
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function meLoggedInEnabled(ApiTester $I)
    {
        $username = 'user19';
        $password = 'password';

        $I->seeInRepository(\App\Entity\User::class, [
            'username' => $username,
            'enabled' => true
        ]);

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
        $I->haveHttpHeader('Authorization', 'Bearer ' . $accessToken);
        $I->sendGet('api/v1/user/me?disableLastLogin=' . true);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
    }

    public function meLoggedOut(ApiTester $I)
    {
        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => 'xxxxxxxx',
            'password' => 'xxxxxxxx'
        ];

        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();
        $expectedResponse = [
            'error' => 'invalid_grant',
            'error_description' => 'Invalid username and password combination'
        ];
        $I->seeResponseContainsJson($expectedResponse);

        $I->sendGET('/api/v1/user/me');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $expectedResponse = [
            'error' => 'access_denied',
            'error_description' => 'OAuth2 authentication required'
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function confirmInvalidUsernameQueryParam(ApiTester $I)
    {
        $username = '';
        $confirmationToken = '123';
        $I->sendGet('api/v1/user/confirm?username='.$username.'&confirmation_token='.$confirmationToken);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContains('Username not found');
    }

    public function confirmInvalidConfirmationTokenQueryParam(ApiTester $I)
    {
        $username = 'user1';
        $confirmationToken = '';
        $I->sendGet('api/v1/user/confirm?username='.$username.'&confirmation_token='.$confirmationToken);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContains('Confirmation Token not found');
    }

    public function confirmUserNotInDatabase(ApiTester $I)
    {
        $username = 'xxxxx';
        $confirmationToken = 'xxxx';

        $I->dontSeeInRepository(\App\Entity\User::class, [
            'username' => $username
        ]);

        $I->sendGet('api/v1/user/confirm?username='.$username.'&confirmation_token='.$confirmationToken);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContains('User not found');
    }

    public function confirmIsUserAlreadyActivated(ApiTester $I)
    {
        $username = 'user1';
        $confirmationToken = 'xxxx';

        $I->seeInRepository(\App\Entity\User::class, [
            'username'  => $username
        ]);

        $I->sendGet('api/v1/user/confirm?username='.$username.'&confirmation_token='.$confirmationToken);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('Already confirmed');
    }

    public function confirmInvalidConfirmationToken(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;

        $username = 'user4';
        $confirmationToken = 'xxxx';

        $I->seeInRepository($userClass, [
            'username'  => $username
        ]);

        $I->sendGet('api/v1/user/confirm?username='.$username.'&confirmation_token='.$confirmationToken);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('Confirmation Token cant be found');
    }

    public function confirmAlreadyLoggedIn(ApiTester $I)
    {
        $userClass = \App\Entity\User::class;

        $username = 'user1';
        $password = 'password';
        $confirmationToken = '4c3fb568d51feb12a0038033890efb5367585af3a';

        $I->seeInRepository($userClass, [
            'username'  => $username
        ]);

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

        $I->sendGet('api/v1/user/confirm?username='.$username.'&confirmation_token='.$confirmationToken);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContains('Already confirmed');
    }

    public function confirmSuccessfully(ApiTester $I)
    {
        $userClass = App\Entity\User::class;

        $username = 'user5';
        $confirmationToken = '5c3fb568d51feb12a0038033890efb5367585af3a';

        $data = [
            'username' => $username,
            'confirmationToken' => $confirmationToken,
            'activatedAt' => null,
            'enabled' => false
        ];
        $I->seeInRepository($userClass, $data);

        $I->sendGet('api/v1/user/confirm?username='
            .$username.'&confirmation_token='.$confirmationToken.'&disableActivation=true');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContains('Successfully confirmed');
    }

    public function resendNoEmail(ApiTester $I)
    {
        $email = null;

        $I->sendGet('api/v1/user/resend');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('Email not entered');
    }

    public function resendNonExistingUser(ApiTester $I)
    {
        $email = 'xxxxxxxx';

        $I->sendGet('api/v1/user/resend?email='.$email);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('User not found');
    }

    public function resendValidEmailNotActivated(ApiTester $I)
    {
        $email = 'user6@email.com';

        $I->sendGet('api/v1/user/resend?email='.$email);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContains('We have resent a new confirmation email');
    }

    public function resendValidEmailActivated(ApiTester $I)
    {
        $email = 'user1@email.com';

        $I->sendGet('api/v1/user/resend?email='.$email);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContains('Already confirmed');
    }

    public function resetPasswordInvalidResetKeyParam(ApiTester $I)
    {
        $I->sendPOST('api/v1/user/reset-password', []);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Reset key not found');
    }

    public function resetPasswordInvalidUsernameParam(ApiTester $I)
    {
        $data = [
            "reset_key" => "xxxxxxxxxxx"
        ];

        $I->sendPOST('api/v1/user/reset-password', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Username not found');
    }

    public function resetPasswordInvalidPasswordParam(ApiTester $I)
    {
        $data = [
            "reset_key" => "xxxxxxxxxxx",
            "username" => "xxxxxxxxxx"
        ];

        $I->sendPOST('api/v1/user/reset-password', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Password not found');
    }

    public function resetPasswordUserDoesNotExist(ApiTester $I)
    {
        $data = [
            "reset_key" => "xxxxxxxxxxx",
            "username" => "xxxxxxxxxx",
            "password" => "xxxxxxxxxx"
        ];

        $I->sendPOST('api/v1/user/reset-password', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
        $I->seeResponseContains('User does not exist');
    }

    public function resetPasswordInvalidResetKey(ApiTester $I)
    {
        $data = [
            "reset_key" => "xxxxxxxxxxx",
            "username" => "user4",
            "password" => "xxxxxxxxxx"
        ];

        $I->sendPOST('api/v1/user/reset-password', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
        $I->seeResponseContains('Reset key does not exist');
    }

    public function resetPasswordIsRequestExpired(ApiTester $I)
    {
        $data = [
            "reset_key" => "4c3fb568d51feb12a0038033890efb5367585af3a",
            "username" => "user4",
            "password" => "xxxxxxxxxx"
        ];

        $I->sendPOST('api/v1/user/reset-password', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
        $I->seeResponseContains('Reset key expired');
    }

    public function resetPasswordSuccessfully(ApiTester $I)
    {
        $data = [
            "reset_key" => "5c3fb568d51feb12a0038033890efb5367585af3a",
            "username" => "user5",
            "password" => "xxxxxxxxxx"
        ];

        $I->sendPOST('api/v1/user/reset-password', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContains('New password set');
    }

    public function forgotUsernameNoEmailParam(ApiTester $I)
    {
        $I->sendPost('api/v1/user/forgot-username', []);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Email not found');
    }

    public function forgotUsernameUserNotFound(ApiTester $I)
    {
        $data = [
            'email' => 'xxxxxx'
        ];

        $I->sendPost('api/v1/user/forgot-username', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContains('User not found');
    }

    public function forgotUsernameSuccessful(ApiTester $I)
    {
        $data = [
            'email' => 'user1@email.com'
        ];

        $I->sendPost('api/v1/user/forgot-username', json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContains('Email has been sent');
    }

    public function getUserInvalidUsername(ApiTester $I)
    {
        $username = 'xxxxxxxxxx';

        $I->sendGET('api/v1/user/' . $username);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->seeResponseContains('User cannot be found');
    }

    public function getUserSuccessful(ApiTester $I)
    {
        $username = 'user1';

        $I->sendGET('api/v1/user/' . $username);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            "name" => "John Smith",
            "username" => "user1",
            "avatar" => "http://localhost:8000/uploads/avatar/0d91cca62a1a31a612b2a6366c7ef56b3e468ce8.jpg",
            "roles" => [
                "ROLE_ADMIN",
                "ROLE_USER"
            ],
        ];

        $I->seeResponseContainsJson($expectedResponse);

        /** @var \App\Service\UserService $userService */
        $userService = $I->grabService('App\Service\UserService');
        $user = $userService->getUserByUsername($username);
        $I->assertNotNull($user->getCreatedAt());
    }

    public function editUserNotLoggedIn(ApiTester $I)
    {
        $userId = 1;
        $data = [];

        $I->sendPATCH('api/v1/user/' . $userId, $data);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $expectedResponse = [
            'error' => 'access_denied',
            'error_description' => 'OAuth2 authentication required'
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function editUserLoggedInButInvalidUser(ApiTester $I)
    {
        $userId = 999999;
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

        $data = [];
        $I->sendPATCH('api/v1/user/' . $userId, json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
        $I->seeResponseContains('User does not exist');
    }

    public function editUserButDifferentUser(ApiTester $I)
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

        /** @var \App\Service\UserService $userService */
        $userService = $I->grabService('App\Service\UserService');
        $userId = $userService->getUserByUsername("user1")->getId();

        $data = [];
        $I->sendPATCH('api/v1/user/' . $userId, json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
        $I->seeResponseContains('You are not allowed to edit a different user');
    }

    public function editUserButDifferentUsername(ApiTester $I)
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

        /** @var \App\Service\UserService $userService */
        $userService = $I->grabService('App\Service\UserService');
        $existingUser = $userService->getUserByUsername($username);

        $data = [
            'name' => $existingUser->getName(),
            'username' => 'user1'
        ];
        $I->sendPATCH('api/v1/user/' . $existingUser->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContains('USERNAME_IS_ALREADY_IN_USE');
    }

    public function editUserSuccessfully(ApiTester $I)
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

        /** @var \App\Service\UserService $userService */
        $userService = $I->grabService('App\Service\UserService');
        $existingUser = $userService->getUserByUsername($username);

        $data = [
            'name' => $existingUser->getName(),
            'username' => 'user99999'
        ];
        $I->sendPATCH('api/v1/user/' . $existingUser->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $expectedResponse = [
            "name" => "Sarah McCarthy",
            "username" => "user99999"
        ];
        $I->seeResponseContainsJson($expectedResponse);

        $data = [
            'name' => $existingUser->getName(),
            'username' => $username
        ];
        $I->sendPatch('api/v1/user/' . $existingUser->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $expectedResponse = [
            "name" => "Sarah McCarthy",
            "username" => "user2"
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function changePasswordNotLoggedIn(ApiTester $I)
    {
        $username = "user1";
        $data = [];

        /** @var \App\Service\UserService $userService */
        $userService = $I->grabService('App\Service\UserService');
        $user = $userService->getUserByUsername($username);

        $I->sendPOST('api/v1/user/' . $user->getId() . '/change-password', $data);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);

        $expectedResponse = [
            'error' => 'access_denied',
            'error_description' => 'OAuth2 authentication required'
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function changePasswordNotLoggedInUser(ApiTester $I)
    {
        $username = 'user3';
        $password = 'password';

        $otherUsername = 'user4';

        $data = [];

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

        /** @var \App\Service\UserService $userService */
        $userService = $I->grabService('App\Service\UserService');
        $otherUser = $userService->getUserByUsername($otherUsername);

        $I->assertEquals($otherUsername, $otherUser->getUsername());

        $I->sendPOST('api/v1/user/' . $otherUser->getId() . '/change-password', $data);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
        $I->seeResponseContains('You cannot change password of someone else');
    }

    public function changePasswordNewPasswordMustBeBetween6And40Chars(ApiTester $I)
    {
        $username = 'user1';
        $currentPassword = 'xxxxx';
        $newPassword = 'xxxxx';
        $confirmPassword = 'xxxxx';

        $data = [
            'currentPassword' => $currentPassword,
            'newPassword' => $newPassword,
            'confirmPassword' => $confirmPassword
        ];

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

        /** @var \App\Service\UserService $userService */
        $userService = $I->grabService('App\Service\UserService');
        $user = $userService->getUserByUsername($username);

        $I->sendPOST('api/v1/user/' . $user->getId() . '/change-password', $data);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContains('New Password must be between 6 and 40 characters');
    }

    public function changePasswordWrongCurrentPassword(ApiTester $I)
    {
        $username = 'user1';
        $currentPassword = 'xxxxxxxx';
        $newPassword = 'xxxxxxxx';
        $confirmPassword = 'xxxxxxxx';

        $data = [
            'currentPassword' => $currentPassword,
            'newPassword' => $newPassword,
            'confirmPassword' => $confirmPassword
        ];

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

        /** @var \App\Service\UserService $userService */
        $userService = $I->grabService('App\Service\UserService');
        $user = $userService->getUserByUsername($username);

        $I->sendPOST('api/v1/user/' . $user->getId() . '/change-password', $data);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Password does not match the one in your account');
    }

    public function changePasswordSuccessfully(ApiTester $I)
    {
        $username = 'user19';
        $currentPassword = 'password';
        $newPassword = 'newpassword';
        $confirmPassword = 'newpassword';

        $data = [
            'currentPassword' => $currentPassword,
            'newPassword' => $newPassword,
            'confirmPassword' => $confirmPassword
        ];

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

        /** @var \App\Service\UserService $userService */
        $userService = $I->grabService('App\Service\UserService');
        $user = $userService->getUserByUsername($username);

        $I->sendPOST('api/v1/user/' . $user->getId() . '/change-password', $data);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        $expectedResponse = [
            "name" => "Kathleen Sims",
            "username" => "user19"
        ];
        $I->seeResponseContainsJson($expectedResponse);

        $logInDetails = [
            'grant_type' => 'password',
            'client_id' => '1_5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s',
            'client_secret' => 'sdgggskokererg4232404gc4csdgfdsgf8s8ck5s',
            'username' => $username,
            'password' => $newPassword
        ];
        $I->sendPOST('oauth/v2/token', $logInDetails);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContains('access_token');
        $I->seeResponseContains('expires_in');
        $I->seeResponseContains('token_type');
        $I->seeResponseContains('scope');
        $I->seeResponseContains('refresh_token');
    }

    public function forgotPasswordNoUser(ApiTester $I)
    {
        $data = [
            'username' => 'xxxxxxxxxxxx'
        ];

        $I->sendPOST('api/v1/user/forgot-password', $data);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Username cannot be found.');
    }

    public function forgotPasswordSuccessful(ApiTester $I)
    {
        $data = [
            'username' => 'user3'
        ];

        $I->sendPOST('api/v1/user/forgot-password', $data);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContains('An email has been sent');
    }

    public function listUsersOrderByLastLogin(ApiTester $I)
    {
        $sort = \App\Enum\UserOrderBy::LAST_LOGIN . '-' . \App\Enum\Order::DESC;
        $I->sendGET('api/v1/user?sort=' . $sort);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $items = json_decode($I->grabResponse(), true)['items'];

        for ($i = 0; $i <= 2; $i++) {
            $item = $items[$i];
            if ($i == 0) {
                $I->assertEquals('user2', $item['username']);
            } else if ($i == 1) {
                $I->assertEquals('user3', $item['username']);
            } else if ($i == 2) {
                $I->assertEquals('user1', $item['username']);
            }
        }
    }

    public function listUsersAsAdminOrderByLastLogin(ApiTester $I)
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

        $sort = \App\Enum\UserOrderBy::LAST_LOGIN . '-' . \App\Enum\Order::DESC;
        $I->sendGET('api/v1/user?sort=' . $sort);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $items = json_decode($I->grabResponse(), true)['items'];

        for ($i = 0; $i <= 4; $i++) {
            $item = $items[$i];
            if ($i == 0) {
                $I->assertEquals('user5', $item['username']);
            } else if ($i == 1) {
                $I->assertEquals('user2', $item['username']);
            } else if ($i == 2) {
                $I->assertEquals('user4', $item['username']);
            } else if ($i == 3) {
                $I->assertEquals('user3', $item['username']);
            } else if ($i == 4) {
                $I->assertEquals('user1', $item['username']);
            }
        }
    }

    public function listUsersAsAdminEnabledFalseOrderByLastLogin(ApiTester $I)
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

        $sort = \App\Enum\UserOrderBy::LAST_LOGIN . '-' . \App\Enum\Order::DESC;
        $enabled = 'false';
        $I->sendGET('api/v1/user?sort=' . $sort . '&enabled=' . $enabled);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $items = json_decode($I->grabResponse(), true)['items'];

        for ($i = 0; $i <= 2; $i++) {
            $item = $items[$i];
            if ($i == 0) {
                $I->assertEquals('user2', $item['username']);
            } else if ($i == 1) {
                $I->assertEquals('user3', $item['username']);
            } else if ($i == 2) {
                $I->assertEquals('user1', $item['username']);
            }
        }
    }

    public function listUsersAsGuestOrderByEnabled(ApiTester $I)
    {
        $sort = \App\Enum\UserOrderBy::ENABLED . '-' . \App\Enum\Order::DESC;
        $enabled = 'false';
        $I->sendGET('api/v1/user?sort=' . $sort . '&enabled=' . $enabled);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Only admin can sort by enabled');
    }

    public function listUsersAsAdminEnabledFalseOrderByEnabled(ApiTester $I)
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

        $sort = \App\Enum\UserOrderBy::ENABLED . '-' . \App\Enum\Order::DESC;
        $enabled = 'false';
        $I->sendGET('api/v1/user?sort=' . $sort . '&enabled=' . $enabled);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $items = json_decode($I->grabResponse(), true)['items'];

        for ($i = 0; $i <= 2; $i++) {
            $item = $items[$i];
            if ($i == 0) {
                $I->assertEquals('user1', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 1) {
                $I->assertEquals('user2', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 2) {
                $I->assertEquals('user3', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 3) {
                $I->assertEquals('user7', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 4) {
                $I->assertEquals('user8', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 5) {
                $I->assertEquals('user10', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 6) {
                $I->assertEquals('user11', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 7) {
                $I->assertEquals('user12', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 8) {
                $I->assertEquals('user13', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 9) {
                $I->assertEquals('user14', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 10) {
                $I->assertEquals('user15', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 11) {
                $I->assertEquals('user16', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 12) {
                $I->assertEquals('user17', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 13) {
                $I->assertEquals('user18', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 14) {
                $I->assertEquals('user19', $item['username']);
                $I->assertEquals(true, $item['enabled']);
            } else if ($i == 15) {
                $I->assertEquals('user4', $item['username']);
                $I->assertEquals(false, $item['enabled']);
            }
        }
    }


}
