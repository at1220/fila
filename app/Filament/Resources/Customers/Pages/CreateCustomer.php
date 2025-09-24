<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Operation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateCustomer extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // đây là nơi xử lý dữ liệu đầu vào
        return $data;
    }

    // xử lý hàm taoj nâng cao
    protected function handleRecordCreation(array $data): Model
    {

        // 1. Backup check: Email đã tồn tại trong bảng users chưa?
        if (User::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Email này đã tồn tại trong hệ thống.',
            ]);
        }

        // 2. Tạo user trước
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'] ?? '123456'), // tuỳ bạn set mặc định hoặc yêu cầu nhập
        ]);

        // 3. Gán user_id cho customer
        $data['user_id'] = $user->id;

        // 4. Tạo customer và trả về
        return Customer::create($data);
    }

    // tao thong bao
    protected function getCreatedNotificationTitle(): ?string
    {
        return "Đã tạo thành công khách hàng '{$this->record->name}'";
    }

    // chuyển đến trang nào
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::geturl('index');
    }

    // noti
    protected function getCreatedNotification(): ?Notification
    {
        // return null để vô hiệu hoá thông báo
        return Notification::make()
            ->success()
            ->title('Thàn công')
            ->body("Đã tạo thành công khách hàng '{$this->record->name}'");
    }
    // đổi lable của các nút

    // tắt nút another
    // protected static bool $canCreateAnother = false;

    // tung buoc
    protected function getSteps(): array
    {
        return [
            Step::make('Tạo mới khách hàng')
                ->description('Tạo mới thông tin đăng nhập')
                ->schema([
                    TextInput::make('email')
                        ->label('Email address')
                        ->email()
                        ->required()
                        ->unique(User::class, 'email'),
                    TextInput::make('password')
                        ->password()
                        // ->visibleOn(Operation::Create)
                        ->required(fn ($livewire) => $livewire instanceof CreateCustomer)
                        ->revealable()
                    // ->dehydrateStateUsing(fn($state)=> $state ? Hash::make($state): null)
                    ,
                    TextInput::make('name')
                        ->required(),
                    TextInput::make('phone')
                        ->tel()
                        ->required(),
                ]),
            Step::make('Tạo mới nhân viên')
                ->description('Tạo mới thông tin đăng nhập')
                ->schema([
                    Section::make('Tạo nhân viên')
                        ->schema([
                            TextInput::make('name_staff')
                                ->label('Tên nhân viên')
                                ->visibleOn(Operation::Create)
                                ->required(),
                            TextInput::make('phone_staff')
                                ->label('SĐT nhân viên')
                                ->visibleOn(Operation::Create)
                                ->tel()
                                ->required(),
                            TextInput::make('address_staff')
                                ->label('Địa chỉ')
                                ->visibleOn(Operation::Create)
                                ->required(),
                            Select::make('type_staff')
                                ->options([
                                    'office' => 'Chính thức',
                                    'probationary' => 'Thử việc',
                                    'intern' => 'Thực tập sinh',
                                ])
                                ->label('Loại nhân viên')
                                ->visibleOn(Operation::Create)
                                ->required(),
                        ])->visibleOn(Operation::Create)
                        ->footerActions([
                            fn (string $operation): Action => Action::make('save')
                                ->action(function (Section $component, CreateRecord $livewire) {
                                    $state = $component->getState();
                                    Sale::create([
                                        'name' => $state['name_staff'],
                                        'phone' => $state['phone_staff'],
                                        'address' => $state['address_staff'],
                                        'type' => $state['type_staff'],
                                    ]);

                                    Notification::make()
                                        ->title('Tạo mới nhân viên thành công')
                                        ->success()
                                        ->send();

                                    $component->saveRelationships();
                                })
                                ->visible($operation === 'create'),
                        ]),

                ]),
        ];
    }

    // có thể bỏ qua bước
    public function hasSkippableSteps(): bool
    {
        return true;
    }

    // them nut
    // protected function getFormActions(): array{
    //     return[
    //         // ...parent::getFormActions(),
    //         // Action::make('close')->action('createAndClose'),
    //         $this->getCreateFormAction()->formId('form')
    //     ];
    // }
    // public function createAndClose(): void {
    //     dd(123);
    //     return ;
    // }
}
