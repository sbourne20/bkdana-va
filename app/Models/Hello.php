<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hello
{
    /**
     * Say hello.
     *
     * @param string $firstName
     * @return string $greetings
     */
    public function sayHello($firstName)
    {
        return 'Hello ' . $firstName;
    }
}
