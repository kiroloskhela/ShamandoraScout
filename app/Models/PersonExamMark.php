<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonExamMark extends Model
{
    protected $table = 'PersonExamMark';

    protected $primaryKey = 'ExamMarkID';

    public $timestamps = false;

    protected $fillable = [
        'PersonID',
        'ServentID',
        'QetaaID',
        'SanaMarhalaID',
        'SeasonID',
        'TheoreticalMark',
        'PracticalMark',
        'ExamDate',
        'Note',
    ];

    protected $casts = [
        'ExamDate' => 'date',
        'SeasonID' => 'integer',
        'TheoreticalMark' => 'integer',
        'PracticalMark' => 'integer',
    ];
}
