<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\HtmlString;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Faturalar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->label('Ad'),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->label('Soyad'),
                Forms\Components\TextInput::make('phone')
                    ->required()
                    ->label('Telefon'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->label('E-posta'),
                Forms\Components\Select::make('status')
                    ->options([
                        'Bekliyor' => 'Bekliyor',
                        'Hazırlanıyor' => 'Hazırlanıyor',
                        'Kargoda' => 'Kargoda',
                        'Tamamlandı' => 'Tamamlandı'
                    ])
                    ->default('Bekliyor')
                    ->label('Durum'),
                Forms\Components\TextInput::make('shipping_no')
                    ->label('Sevkiyat No'),
                Forms\Components\TextInput::make('warehouse')
                    ->label('Depo'),
                Forms\Components\TextInput::make('shipping_region')
                    ->label('Sevkiyat Bölgesi'),
                Forms\Components\Textarea::make('address')
                    ->label('Adres'),
                Forms\Components\DatePicker::make('shipping_date')
                    ->label('Sevkiyat Tarihi'),
                Forms\Components\TextInput::make('total_amount')
                    ->numeric()
                    ->label('Toplam Tutar'),
                Forms\Components\TextInput::make('kdv_amount')
                    ->numeric()
                    ->label('KDV Tutarı'),
                Forms\Components\Select::make('currency')
                    ->options([
                        'TL' => 'TL',
                        'USD' => 'USD',
                        'EUR' => 'EUR'
                    ])
                    ->default('TL')
                    ->label('Para Birimi'),
                Forms\Components\Select::make('membership_status')
                    ->options([
                        'Üyeliksiz' => 'Üyeliksiz',
                        'Üye' => 'Üye',
                        'VIP' => 'VIP'
                    ])
                    ->default('Üyeliksiz')
                    ->label('Üyelik Durumu'),
                Forms\Components\FileUpload::make('pdf_path')
                    ->required()
                    ->label('Fatura PDF')
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('invoices'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shipping_no')
                    ->label('Sevkiyat No')
                    ->searchable(),
                Tables\Columns\TextColumn::make('shipping_region')
                    ->label('Bölge')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Bekliyor' => 'warning',
                        'Hazırlanıyor' => 'info',
                        'Kargoda' => 'primary',
                        'Tamamlandı' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('view_invoice')
                    ->label('Detayları Göster')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detayları Göster')
                    ->modalContent(function (Invoice $record): HtmlString {
                        return new HtmlString('
                            <div 
                                x-data="{ 
                                    activeTab: \'sales\', 
                                    showProductModal: false,
                                    init() {
                                        this.$watch(\'showProductModal\', (value) => {
                                            if (value) {
                                                document.body.style.overflow = \'hidden\';
                                            } else {
                                                document.body.style.overflow = \'auto\';
                                            }
                                        });
                                    }
                                }" 
                                @keydown.escape="showProductModal = false"
                                class="relative space-y-4"
                            >
                                <nav class="flex space-x-4 border-b border-gray-200 pb-4 mb-6">
                                    <button 
                                        type="button"
                                        @click.prevent="activeTab = \'sales\'" 
                                        :class="{ \'bg-primary-600 text-white shadow-lg transform scale-105\': activeTab === \'sales\', \'bg-white text-gray-600 hover:bg-gray-50\': activeTab !== \'sales\' }"
                                        class="px-6 py-3 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2 focus:outline-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                        <span>Satış Bilgileri</span>
                                    </button>
                                    <button 
                                        type="button"
                                        @click.prevent="activeTab = \'shipping\'" 
                                        :class="{ \'bg-primary-600 text-white shadow-lg transform scale-105\': activeTab === \'shipping\', \'bg-white text-gray-600 hover:bg-gray-50\': activeTab !== \'shipping\' }"
                                        class="px-6 py-3 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2 focus:outline-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                        </svg>
                                        <span>Sevkiyat Bilgisi</span>
                                    </button>
                                </nav>
                                
                                <div x-cloak x-show="activeTab === \'sales\'" 
                                    x-transition:enter="transition ease-out duration-300" 
                                    x-transition:enter-start="opacity-0 transform -translate-x-2" 
                                    x-transition:enter-end="opacity-100 transform translate-x-0">
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-6">
                                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                                                <div class="flex items-center space-x-3 mb-4">
                                                    <div class="p-2 bg-primary-50 rounded-lg">
                                                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </div>
                                                    <h3 class="text-lg font-semibold text-gray-800">Durum</h3>
                                                </div>
                                                <div class="pl-11">
                                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium ' . match($record->status) {
                                                        "Bekliyor" => "bg-yellow-100 text-yellow-800",
                                                        "Hazırlanıyor" => "bg-blue-100 text-blue-800",
                                                        "Kargoda" => "bg-indigo-100 text-indigo-800",
                                                        "Tamamlandı" => "bg-green-100 text-green-800",
                                                        default => "bg-gray-100 text-gray-800",
                                                    } . '">
                                                        ' . $record->status . '
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                                                <div class="flex items-center space-x-3 mb-4">
                                                    <div class="p-2 bg-primary-50 rounded-lg">
                                                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                    </div>
                                                    <h3 class="text-lg font-semibold text-gray-800">Müşteri Bilgileri</h3>
                                                </div>
                                                <div class="pl-11 space-y-3">
                                                    <div class="flex items-center text-gray-600">
                                                        <span class="font-medium w-24">Ad Soyad:</span>
                                                        <span>' . $record->first_name . ' ' . $record->last_name . '</span>
                                                    </div>
                                                    <div class="flex items-center text-gray-600">
                                                        <span class="font-medium w-24">Telefon:</span>
                                                        <span>' . $record->phone . '</span>
                                                    </div>
                                                    <div class="flex items-center text-gray-600">
                                                        <span class="font-medium w-24">E-posta:</span>
                                                        <span>' . $record->email . '</span>
                                                    </div>
                                                    <div class="flex items-center text-gray-600">
                                                        <span class="font-medium w-24">Üyelik:</span>
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ' . match($record->membership_status) {
                                                            "VIP" => "bg-purple-100 text-purple-800",
                                                            "Üye" => "bg-blue-100 text-blue-800",
                                                            default => "bg-gray-100 text-gray-800",
                                                        } . '">
                                                            ' . $record->membership_status . '
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                                                <div class="flex items-center justify-between mb-4">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="p-2 bg-primary-50 rounded-lg">
                                                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                            </svg>
                                                        </div>
                                                        <h3 class="text-lg font-semibold text-gray-800">Ürün Detayları</h3>
                                                    </div>
                                                    <button 
                                                        @click="showProductModal = true" 
                                                        class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200">
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                        </svg>
                                                        Ürün Ekle
                                                    </button>
                                                </div>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200">
                                                        <thead>
                                                            <tr>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün ID</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün Adı</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Miktar</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Birim Fiyat</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KDV</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toplam</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">1</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">Örnek Ürün</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">1</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">' . number_format($record->total_amount, 2) . ' ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">' . number_format($record->kdv_amount, 2) . ' ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">' . number_format($record->total_amount + $record->kdv_amount, 2) . ' ' . $record->currency . '</td>
                                                            </tr>
                                                        </tbody>
                                                        <tfoot class="bg-gray-50">
                                                            <tr>
                                                                <td colspan="4" class="px-3 py-3"></td>
                                                                <td class="px-3 py-3 text-sm font-medium text-gray-700">Ara Toplam:</td>
                                                                <td class="px-3 py-3 text-sm text-gray-900">' . number_format($record->total_amount, 2) . ' ' . $record->currency . '</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="4" class="px-3 py-3"></td>
                                                                <td class="px-3 py-3 text-sm font-medium text-gray-700">KDV:</td>
                                                                <td class="px-3 py-3 text-sm text-gray-900">' . number_format($record->kdv_amount, 2) . ' ' . $record->currency . '</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="4" class="px-3 py-3"></td>
                                                                <td class="px-3 py-3 text-sm font-medium text-gray-900">Genel Toplam:</td>
                                                                <td class="px-3 py-3 text-sm font-bold text-gray-900">' . number_format($record->total_amount + $record->kdv_amount, 2) . ' ' . $record->currency . '</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                                            <div class="flex items-center space-x-3 mb-4">
                                                <div class="p-2 bg-primary-50 rounded-lg">
                                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </div>
                                                <h3 class="text-lg font-semibold text-gray-800">Fatura PDF</h3>
                                            </div>
                                            <iframe src="' . asset('storage/' . $record->pdf_path) . '" width="100%" height="600px" class="rounded-lg border-0"></iframe>
                                        </div>
                                    </div>
                                </div>
                                
                                <div x-cloak x-show="activeTab === \'shipping\'"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 transform translate-x-2"
                                    x-transition:enter-end="opacity-100 transform translate-x-0">
                                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                                        <div class="flex items-center space-x-3 mb-6">
                                            <div class="p-2 bg-primary-50 rounded-lg">
                                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-800">Sevkiyat Detayı</h3>
                                        </div>
                                        <div class="grid grid-cols-2 gap-6 pl-11">
                                            <div class="space-y-4">
                                                <div class="bg-gray-50 rounded-xl p-4">
                                                    <p class="text-sm text-gray-500 mb-1">Sevkiyat No</p>
                                                    <p class="text-gray-900 font-medium">' . $record->shipping_no . '</p>
                                                </div>
                                                <div class="bg-gray-50 rounded-xl p-4">
                                                    <p class="text-sm text-gray-500 mb-1">Depo</p>
                                                    <p class="text-gray-900 font-medium">' . $record->warehouse . '</p>
                                                </div>
                                                <div class="bg-gray-50 rounded-xl p-4">
                                                    <p class="text-sm text-gray-500 mb-1">Sevkiyat Bölgesi</p>
                                                    <p class="text-gray-900 font-medium">' . $record->shipping_region . '</p>
                                                </div>
                                            </div>
                                            <div class="space-y-4">
                                                <div class="bg-gray-50 rounded-xl p-4">
                                                    <p class="text-sm text-gray-500 mb-1">Planlanan Sevk Tarihi</p>
                                                    <p class="text-gray-900 font-medium">' . $record->shipping_date . '</p>
                                                </div>
                                                <div class="bg-gray-50 rounded-xl p-4">
                                                    <p class="text-sm text-gray-500 mb-1">Teslimat Adresi</p>
                                                    <p class="text-gray-900 font-medium">' . $record->address . '</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ürün Ekleme Modalı -->
                                <template x-teleport="body">
                                    <div x-show="showProductModal"
                                        x-trap.noscroll="showProductModal"
                                        class="fixed inset-0 z-[999] overflow-y-auto"
                                        @click.self="showProductModal = false">
                                        <div class="min-h-screen px-4 text-center">
                                            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                                                x-show="showProductModal"
                                                x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0">
                                            </div>

                                            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full"
                                                x-show="showProductModal"
                                                @click.outside="showProductModal = false"
                                                x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave="ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                                                <div class="bg-white p-6">
                                                    <div class="flex items-center justify-between mb-6">
                                                        <h3 class="text-lg font-semibold text-gray-800">Yeni Ürün Ekle</h3>
                                                        <button @click.prevent="showProductModal = false" type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <form @submit.prevent="saveProduct">
                                                        <div class="space-y-4">
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">Ürün Adı</label>
                                                                <input type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">Miktar</label>
                                                                <input type="number" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">Birim Fiyat</label>
                                                                <input type="number" step="0.01" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                                            </div>
                                                            <div class="flex justify-end space-x-3 mt-6">
                                                                <button type="button" @click.prevent="showProductModal = false" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                                                    İptal
                                                                </button>
                                                                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                                                    Kaydet
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                
                        ');
                    })
                    ->modalWidth('7xl'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
