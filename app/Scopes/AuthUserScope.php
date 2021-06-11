<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;


class AuthUserScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
//        if ($builder->getConnection() == 'pgsql') {
//            $builder->whereRaw(Auth::id() . ' = ANY(' . $model->getTable() . '.user_ids)');
//        } else {
//            $builder->whereRaw('FIND_IN_SET(' . Auth::id() . ', ' . $model->getTable() . '.user_ids)');
//        }
    }
}