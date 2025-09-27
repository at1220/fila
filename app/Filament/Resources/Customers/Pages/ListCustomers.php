<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalHeading('Tạo khách hàng mới'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->icon('heroicon-s-arrow-up-right')->label('Tất cả'),
            'has_account' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('user_id'))->label('Có tài khoản'),
        ];
    }

    public function getDefaultActiveTab(): int|string|null
    {
        return 'all';
    }
}
