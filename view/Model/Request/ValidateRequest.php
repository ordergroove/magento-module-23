<?php

namespace Ordergroove\Subscription\Model\Request;

/**
 * Class ValidateRequest
 * @package Ordergroove\Subscription\Model\Request
 */
class ValidateRequest
{
    /**
     * @param $request
     * @return bool|string[]
     */
    public function checkPostRequestData($request)
    {
        $isPost = $request->isPost();
        if (!$isPost) {
            return ['errorMsg' => 'Wrong method request, Please try again'];
        }

        $authorization = $request->getServerValue('HTTP_AUTHORIZATION');
        if (!$authorization) {
            return ['errorMsg' => 'Authentication failed. Authorization Header is missing'];
        }

        $contentType = $request->getHeader('Content-Type');
        if (!($contentType == 'application/xml')) {
            return ['errorMsg' => 'Invalid content type has been received'];
        }

        $orderData = $request->getContent();
        if (empty($orderData)) {
            return ['errorMsg' => 'No data to process'];
        }
        return true;
    }
}
