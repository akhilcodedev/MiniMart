<?php

 namespace Modules\CMS\Models;

 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes;
 use Illuminate\Database\Eloquent\Factories\HasFactory;

 class Banner extends Model
 {
     use HasFactory, SoftDeletes;

     /**
      * The attributes that are mass assignable.
      */
     protected $table = 'hero_banners';

     protected $fillable = [
         'title',
         'subtitle',
         'description',
         'images',
         'alt_tag',
         'position',
         'status'
     ];


     protected $casts = [
         'images' => 'array',
       ];
 }