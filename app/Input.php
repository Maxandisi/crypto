<?php

namespace App;

use Illuminate\Support\Facades\Storage;

class Input
{
    public $file = 'rates.json';
    public $rates;

    public function __construct($file = '')
    {
        if ($file !== '')
            $this->file = $file;

        $this->rates = new \stdClass();
    }

    public function load($disk = 'public')
    {
        if (Storage::disk($disk)->exists($this->file)) {
            $this->rates = json_decode(Storage::disk($disk)->get($this->file));

            return $this;
        } else {
            return false;
        }
    }
}
