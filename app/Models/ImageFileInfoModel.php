<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class ImageFileInfoModel extends Model
{
    protected $table = 'file_infos';
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $keyType = 'string';
    protected $fillable = ['id', 'name', 'extension', 'userId'];
}
