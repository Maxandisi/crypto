<?php

namespace App\Abstracts;

use Illuminate\Support\Facades\Storage;

abstract class Output
{
    public $input;
    public $output;
    public $fileType = '';

    public function __construct(\App\Input $input)
    {
        $this->input = $input;
    }

    public function save($name, $disk = 'public')
    {
        $this->convert();
        $this->setOutput();

        Storage::disk($disk)->put('/feeds/' . $name . '.' . $this->fileType, $this->toString($this->output));
    }

    protected function convert()
    {
        foreach ($this->input->rates as &$rate) {
            if ((float)$rate->fees->in_per != 0.00) {
                $rate->price = $rate->price / (1 + 1 * $rate->fees->in_per / 100);
            }

            if ((float)$rate->fees->out_per != 0.00) {
                $rate->price = $rate->price - ($rate->price * $rate->fees->out_per / 100);
            }
        }
    }

    protected function roundingDown($num, $precision = 0)
    {
        return floor($num * pow(10, $precision)) / pow(10, $precision);
    }

    protected abstract function setOutput();

    protected abstract function toString();
}
