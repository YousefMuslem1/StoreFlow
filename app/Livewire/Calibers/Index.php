<?php

namespace App\Livewire\Calibers;

use App\Models\Caliber;
use Illuminate\Support\Facades\Lang;
use Livewire\Component;
use Livewire\Attributes\On; 

class Index extends Component
{
    public $calibers;
    protected $listeners = ['refresh' => 'update'];

    public function mount()
    {

        $this->calibers = Caliber::latest()->get();
    }
    #[On('post-created')] 
    public function update()
    {
        $this->calibers = Caliber::latest()->get();
    }
    public function render()
    {
        return view('livewire.calibers.index')
            ->extends('layouts.app', ['header' => Lang::get('caliber.header'),
                                    'calibers' => $this->calibers]);
    }
}
