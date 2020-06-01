<?php

namespace Cart\Checkout;

use Cart\Models\Product;
use Cart\Support\Storage\Contracts\StorageInterface;
use Cart\Checkout\Exceptions\QuantityExceededException;

class Basket
{
    protected $storage;

    protected $product;

    public function __construct(StorageInterface $storage, Product $product)
    {
        $this->storage = $storage;
        $this->product = $product;
    }

    public function add(Product $product, $quantity)
    {
        if ($this->has($product))
        {
            $quantity = $this->get($product)['quantity'] + $quantity;
        }

        $this->update($product, $quantity);
    }

    public function has(Product $product)
    {
        return $this->storage->exists('cart')[$product->id];
    }

    public function get(Product $product)
    {
        return $this->storage->get('cart')[$product->id];
    }

    public function update(Product $product, $quantity)
    {
        if (!$this->product->find($product->id)->hasStock($quantity))
        {
            throw new QuantityExceededException;
        }

        if ($quantity == 0)
        {
            $this->remove($product);
            return;
        }

        $this->storage->setArray('cart', [$product->id => [
            'product_id' => (int) $product->id,
            'quantity'   => (int) $quantity
        ]]);
    }

    public function remove(Product $product)
    {
        return $this->storage->unsetItem('cart', $product->id);
    }

    public function clear()
    {
        return $this->storage->clear();
    }

    public function all()
    {
        $ids = [];
        $items = [];

        if (!$this->storage->get('cart')) {
            return;
        }

        foreach ($this->storage->get('cart') as $product)
        {
            $ids[] = $product['product_id'];
        }

        $products = $this->product->find($ids);

        foreach ($products as $product)
        {
            $product->quantity = $this->get($product)['quantity'];
            $items[] = $product;
        }

        return $items ?: [];
    }

    public function itemCount()
    {
        if(!$this->storage->get('cart')) return 0;
        return count($this->storage->get('cart'));
    }

    public function subTotal()
    {
        $total = 0;

        foreach ($this->all() as $item)
        {
            if ($item->outOfStock())
            {
                continue;
            }

            $total = $total + $item->price * $item->quantity;
        }

        return $total;
    }

    public function tva()
    {
        $price = $this->subTotal();
        $tva = round( $price * 5.5 ) / 100;
        
        return $tva;
    }

    public function totalPrice()
    {
        return $this->subTotal();
    }

    public function refresh()
    {
        if (!$this->storage->get('cart')) {
            return;
        }
        
        foreach ($this->all() as $item)
        {
            if (!$item->hasStock($item->quantity)) {
                $this->update($item, $item->stock);
            } else if($item->hasStock(1) && $item->quantity == 0) {
                $this->update($item, 1);
            }
        }
    }
}