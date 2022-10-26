<?php

namespace App\Abstraction\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface ReadWriteControllerInterface extends ControllerInterface, ReadOnlyControllerInterface
{

}

