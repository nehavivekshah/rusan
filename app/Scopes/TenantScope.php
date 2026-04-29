<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
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
        if (app()->runningInConsole()) {
            return;
        }

        if (Auth::hasUser()) {
            // Master role bypasses multi-tenant scoping
            if (Auth::user()->role === 'master') {
                return;
            }
            $builder->where($model->getTable() . '.cid', Auth::user()->cid);
        } elseif (session()->has('cid')) {
            $builder->where($model->getTable() . '.cid', session('cid'));
        }
    }
}
