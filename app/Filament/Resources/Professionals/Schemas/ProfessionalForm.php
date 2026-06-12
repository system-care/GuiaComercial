<?php

namespace App\Filament\Resources\Professionals\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProfessionalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([

                // ── Coluna principal (2/3) ──────────────────────────────
                Grid::make()
                    ->columnSpan(['default' => 1, 'lg' => 2])
                    ->columns(2)
                    ->schema([
                        Section::make('Identificação')
                            ->columns(2)
                            ->schema([
                                Select::make('tenant_id')
                                    ->label('Empresa')
                                    ->relationship('tenant', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull()
                                    ->visible(fn () => auth()->user()?->isSuperAdmin()),
                                TextInput::make('name')
                                    ->label('Nome')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('specialty')
                                    ->label('Especialidade / Função')
                                    ->nullable(),
                                TextInput::make('email')
                                    ->label('E-mail')
                                    ->email()
                                    ->nullable(),
                                TextInput::make('phone')
                                    ->label('Telefone')
                                    ->tel()
                                    ->nullable(),
                            ]),

                        Section::make('Perfil público')
                            ->schema([
                                RichEditor::make('bio')
                                    ->label('Bio')
                                    ->nullable()
                                    ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link']),
                            ]),

                        Section::make('Redes sociais')
                            ->columns(2)
                            ->schema([
                                TextInput::make('social_links.instagram')
                                    ->label('Instagram')
                                    ->placeholder('https://instagram.com/usuario')
                                    ->nullable(),
                                TextInput::make('social_links.whatsapp')
                                    ->label('WhatsApp')
                                    ->placeholder('5511999999999')
                                    ->nullable(),
                                TextInput::make('social_links.linkedin')
                                    ->label('LinkedIn')
                                    ->placeholder('https://linkedin.com/in/usuario')
                                    ->nullable(),
                                TextInput::make('social_links.facebook')
                                    ->label('Facebook')
                                    ->placeholder('https://facebook.com/usuario')
                                    ->nullable(),
                                TextInput::make('social_links.tiktok')
                                    ->label('TikTok')
                                    ->placeholder('https://tiktok.com/@usuario')
                                    ->nullable(),
                                TextInput::make('social_links.website')
                                    ->label('Website')
                                    ->placeholder('https://seusite.com.br')
                                    ->nullable(),
                            ]),
                    ]),

                // ── Sidebar (1/3) ───────────────────────────────────────
                Grid::make()
                    ->columnSpan(['default' => 1, 'lg' => 1])
                    ->columns(1)
                    ->schema([
                        Section::make('Avatar')
                            ->schema([
                                FileUpload::make('avatar_path')
                                    ->label(false)
                                    ->image()
                                    ->disk('panel')
                                    ->directory('professionals')
                                    ->visibility('public')
                                    ->avatar()
                                    ->imagePreviewHeight('120')
                                    ->maxSize(4096)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                    ->helperText('Tamanho ideal: 400 × 400 px.')
                                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                                        Storage::disk('public')->makeDirectory('professionals');

                                        $src = imagecreatefromstring(file_get_contents($file->getRealPath()));

                                        $w = imagesx($src);
                                        $h = imagesy($src);
                                        if ($w > 400 || $h > 400) {
                                            $scale = 400 / max($w, $h);
                                            $src   = imagescale($src, (int) round($w * $scale), (int) round($h * $scale));
                                        }

                                        $filename = Str::uuid() . '.webp';
                                        $dest     = storage_path("app/public/professionals/{$filename}");
                                        imagewebp($src, $dest, 80);
                                        imagedestroy($src);

                                        return "professionals/{$filename}";
                                    })
                                    ->deleteUploadedFileUsing(function (string $file): void {
                                        Storage::disk('public')->delete($file);
                                    }),
                            ]),

                        Section::make('Configurações')
                            ->schema([
                                ColorPicker::make('color')
                                    ->label('Cor na agenda')
                                    ->default('#10B981'),
                                Toggle::make('active')
                                    ->label('Ativo')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
