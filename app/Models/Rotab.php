<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rotab extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'RotabInformation';
    
    /**
     * Indicates primary key of the table
     *
     * @var int
     */
    public $primaryKey = 'RotbaID';
    
    /**
     * Indicates if the model should be timestamped
     * 
     * default value is 'true'
     * 
     * If set 'true' then created_at and updated_at columns 
     * will be automatically managed by Eloquent
     * 
     * If created_at and updated_at columns are not in your table
     * then set the value to 'false'
     *
     * @var string
     */
    public $rotbaName = 'RotbaName';
    
    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = array('rotba_id', 'rotba_name');
    
    /**
     * The attributes that aren't mass assignable
     *
     * @var array
     */
    protected $guarded = array();
}