<?php
declare(strict_types=1);

namespace App\Test\Helper;

trait IntegrationHelperTrait
{
    public function getJsonBody()
    {
        return json_decode((string)$this->_response->getBody(), true);
    }
}
