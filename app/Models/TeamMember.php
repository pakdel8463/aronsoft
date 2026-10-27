<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    protected $fillable = ['name', 'role', 'photo', 'linkedin'];

    public function portfolios()
    {
        return $this->belongsToMany(Portfolio::class, 'portfolio_team_member', 'team_member_id', 'portfolio_id');
    }
}
