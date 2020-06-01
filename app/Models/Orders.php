<?php

namespace Cart\Models;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    public function status() {
        $result = "";

        switch($this->status) {
            case 0:
                $result = "Comanda Plasata";
            break;

            case 1:
                $result = "Comanda procesata";
            break;

            case 2:
                $result = "Produse livrate";
            break;

            case 3:
                $result = "Livrare anulata";
            break;
        }

        return $result;
    }
}