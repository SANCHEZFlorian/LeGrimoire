<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['question', 'answer', 'is_active', 'order'])]
class Faq extends Model
{
    //
}
