<?php 

class YearCest
{
    public function _before(ApiTester $I)
    {
    }

    public function listYears(ApiTester $I)
    {
        $I->sendGET('api/v1/year');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseContainsJson();
        $expectedResponse = [
            [
                'year_from' => 2000
            ],
            [
                'year_from' => 2001
            ],
            [
                'year_from' => 2002
            ],
            [
                'year_from' => 2003
            ],
            [
                'year_from' => 2009
            ],
            [
                'year_from' => 2010
            ],
            [
                'year_from' => 2011
            ],
            [
                'year_from' => 2012
            ],
            [
                'year_from' => 2015
            ],
            [
                'year_from' => 2016
            ],
            [
                'year_from' => 2017
            ],
            [
                'year_from' => 2018
            ],
            [
                'year_from' => 2019
            ]
        ];
        $I->seeResponseContainsJson($expectedResponse);
    }
}
