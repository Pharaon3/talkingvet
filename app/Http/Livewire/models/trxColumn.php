<?php

namespace App\Http\Livewire\models;

class trxColumn
{
    public $title = "";
    public ?string $from = null;
    public bool $sortable = false;
    public bool $hidden = false;

    /**
     * @param string $title
     */
    public function __construct(string $title, string $from = null)
    {
        $this->title = $title;
        if($from)
        {
            $this->from = $from;
        }
    }

    /**
     * @param  string  $title
     *
     * @return static
     */
    public static function make(string $title, string $from = null): trxColumn
    {
        return new static($title, $from);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getColumnSelectName() : string
    {
        return $this->from ?? $this->title;
    }

    public function sortable(): trxColumn
    {
        $this->sortable = true;
        return $this;
    }

    public function hidden(): trxColumn
    {
        $this->hidden = true;
        return $this;
    }


}
