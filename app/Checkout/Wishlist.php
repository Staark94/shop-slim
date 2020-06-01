<?php

namespace Cart\Checkout;

use Cart\Models\Product;
use Cart\Support\Storage\Contracts\StorageInterface;
use Cart\Checkout\Exceptions\QuantityExceededException;

class Wishlist
{
    protected $storage;

    protected $product;

    public function __construct(StorageInterface $storage, Product $product)
    {
        $this->storage = $storage;
        $this->product = $product;
    }

    public function add(Product $product)
    {
        return $this->update($product);
    }

    public function has(Product $product) {
        return $this->storage->exists('wishlist')[$product->id];
    }

    public function get(Product $product)
    {
        return $this->storage->get('wishlist')[$product->id];
    }

    public function remove(Product $product)
    {
        return $this->storage->unsetItem('wishlist', $product->id);
    }

    public function update(Product $product)
    {
        if (!$this->product->find($product->id)){
            throw new QuantityExceededException;
        }

        $this->storage->setArray('wishlist', [$product->id => [
            'id'    => (int) $product->id,
            'title' => (string) $product->title,
            'stock' => (int) $product->stock,
            'slug'  => (string) $product->slug
        ]]);
    }

    public function all()
    {
        $ids = [];
        $items = [];

        if (!$this->storage->get('wishlist')) {
            return;
        }

        foreach ($this->storage->get('wishlist') as $product)
        {
            $ids[] = $product['id'];
        }

        $products = $this->product->find($ids);

        foreach ($products as $product)
        {
            $items[] = $product;
        }

        return $items ?: [];
    }

    public function itemCount()
    {
        if(!$this->storage->get('wishlist')) return 0;
        return count($this->storage->get('wishlist'));
    }
}