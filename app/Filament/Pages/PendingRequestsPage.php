<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class PendingRequestsPage extends Page implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected string $view = 'filament.pages.pending-requests';
public static function getNavigationLabel(): string
{
    return 'الطلبات المعلقة';
}

    // ✅ إظهار العدد في القائمة الجانبية
    public static function getNavigationBadge(): ?string
    {
        $count = \App\Models\User::whereIn('user_type', ['clinic', 'lab'])
            ->where('user_status', 'pending')
            ->count();

        // إذا كان العدد 0، نرجع null ليخفي Filament الشارة تلقائياً
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning'; // خيارات: primary, success, danger, warning, info, gray
    }

    public function getTitle(): string
    {
        return 'إدارة الطلبات';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-clipboard-document-list';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return User::query()
            ->whereIn('user_type', ['clinic', 'lab'])
            ->where('user_status', 'pending')
            ->with(['ownedClinic', 'ownedLab']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المستخدم')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                                    ->label('الهاتف')
                                    ->searchable(),

                Tables\Columns\TextColumn::make('user_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match($state) {
                        'clinic' => 'عيادة',
                        'lab'    => 'مختبر',
                        default  => $state,
                    })
                    ->colors(['info' => 'clinic', 'purple' => 'lab']),

                Tables\Columns\TextColumn::make('relation_name')
                    ->label('اسم العيادة/المختبر')
                    ->getStateUsing(fn(User $r) => $this->getRelated($r, 'name'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('relation_phone')
                    ->label('هاتف العيادة/المختبر')
                    ->getStateUsing(fn(User $r) => $this->getRelated($r, 'phone')),

                Tables\Columns\TextColumn::make('relation_location')
                    ->label('الموقع')
                    ->getStateUsing(fn(User $r) => $this->getRelated($r, 'location'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('relation_license_number')
                    ->label('رقم الترخيص')
                    ->getStateUsing(fn(User $r) => $this->getRelated($r, 'license_number')),

                Tables\Columns\TextColumn::make('phone')
                                    ->label('الهاتف')
                                    ->searchable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->modalWidth('4xl')
                    ->modalHeading(fn(User $record) => 'تفاصيل الطلب: ' . $this->getRelated($record, 'name'))
                    ->form(fn(User $record) => $this->getDetailsFormSchema($record))
                    ->modalFooterActions(fn(User $record) => [
                        \Filament\Actions\Action::make('approve')
                            ->label('قبول')

                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->action(function (User $record) {
                                $this->handleRequest($record, 'approved');
                                
                            }),

                    // داخل ->actions([...]) استبدل Action::make('reject') بهذا:

                    Action::make('reject')
    ->label('رفض')
    ->color('danger')
    ->icon('heroicon-o-x-circle')
    ->form([
        Forms\Components\Textarea::make('rejection_reason')
            ->label('سبب الرفض')
            ->required()
            ->rows(3)
            ->placeholder('اكتب سبب رفض الطلب هنا...'),

        Forms\Components\CheckboxList::make('rejected_documents')
            ->label('الوثائق المرفوضة')
            ->options([
                'license'        => 'الترخيص',
                'commercial_reg' => 'السجل التجاري',
            ])
            ->columns(2)
            ->minItems(1) // ✅ الدالة الصحيحة في Filament v4
            ->required()
            ->helperText('يرجى تحديد وثيقة واحدة على الأقل للمتابعة.'),
    ])
    ->action(function (User $record, array $data) {
        $related = $record->user_type === 'clinic' ? $record->ownedClinic : $record->ownedLab;

        if (!$related) {
            Notification::make()->title('خطأ: لا توجد بيانات مرتبطة بالسجل')->danger()->send();
            return;
        }

        DB::transaction(function () use ($record, $related, $data) {
            // 1. تحديث حالة المستخدم
            $record->update(['user_status' => 'rejected']);

            // 2. حفظ سبب الرفض
            $related->update(['rejection_reason' => $data['rejection_reason']]);

            // 3. تحديث حالة الوثائق المحددة فقط
            if (in_array('license', $data['rejected_documents'] ?? [])) {
                $related->update(['license_status' => 'rejected']);
            }
            if (in_array('commercial_reg', $data['rejected_documents'] ?? [])) {
                $related->update(['commercial_reg_status' => 'rejected']);
            }
        });

        Notification::make()
            ->title('تم رفض الطلب وإرسال الملاحظات بنجاح')
            ->success()
            ->send();

        $this->dispatch('close-modal');
        $this->dispatch('refresh-table');
    }),
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('5s');
    }

   /**
 * ✅ الفورم الاحترافي باستخدام مكونات Filament
 */
private function getDetailsFormSchema(User $user): array
{
    $related = $user->user_type === 'clinic' ? $user->ownedClinic : $user->ownedLab;
    $typeName = $user->user_type === 'clinic' ? 'عيادة' : 'مختبر';

    return [
        // قسم المعلومات الأساسية
        Section::make("بيانات {$typeName}")
            ->description('المعلومات الأساسية للتسجيل')
            ->schema([
                Forms\Components\TextInput::make('relation_name')
                    ->label('الاسم')
                    ->default($related->name ?? '-')
                    ->disabled()
                    ->columnSpan(2),

                Forms\Components\TextInput::make('relation_phone')
                    ->label('الهاتف')
                    ->default($related->phone ?? '-')
                    ->disabled()
                    ->columnSpan(1),

                Forms\Components\TextInput::make('relation_location')
                    ->label('الموقع')
                    ->default($related->location ?? '-')
                    ->disabled()
                    ->columnSpanFull(),
            ])
            ->columns(3),

        // قسم التراخيص
        Section::make('التراخيص والتصاريح')
            ->description('معلومات الترخيص والسجل التجاري')
            ->schema([
                Forms\Components\TextInput::make('relation_license_number')
                    ->label('رقم الترخيص')
                    ->default($related->license_number ?? '-')
                    ->disabled()
                    ->columnSpan(2),


            ])
            ->columns(3),

        // قسم المرفقات
        Section::make('المرفقات والوثائق')
            ->description('يمكنك معاينة أو تحميل الوثائق المرفقة')
            ->schema([
                // ✅ رخصة الترخيص - مع زر المعاينة
                Forms\Components\FileUpload::make('license_file')
                    ->label('رخصة الترخيص')
                    ->disk('public')
                    ->directory('licenses')
                    ->downloadable()
                    ->openable()
                    ->disabled()
                    ->default($related->license ?? null)
                    ->visibility('public')
                    ->columnSpan(1),

                // ✅ السجل التجاري - مع زر المعاينة
                Forms\Components\FileUpload::make('commercial_reg_file')
                    ->label('السجل التجاري')
                    ->disk('public')
                    ->directory('commercial_regs')
                    ->downloadable()
                    ->openable()
                    ->disabled()
                    ->default($related->commercial_reg ?? null)
                    ->visibility('public')
                    ->columnSpan(1),
            ])
            ->columns(2),
    ];
}
    private function getRelated(User $user, string $field): mixed
    {
        $related = $user->user_type === 'clinic' ? $user->ownedClinic : $user->ownedLab;
        return $related?->{$field};
    }

    private function handleRequest(User $user, string $status): void
    {
        DB::transaction(function () use ($user, $status) {
            $user->update(['user_status' => $status]);

            $related = $user->user_type === 'clinic' ? $user->ownedClinic : $user->ownedLab;
            if ($related) {
                $related->update([
                    'license_status'        => $status,
                    'commercial_reg_status' => $status,
                ]);
            }
        });

        Notification::make()
            ->title($status === 'approved' ? 'تم قبول الطلب بنجاح' : 'تم رفض الطلب بنجاح')
            ->success()
            ->send();

        $this->dispatch('close-modal');
        $this->dispatch('refresh-table');
        $this->redirect(static::getUrl());
    }
}
