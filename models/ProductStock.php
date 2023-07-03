<?php
class ProductStock 
{
    private $sku_product;
    private $stock;
    private $send_type;


    public function __construct($sku_product, $stock, $send_type = "Normal")
    {
        $this->sku_product = $sku_product;
        $this->stock = $stock;
        $this->send_type = $send_type;
    }

    public function getSkuProduct()
    {
        return $this->sku_product;
    }

    public function getStock()
    {
        return $this->stock;
    }

    public function getSendType()
    {
        return $this->send_type;
    }
}

