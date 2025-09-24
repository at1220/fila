<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Models\Sale;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
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
                // ->dehydrateStateUsing(fn($state)=> $state ? Hash::make($state): null)
                ,
                // tạo mới trong select

                Select::make('cared_by')
                    ->label('Nhân viên quản lý')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(User::pluck('name', 'id')) // 👈 ánh xạ id => name
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(User::class, 'email')
                            ->required(),
                        TextInput::make('password')
                            ->password()
                            ->required()
                            ->revealable(),
                    ])
                    ->createOptionAction(function ($action) {
                        return $action
                            ->modalHeading('Thêm User mới')
                            ->modalWidth('sm');
                    })
                    ->createOptionModalHeading('Thêm nhân viên')
                    ->createOptionUsing(function (array $data) {
                        $user = \App\Models\User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
                        ]);

                        return $user->id; // 👈 chỉ return id
                    }),

                // đây là tạo 1 trang lưu riêng không ảnh
                Section::make('Thay đổi mật khẩu')
                    ->schema([
                        TextInput::make('old_password')
                            ->password()
                            ->visibleOn(Operation::Edit)
                            ->label('Mật khẩu cũ')
                            ->required(fn ($livewire) => $livewire instanceof CreateCustomer)
                            ->revealable(),
                        TextInput::make('new_password')
                            ->visibleOn(Operation::Edit)
                            ->label('Mật khẩu mới')
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof CreateCustomer)
                            ->revealable(),
                        TextInput::make('confirm_password')
                            ->password()
                            ->visibleOn(Operation::Edit)
                            ->label('Xác nhận mật khẩu')
                            ->required(fn ($livewire) => $livewire instanceof CreateCustomer)
                            ->revealable(),
                    ])->visibleOn(Operation::Edit)
                    ->footerActions([
                        fn (string $operation): Action => Action::make('save')
                            ->action(function (Section $component, EditRecord $livewire) {
                                $state = $component->getState();
                                if ($state['old_password'] !== $livewire->record->password) {
                                    Notification::make()
                                        ->title('Mật khẩu cũ không đúng')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                if ($state['new_password'] !== $state['confirm_password']) {
                                    Notification::make()
                                        ->title('Xác nhận mật khẩu không khớp')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $livewire->record->update([
                                    'password' => $state['new_password'],
                                ]);

                                Notification::make()
                                    ->title('Đã đổi mật khẩu thành công')
                                    ->success()
                                    ->send();

                                $component->saveRelationships();
                            })
                            ->visible($operation === 'edit'),
                    ]),

                // Section::make('Tạo nhân viên')
                //     ->schema([
                //         TextInput::make('name_staff')
                //             ->label('Tên nhân viên')
                //             ->visibleOn(Operation::Create)
                //             ->required(),
                //         TextInput::make('phone_staff')
                //             ->label('SĐT nhân viên')
                //             ->visibleOn(Operation::Create)
                //             ->tel()
                //             ->required(),
                //         TextInput::make('address_staff')
                //             ->label('Địa chỉ')
                //             ->visibleOn(Operation::Create)
                //             ->required(),
                //         Select::make('type_staff')
                //             ->options([
                //                 'office' => 'Chính thức',
                //                 'probationary' => 'Thử việc',
                //                 'intern' => 'Thực tập sinh',
                //             ])
                //             ->label('Loại nhân viên')
                //             ->visibleOn(Operation::Create)
                //             ->required(),
                //     ])->visibleOn(Operation::Create)
                //     ->footerActions([
                //         fn (string $operation): Action => Action::make('save')
                //             ->action(function (Section $component, CreateRecord $livewire) {
                //                 $state = $component->getState();
                //                 Sale::create([
                //                     'name' => $state['name_staff'],
                //                     'phone' => $state['phone_staff'],
                //                     'address' => $state['address_staff'],
                //                     'type' => $state['type_staff'],
                //                 ]);

                //                 Notification::make()
                //                     ->title('Tạo mới nhân viên thành công')
                //                     ->success()
                //                     ->send();

                //                 $component->saveRelationships();
                //             })
                //             ->visible($operation === 'create'),
                //     ]),
            ]);
    }
}
