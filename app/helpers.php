<?php

function dd(...$dump) {
    foreach ($dump as $line)
    {
        var_dump($line);
    }
    
    die();
}