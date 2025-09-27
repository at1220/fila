<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use stdClass;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('print_action')
                    ->label('In')
                    ->state('📄')
                    ->url(fn ($record) => CustomerResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
                TextColumn::make('index')
                    ->label('STT'),
                TextColumn::make('id')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record, $livewire) {
                        return highlightSearch($state, $livewire->getTableSearch());
                    })

                    ->html() // bắt buộc: cho phép render HTML
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Tên - SĐT')
                    // màu xanh nguyên dòng và có descriptions
                    ->formatStateUsing(function ($state, $record, $livewire) {
                        $search = $livewire->getTableSearch();

                        $highlightedName = highlightSearch($state, $search);
                        $highlightedPhone = highlightSearch($record->phone, $search);

                        return $highlightedName.'<br><small style="color: gray;">'.$highlightedPhone.'</small>';
                    })
                    ->html()
                    ->sortable(['name', 'phone'])
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    }),
                // TextColumn::make('name')
                //     ->label('Tên')
                //     ->wrapHeader()
                //     ->alignStart()
                //     ->verticallyAlignStart()
                //     ->state(fn ($record) => 'Hello '.$record->name)
                //     ->searchable()
                //     // ->searchable(isIndividual: true, isGlobal: false)
                //     ->tooltip('Tên')
                //     ->formatStateUsing(function ($state, $record, $livewire) {
                //         return highlightSearch($state, $livewire->getTableSearch());
                //     })

                //     ->html() // bắt buộc: cho phép render HTML
                // ,
                // TextColumn::make('phone')
                //     ->alignCenter()

                //     ->verticallyAlignCenter()
                //     ->formatStateUsing(function ($state, $record, $livewire) {
                //         return highlightSearch($state, $livewire->getTableSearch());
                //     })

                //     ->html() // bắt buộc: cho phép render HTML
                //   // ->searchable(isIndividual: true, isGlobal: false),
                //     ->searchable(),
                ColumnGroup::make('Email', [
                    TextColumn::make('email')
                    // tô xanh nguyên dòng nếu trùng
                        ->formatStateUsing(function ($state, $record, $livewire) {
                            return highlightSearch($state, $livewire->getTableSearch());
                        })

                        ->html() // bắt buộc: cho phép render HTML
                        ->toggleable()
                        ->label('Email address')
                        ->searchable(),
                    TextColumn::make('user.email')
                        ->extraHeaderAttributes(fn () => [
                            'class' => 'bg-yellow-200 !text-red-600',
                        ])
                        ->formatStateUsing(function ($state, stdClass $rowLoop, $livewire) {
                            $rowLoop = $rowLoop->iteration.'. '.$state;
                            $highlightedState = highlightSearch($rowLoop, $livewire->getTableSearch());

                            return strip_tags($highlightedState);
                        })
                        ->toggleable()
                        ->label('Email đăng nhập')
                        ->searchable(),
                ]),

                TextColumn::make('caredByNames')
                    ->alignEnd()
                    ->verticallyAlignEnd()
                    ->label('NV quản lí')
                    ->placeholder('Không có')
                    ->listWithLineBreaks()
                    ->formatStateUsing(function ($state, $record, $livewire) {
                        return highlightSearch($state, $livewire->getTableSearch());
                    })

                    ->html() // bắt buộc: cho phép render HTML
                    ->searchable(query: function ($query, string $search) {
                        // Lấy list id user có tên giống từ khóa
                        $userIds = User::where('name', 'like', "%{$search}%")
                            ->pluck('id')
                            ->toArray();

                        if (! empty($userIds)) {
                            $query->orWhere(function ($q) use ($userIds) {
                                foreach ($userIds as $id) {
                                    $q->orWhereJsonContains('cared_by', $id);
                                }
                            });
                        }

                        // Ngoài ra search thêm trong quan hệ user chính
                        $query->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('user_id')
                    ->label('Tài khoản')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->getSearchResultsUsing(function (string $search) {
                        return User::query()
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id');
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->name),
            ])
            ->paginated([15, 25, 50, 100])->extremePaginationLinks()->striped()
            // ->persistSortInSession()//duy trì các trường khi tải lại trang
            ->deferLoading() // skeleton
            ->reorderableColumns() // sắp xếp lại cột theo thứ tự kéo thả đănng xuất là quên
        //    ->paginationMode(PaginationMode::Cursor)
        //    ->openRecordUrlInNewTab()//mở ra tab mới
        //     ->defaultSort(function (Builder $query): Builder {
        //         return $query->orderBy('phone','asc')->orderBy('id','desc');
        //     })
        //    ->searchable(['user.email']) // search các trường ko có trong bảng
        //    ->searchPlaceholder('Search (ID, Name)')
        //     ->searchOnBlur() //enter mới search
            ->columnManagerTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Tuỳ chỉnh'),
            )
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
