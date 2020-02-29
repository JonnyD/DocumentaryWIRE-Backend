<?php 

class CategoryCest
{
    public function _before(ApiTester $I)
    {
    }

    public function listCategoriesAsGuest(ApiTester $I)
    {
        $I->sendGET('api/v1/category');

        $expectedResponse = [
            [
                'name' => 'Category 1',
                'slug' => 'category-1',
                'status' => 'enabled',
                'documentaryCount' => 2
            ],
            [
                'name' => 'Category 2',
                'slug' => 'category-2',
                'status' => 'enabled',
                'documentaryCount' => 2
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listCategoriesAsUser(ApiTester $I)
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

        $I->sendGET('api/v1/category');

        $expectedResponse = [
            [
                'name' => 'Category 1',
                'slug' => 'category-1',
                'status' => 'enabled',
                'documentaryCount' => 2
            ],
            [
                'name' => 'Category 2',
                'slug' => 'category-2',
                'status' => 'enabled',
                'documentaryCount' => 2
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listCategoriesAsAdmin(ApiTester $I)
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

        $I->sendGET('api/v1/category');

        $expectedResponse = [
            [
                'name' => 'Category 1',
                'slug' => 'category-1',
                'status' => 'enabled',
                'documentaryCount' => 2
            ],
            [
                'name' => 'Category 2',
                'slug' => 'category-2',
                'status' => 'enabled',
                'documentaryCount' => 2
            ],
            [
                'name' => 'Category 3',
                'slug' => 'category-3',
                'status' => 'disabled',
                'documentaryCount' => 0
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function listDisabledCategoriesAsAdmin(ApiTester $I)
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

        $status = \App\Enum\CategoryStatus::DISABLED;
        $I->sendGET('api/v1/category?status=' . $status);

        $expectedResponse = [
            [
                'name' => 'Category 3',
                'slug' => 'category-3',
                'status' => 'disabled',
                'documentaryCount' => 0
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function getEnabledCategoryAsGuest(ApiTester $I)
    {
        $categorySlug = 'category-1';
        $I->sendGET('api/v1/category/' . $categorySlug);

        $expectedResponse = [
            'name' => 'Category 1',
            'slug' => 'category-1',
            'status' => 'enabled',
            'documentaryCount' => 2
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function getDisabledCategoryAsGuest(ApiTester $I)
    {
        $categorySlug = 'category-3';
        $I->sendGET('api/v1/category/' . $categorySlug);

        $I->seeResponseContains('Not authorixed');
    }

    public function getEnabledCategoryAsUser(ApiTester $I)
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

        $categorySlug = 'category-1';
        $I->sendGET('api/v1/category/' . $categorySlug);

        $expectedResponse = [
            'name' => 'Category 1',
            'slug' => 'category-1',
            'status' => 'enabled',
            'documentaryCount' => 2
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function getDisabledCategoryAsUser(ApiTester $I)
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

        $categorySlug = 'category-3';
        $I->sendGET('api/v1/category/' . $categorySlug);

        $I->seeResponseContains('Not authorixed');
    }

    public function getDisabledCategoryAsAdmin(ApiTester $I)
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

        $categorySlug = 'category-3';
        $I->sendGET('api/v1/category/' . $categorySlug);

        $expectedResponse = [
            'name' => 'Category 3',
            'slug' => 'category-3',
            'status' => 'disabled',
            'documentaryCount' => 0
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }

    public function editCategoryAsGuest(ApiTester $I)
    {
        $categoryClass = \App\Entity\Category::class;

        /** @var \App\Entity\Category $category */
        $category = $I->grabEntityFromRepository($categoryClass, [
            'slug' => 'category-1'
        ]);
        $I->assertNotNull($category->getId());

        $data = [
            "name" => "Category 999",
	        "slug" => "category-999",
	        "status" => "enabled"
        ];
        $I->sendPATCH('/api/v1/category/' . $category->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
    }

    public function editCategoryAsUser(ApiTester $I)
    {
        $categoryClass = \App\Entity\Category::class;

        /** @var \App\Entity\Category $category */
        $category = $I->grabEntityFromRepository($categoryClass, [
            'slug' => 'category-1'
        ]);
        $I->assertNotNull($category->getId());

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

        $data = [
            "name" => "Category 1",
            "slug" => "category-1",
            "status" => "enabled"
        ];
        $I->sendPATCH('/api/v1/category/' . $category->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
    }

    public function editCategoryAsAdmin(ApiTester $I)
    {
        $categoryClass = \App\Entity\Category::class;

        /** @var \App\Entity\Category $category */
        $category = $I->grabEntityFromRepository($categoryClass, [
            'slug' => 'category-1'
        ]);
        $I->assertNotNull($category->getId());

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

        $data = [
            "name" => "Category 999",
            "slug" => "category-999",
            "status" => "enabled"
        ];
        $I->sendPATCH('/api/v1/category/' . $category->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContainsJson();

        $expectedResponse = [
            'id' => $category->getId(),
            'name' => 'Category 999',
            'slug' => 'category-999',
            'status' => 'enabled'
        ];
        $I->seeResponseContainsJson($expectedResponse);

        $data = [
            "name" => "Category 1",
            "slug" => "category-1",
            "status" => "enabled"
        ];
        $I->sendPATCH('/api/v1/category/' . $category->getId(), json_encode($data));
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }
}
