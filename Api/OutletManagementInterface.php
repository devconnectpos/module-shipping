<?php
namespace SM\Shipping\Api;

interface OutletManagementInterface
{

    /**
     * Find offices for the customer
     *
     * @return mixed
     */
    public function fetchOutlets();
}
