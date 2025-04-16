<?php

namespace App;

use Illuminate\Support\Facades\Auth;

trait TracksUserActivity
{
    public static function bootTracksUserActivity()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = $model->created_by ?? Auth::id();
                $model->updated_by = $model->updated_by ?? Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
        
        static::deleting(function ($model) {
            if (Auth::check() && $model->usesSoftDeletes()) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }
}