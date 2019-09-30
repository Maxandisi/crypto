<?php

namespace App\Http\Controllers;

use \App\Input;

class GenerateController extends Controller
{
    public function index()
    {
        $input = (new Input)->load();

        if ($input === false) {
            die('File not found.');
        }

        // Информация из БД о фидах, для кого выгружаем
        $feeds = ['json'];

        array_map(function($feed) use ($input){
            $classname = '\App\Feeds\Output' . ucfirst(strtolower($feed));

            if (class_exists($classname)) {
                $output = new $classname($input);
                $output->save($feed);
            }
        }, $feeds);
    }
}
