<?php
class ProductStock 
{
    private $sku_product;
    private $stock;


    public function __construct($sku_product, $stock)
    {
        $this->sku_product = $sku_product;
        $this->stock = $stock;
    }

    public function getSkuProduct()
    {
        return $this->sku_product;
    }

    public function getStock()
    {
        return $this->stock;
    }
}

