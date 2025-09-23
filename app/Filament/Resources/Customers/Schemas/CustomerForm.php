<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Support\Facades\Hash;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                     ->rules(function ($livewire) {
        if ($livewire instanceof \Filament\Resources\Pages\CreateRecord) {
            return [
                \Illuminate\Validation\Rule::unique('users', 'email'),
            ];
        }

        return []; // không cần unique khi edit
    }),
                
                TextInput::make('password')
                   ->password()
                   ->visibleOn(Operation::Create)
                   ->required(fn ($livewire) => $livewire instanceof CreateCustomer)
                   ->revealable()
                   //->dehydrateStateUsing(fn($state)=> $state ? Hash::make($state): null)
                   ,
            ]);
    }
}
