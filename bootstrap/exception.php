<?php

use libs\View;

function exception_handler($exception)
{
    // return View::render("An error occurred, please try again later", 500);
    dd($exception->getMessage());
}

set_exception_handler('exception_handler');