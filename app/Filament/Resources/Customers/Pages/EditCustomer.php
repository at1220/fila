<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Panel;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;


class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    // format du lieu truoc khi fill
    protected function mutateFormDataBeforeFill(array $data): array{
        $data['name'] == 'Tuaans' ? $data['name'] = 'Ddungs' : $data['name'];
        return $data;
    }
    //format du lieu truoc khi luu
    protected function mutateFormDataBeforeSave(array $data): array{
        $data['name'] == 'Ddungs' ? $data['name'] = 'Tuaans' : $data['name'];
        return $data;
    }
    // custome lưu
    protected function handleRecordUpdate(Model $record, array $data): Model{
       // $data['name'] = $record->name . ' đã thay đổi thành '. $data['name'];
        $record->update($data);
        return $record;
    }
    //
    // Section::make('Rate limiting')
    // ->schema([
    //     // ...
    // ])
    // ->footerActions([
    //     fn (string $operation): Action => Action::make('save')
    //         ->action(function (Section $component, EditRecord $livewire) {
    //             $livewire->saveFormComponentOnly($component);

    //             Notification::make()
    //                 ->title('Rate limiting saved')
    //                 ->body('The rate limiting settings have been saved successfully.')
    //                 ->success()
    //                 ->send();
    //         })
    //         ->visible($operation === 'edit'),
    // ])
}
