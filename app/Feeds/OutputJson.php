<?php

namespace App\Feeds;

use App\Abstracts\Output;

class OutputJson extends Output
{
    public $fileType = 'json';

    protected function setOutput()
    {
        $output = new \stdClass();
        $output->exchanges = new \stdClass();
        $output->exchanges->currencies = new \stdClass();
        $output->exchanges->from = new \stdClass();

        // Счетчик, для целей оптимизации
        $i = 1;

        // Названия валют, для оптимизации
        $aliases = [];


        foreach ($this->input->rates as $pair => $rate) {
            if($rate->price <= 0) continue;

            $currencies = explode('-', $pair);

            $curFrom = array_search($currencies[0], $aliases);
            if ($curFrom === false) {
                $aliases[$i] = $currencies[0];
                $curFrom = $i;
                $i++;
            }

            $curTo = array_search($currencies[1], $aliases);
            if ($curTo === false) {
                $aliases[$i] = $currencies[1];
                $curTo = $i;
                $i++;
            }


            if (!isset($output->exchanges->from->{$curFrom})) {
                $output->exchanges->from->{$curFrom} = new \stdClass();
                $output->exchanges->from->{$curFrom}->to = new \stdClass();
            }

            $objectTo = $output->exchanges->from->{$curFrom}->to;

            if (!isset($objectTo->{$curTo})) {
                $objectTo->{$curTo} = new \stdClass();
            }

            $objectTo->{$curTo}->out = $this->roundingDown($rate->price, 6);
            // Убираем експоненту
            $objectTo->{$curTo}->out = rtrim(sprintf('%.6F', $objectTo->{$curTo}->out), '0');

            // Для фиксированной комиссии, если необходимо передавать
            // Оптимизация не применяется, комисии могут быть различны в пределах группы
//            if ($rate->fees->in_fix != 0) {
//                $objectTo->{$curTo}->in_fee = $rate->fees->in_fix;
//            }
//
//            if ($rate->fees->out_fix != 0) {
//                $objectTo->{$curTo}->out_fee = $rate->fees->out_fix;
//            }

            // Если необходимы лимиты для обмена
            // TODO: необходимо проверять что мы округляем, если фиат, то до двух знаков, если крипта то, до 4 знаков
            // Оптимизацию для amount не применяю, так как, максимальная сумма, не везде одинакова для группы валют
//            if ($rate->limits->max != 0) {
//                $objectTo->{$curTo}->amount = $this->roundingDown($rate->limits->max, 4);
//            }
//
//            if ($rate->limits->min != 0) {
//                $objectTo->{$curTo}->in_min_amount = $this->roundingDown($rate->limits->min, 4);
//            }
        }

        // Записываем валюты для оптимизации
        $objectAliases = new \stdClass();

        foreach ($aliases as $key => $val) {
            $key = (string) $key;
            $objectAliases->{$key} = $val;
        }

        $output->exchanges->currencies->aliases = $objectAliases;

        $this->output = $output;
    }

    protected function toString()
    {
        return json_encode($this->output);
    }
}
