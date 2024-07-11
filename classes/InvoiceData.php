<?php

namespace classes;

class InvoiceData
{
    public PersonalData $personalData;
    public float $finalPrice = 0.0;
    public array $products = [];

    public function __construct()
    {
        $this->personalData = new PersonalData();
    }
}