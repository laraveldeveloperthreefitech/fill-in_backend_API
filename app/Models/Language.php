<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['id', 'name','status', 'created_at', 'updated_at'];

    public function scopeSearch($query ,$search){
        if($search->search){
            $query->where('name', 'like','%'.$search->search.'%');
        }
        if(isset($search->status) && $search->status != ''){
            $query->where('status',$search->status);
        } 
        if(isset($search->date) && $search->date){
            $date = explode('-',$search->date);
            $query->whereBetween('created_at', [date('Y-m-d',strtotime($date[0])), date('Y-m-d',strtotime($date[1]))]);
        } 
    }
}
