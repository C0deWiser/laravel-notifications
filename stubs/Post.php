<?php

namespace Stubs;

use Codewiser\Notifications\Contracts\Mentionable;
use Codewiser\Notifications\Traits\HasMentions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model implements Mentionable
{
    use HasMentions, SoftDeletes;
}