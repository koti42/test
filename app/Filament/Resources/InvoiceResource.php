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
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün Kodu</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün Adı</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Miktar</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Birim</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Birim Fiyat</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KDV</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toplam</th>
                                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">1</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">PRD-001</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">iPhone 14 Pro</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Elektronik</span>
                                                                </td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">2</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">Adet</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">52,499.00 ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">18,899.64 ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">123,897.64 ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">
                                                                    <div class="flex items-center space-x-2">
                                                                        <button class="text-blue-600 hover:text-blue-800">
                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                            </svg>
                                                                        </button>
                                                                        <button class="text-red-600 hover:text-red-800">
                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">2</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">PRD-002</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">MacBook Pro M2</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Elektronik</span>
                                                                </td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">1</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">Adet</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">72,999.00 ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">13,139.82 ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">86,138.82 ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">
                                                                    <div class="flex items-center space-x-2">
                                                                        <button class="text-blue-600 hover:text-blue-800">
                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                            </svg>
                                                                        </button>
                                                                        <button class="text-red-600 hover:text-red-800">
                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">3</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">PRD-003</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">Nike Air Max</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Giyim</span>
                                                                </td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">2</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">Adet</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">2,499.00 ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">899.64 ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">5,897.64 ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">
                                                                    <div class="flex items-center space-x-2">
                                                                        <button class="text-blue-600 hover:text-blue-800">
                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                            </svg>
                                                                        </button>
                                                                        <button class="text-red-600 hover:text-red-800">
                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">4</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">PRD-004</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">Organik Zeytinyağı</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Gıda</span>
                                                                </td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">5</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">Lt</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">399.90 ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">359.91 ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">2,359.41 ' . $record->currency . '</td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-600">
                                                                    <div class="flex items-center space-x-2">
                                                                        <button class="text-blue-600 hover:text-blue-800">
                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                            </svg>
                                                                        </button>
                                                                        <button class="text-red-600 hover:text-red-800">
                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                        <tfoot class="bg-gray-50">
                                                            <tr>
                                                                <td colspan="6" class="px-3 py-3"></td>
                                                                <td class="px-3 py-3 text-sm font-medium text-gray-700">Ara Toplam:</td>
                                                                <td colspan="2" class="px-3 py-3 text-sm text-gray-900">' . number_format(128396.90, 2) . ' ' . $record->currency . '</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="6" class="px-3 py-3"></td>
                                                                <td class="px-3 py-3 text-sm font-medium text-gray-700">KDV:</td>
                                                                <td colspan="2" class="px-3 py-3 text-sm text-gray-900">' . number_format(33199.01, 2) . ' ' . $record->currency . '</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="6" class="px-3 py-3"></td>
                                                                <td class="px-3 py-3 text-sm font-medium text-gray-900">Genel Toplam:</td>
                                                                <td colspan="2" class="px-3 py-3 text-sm font-bold text-gray-900">' . number_format(218293.51, 2) . ' ' . $record->currency . '</td>
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
                                <div x-show="showProductModal" 
                                    class="fixed inset-0 z-[60] overflow-y-auto"
                                    x-cloak
                                    style="display: none;">
                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showProductModal = false"></div>

                                        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full">
                                            <div class="bg-white px-6 pt-6 pb-6">
                                                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                                                    <h3 class="text-xl font-semibold text-gray-900">
                                                        Yeni Ürün Ekle
                                                    </h3>
                                                    <button @click="showProductModal = false" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                                        <span class="sr-only">Kapat</span>
                                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <form @submit.prevent="$event.preventDefault()">
                                                    <div class="grid grid-cols-2 gap-6">
                                                        <div class="space-y-6">
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">Ürün Adı</label>
                                                                <input type="text" name="product_name" required placeholder="Ürün adını giriniz" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">Ürün Kodu</label>
                                                                <input type="text" name="product_code" placeholder="Ürün kodunu giriniz" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">Barkod</label>
                                                                <input type="text" name="barcode" placeholder="Barkod numarasını giriniz" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                                                <select name="category" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                                                    <option value="">Kategori seçiniz</option>
                                                                    <option value="elektronik">Elektronik</option>
                                                                    <option value="giyim">Giyim</option>
                                                                    <option value="gida">Gıda</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="space-y-6">
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">Miktar</label>
                                                                <input type="number" name="quantity" required min="1" placeholder="Miktar giriniz" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">Birim</label>
                                                                <select name="unit" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                                                    <option value="adet">Adet</option>
                                                                    <option value="kg">KG</option>
                                                                    <option value="lt">LT</option>
                                                                    <option value="mt">MT</option>
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">Birim Fiyat</label>
                                                                <input type="number" name="unit_price" step="0.01" required min="0" placeholder="Birim fiyat giriniz" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">KDV Oranı (%)</label>
                                                                <select name="tax_rate" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                                                                    <option value="0">0%</option>
                                                                    <option value="1">1%</option>
                                                                    <option value="8">8%</option>
                                                                    <option value="18" selected>18%</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-12 border-t border-gray-100 pt-6">
                                                        <div class="flex justify-start space-x-4">
                                                            <button type="submit" class="w-32 inline-flex justify-center rounded-lg border border-transparent bg-primary-600 px-4 py-3 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                                                Kaydet
                                                            </button>
                                                            <button type="button" @click="showProductModal = false" class="w-32 inline-flex justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                                                İptal
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
