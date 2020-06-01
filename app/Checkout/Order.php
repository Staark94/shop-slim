<?php

namespace Cart\Checkout;

use Cart\Models\Product;
use Cart\Support\Storage\Contracts\StorageInterface;
use Cart\Checkout\Exceptions\QuantityExceededException;

class Order
{
    protected $storage;

    protected $product;

    public function __construct(StorageInterface $storage, Product $product)
    {
        $this->storage = $storage;
        $this->product = $product;
    }
}