<?php


class RecommendationsCest
{
    public function GetRecommendationsWithLimit(ApiTester $I)
    {
        $I->am('client');
        $I->wantTo("get recommendations - limited count");
        $I->haveHttpHeader('Content-Type', 'application/json');
        $sku ='LO019EMJGZ27';
        $limit = 5;
        $I->sendPOST('get_product_product_recommendations',['sku'=>$sku, 'limit'=>$limit]);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse(), true); //преобразуем json в массив
        $I->assertTrue(count($response)==$limit, "there are $limit recommendations in response"); //в ответе запрошенное количество рекомендаций
        /*
         * Для каждого продукта проверяем наличие поля sku и формат значения в поле -  строка из англ. букв и цифр длиной 12 символов
         */
        for ($i=0; $i<count($response); $i++)
        {
            $I->seeResponseJsonMatchesJsonPath("$.[$i].product.sku");
            $I->seeResponseMatchesJsonType(['sku'=>'string:regex(/^[a-zA-Z0-9]{12}$/)'],"$.[$i].product");
        }
    }

    public function GetRecommendationsDefaultLimit(ApiTester $I) //падает, приходит 20 шт
    {
        $I->am('client');
        $I->wantTo("get recommendations - default limit");
        $I->haveHttpHeader('Content-Type', 'application/json');
        $sku ='LO019EMJGZ27';
        $I->sendPOST('get_product_product_recommendations',['sku'=>$sku]);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse(), true); //преобразуем json в массив
        $I->assertTrue(count($response)==12, "there are 12 recommendations in response"); //в ответе 12 рекомендаций
    }

    public function GetRecommendationsNullSku(ApiTester $I)
    {
        $I->am('client');
        $I->wantTo("see error message when try to get recommendations with null SKU provided");
        $I->haveHttpHeader('Content-Type', 'application/json');
        $limit = 6;
        $I->sendPOST('get_product_product_recommendations',['sku'=>null, 'limit'=>$limit]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson(['faultcode'=>'Client.ValidationError']);
    }

    public function GetRecommendationsInvalidSku(ApiTester $I)
    {
        $I->am('client');
        $I->wantTo("see error message when try to get recommendations with invalid (long) SKU provided");
        $I->haveHttpHeader('Content-Type', 'application/json');
        $limit = 6;
        $sku ='LO019EMJGZ27/';
        $I->sendPOST('get_product_product_recommendations',['sku'=>$sku, 'limit'=>$limit]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson(['faultcode'=>'Client.RECOMMENDATIONS_NOT_AVAILABLE']);
    }
}
