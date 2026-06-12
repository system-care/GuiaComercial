<?php

namespace App\Support;

use App\Models\Tenant;
use App\Support\GeoDistance;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicSearch
{
    public const PUBLIC_TENANT_LIMIT = 300;

    public static function normalize(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $value = Str::ascii($value);
        $value = Str::lower($value);
        $value = str_replace(['-', '_', '/', '\\'], ' ', $value);
        $value = preg_replace('/[^a-z0-9\s]+/', ' ', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return trim($value);
    }

    public static function terms(?string $value): array
    {
        $normalized = self::normalize($value);

        if ($normalized === '') {
            return [];
        }

        $slug = str_replace('-', ' ', Str::slug((string) $value));

        return self::uniqueTerms([
            $value,
            $normalized,
            $slug,
            ...(self::categorySynonyms(Str::slug((string) $value))),
        ]);
    }

    public static function categoryTerms(string $category): array
    {
        $slug = Str::slug($category);
        $normalizedSlug = str_replace('-', ' ', $slug);

        return self::uniqueTerms([
            $category,
            $slug,
            $normalizedSlug,
            ...self::categorySynonyms($slug),
        ]);
    }

    public static function categoryName(string $category): string
    {
        return match (Str::slug($category)) {
            // novos slugs (keys das categorias)
            'saude'        => 'Saúde e Bem-estar',
            'beleza'       => 'Beleza e Estética',
            'pets'         => 'Pets e Veterinária',
            'automotivo'   => 'Automotivo',
            'casa'         => 'Casa, Reformas e Manutenção',
            'eventos'      => 'Eventos, Festas e Alimentação',
            'profissional' => 'Educação e Serviços Profissionais',
            // slugs legados (compatibilidade)
            'estetica'              => 'Estética',
            'lava-jato'             => 'Lava Jato',
            'pet-shop'              => 'Pet Shop',
            'servicos-domiciliares' => 'Serviços Domiciliares',
            'clinicas'              => 'Clínicas',
            'saloes'                => 'Salões',
            'barbearias'            => 'Barbearias',
            'consultorias'          => 'Consultorias',
            default => Str::of($category)->replace('-', ' ')->title()->toString(),
        };
    }

    public static function cityTerms(string $city): array
    {
        $normalized = self::normalize($city);

        if ($normalized === '') {
            return [];
        }

        return self::uniqueTerms([
            $city,
            $normalized,
            str_replace('-', ' ', Str::slug($city)),
        ]);
    }

    public static function citySlug(string $city): string
    {
        return Str::slug($city);
    }

    public static function cityName(string $city, Collection $tenants): string
    {
        $terms = self::cityTerms($city);

        foreach (self::cityNames($tenants) as $name) {
            if (self::matches($terms, $name)) {
                return $name;
            }
        }

        return Str::of(str_replace('-', ' ', $city))->title()->toString();
    }

    public static function cityLinks(Collection $tenants, ?string $category = null, int $limit = 8): array
    {
        return collect(self::cityNames($tenants))
            ->take($limit)
            ->map(fn (string $city) => [
                'name' => $city,
                'slug' => self::citySlug($city),
                'url' => $category
                    ? route('public.category.city', ['category' => $category, 'city' => self::citySlug($city)])
                    : route('public.cities.show', self::citySlug($city)),
            ])
            ->values()
            ->all();
    }

    public static function geoParams(Request $request): array
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return [null, null];
        }

        $latF = (float) $lat;
        $lngF = (float) $lng;

        if ($latF < -90 || $latF > 90 || $lngF < -180 || $lngF > 180) {
            return [null, null];
        }

        return [$latF, $lngF];
    }

    public static function sortByDistance(Collection $tenants, float $userLat, float $userLng): Collection
    {
        return $tenants
            ->map(function (Tenant $tenant) use ($userLat, $userLng) {
                $lat = $tenant->settings?->latitude;
                $lng = $tenant->settings?->longitude;

                $tenant->distance_km = ($lat !== null && $lng !== null)
                    ? GeoDistance::haversineKm($userLat, $userLng, (float) $lat, (float) $lng)
                    : null;

                return $tenant;
            })
            ->sortBy(fn (Tenant $tenant) => $tenant->distance_km ?? PHP_FLOAT_MAX)
            ->values();
    }

    public static function tenantMatches(Tenant $tenant, Collection $services, array $terms): bool
    {
        return self::matches($terms, self::tenantSearchText($tenant, $services));
    }

    public static function tenantLocationMatches(Tenant $tenant, array $terms): bool
    {
        $settings = $tenant->settings?->settings ?? [];

        return self::matches($terms, implode(' ', array_filter([
            $tenant->city,
            $settings['city'] ?? null,
            $settings['neighborhood'] ?? null,
            $settings['bairro'] ?? null,
            $settings['address'] ?? null,
        ])));
    }

    private static function cityNames(Collection $tenants): array
    {
        return $tenants
            ->flatMap(function (Tenant $tenant) {
                $settings = $tenant->settings?->settings ?? [];

                return [
                    $tenant->city,
                    $settings['city'] ?? null,
                ];
            })
            ->filter(fn ($city) => is_string($city) && trim($city) !== '')
            ->unique(fn (string $city) => self::normalize($city))
            ->sortBy(fn (string $city) => self::normalize($city))
            ->values()
            ->all();
    }

    public static function matches(array $terms, string $haystack): bool
    {
        $normalizedHaystack = self::normalize($haystack);

        if ($normalizedHaystack === '') {
            return false;
        }

        foreach ($terms as $term) {
            $normalizedTerm = self::normalize($term);

            if ($normalizedTerm !== '' && str_contains($normalizedHaystack, $normalizedTerm)) {
                return true;
            }
        }

        return false;
    }

    private static function tenantSearchText(Tenant $tenant, Collection $services): string
    {
        $settings = $tenant->settings?->settings ?? [];

        return implode(' ', array_filter([
            $tenant->name,
            $tenant->city,
            $tenant->niche?->name,
            $tenant->niche?->key,
            self::flattenPublicSettings($settings),
            $services->pluck('name')->implode(' '),
            $services->pluck('description')->implode(' '),
        ]));
    }

    private static function flattenPublicSettings(array $settings): string
    {
        $publicKeys = [
            'description',
            'category',
            'neighborhood',
            'bairro',
            'city',
            'address',
            'public_phone',
            'attendance_modes',
            'service_modes',
        ];

        return collect($publicKeys)
            ->map(fn (string $key) => $settings[$key] ?? null)
            ->flatten()
            ->filter(fn ($value) => is_scalar($value))
            ->implode(' ');
    }

    private static function uniqueTerms(array $terms): array
    {
        return collect($terms)
            ->filter(fn ($term) => is_string($term) && trim($term) !== '')
            ->flatMap(fn (string $term) => [$term, self::normalize($term)])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private static function categorySynonyms(string $slug): array
    {
        $synonyms = [
            // ── novos slugs (keys das categorias) ────────────────────────────
            'saude' => [
                'saúde', 'saude', 'clínica', 'clinica', 'consultório', 'consultorio',
                'médico', 'medico', 'fisioterapia', 'psicólogo', 'psicologo', 'odontologia',
                'dentista', 'acupuntura', 'nutricionista', 'fonoaudiologia', 'oftalmologia',
                'dermatologia', 'cardiologia', 'ortopedia', 'ginecologia', 'pediatria',
                'endocrinologia', 'neurologia', 'psiquiatria', 'homeopatia', 'urologia',
            ],
            'beleza' => [
                'beleza', 'estética', 'estetica', 'salão', 'salao', 'barbearia', 'barba',
                'manicure', 'pedicure', 'sobrancelha', 'depilação', 'depilacao',
                'maquiagem', 'micropigmentação', 'micropigmentacao', 'cílios', 'cilios',
                'lash', 'nail', 'colorimetria', 'cabelo', 'spa', 'corte',
            ],
            'pets' => [
                'pet', 'pets', 'veterinário', 'veterinario', 'veterinária', 'veterinaria',
                'banho e tosa', 'tosa', 'adestramento', 'hotel pet', 'hospedagem pet',
            ],
            'automotivo' => [
                'automotivo', 'carro', 'veículo', 'veiculo', 'lava jato', 'lava-jato',
                'funilaria', 'pintura automotiva', 'mecânica', 'mecanica', 'troca de óleo',
                'alinhamento', 'balanceamento', 'vidros automotivos', 'elétrica automotiva',
                'eletrica automotiva', 'polimento', 'martelinho', 'estética automotiva',
                'estetica automotiva', 'car wash', 'despachante',
            ],
            'casa' => [
                'casa', 'residencial', 'limpeza', 'eletricista', 'encanador', 'encanamento',
                'marcenaria', 'pintura residencial', 'ar-condicionado', 'ar condicionado',
                'jardinagem', 'dedetização', 'dedetizacao', 'reforma', 'mudança', 'mudanca',
                'chaveiro', 'pedreiro', 'gesso', 'drywall', 'energia solar', 'portão',
                'portao', 'segurança eletrônica', 'seguranca eletronica',
            ],
            'eventos' => [
                'evento', 'eventos', 'festa', 'festas', 'casamento', 'fotografia',
                'buffet', 'gastronomia', 'decoração', 'decoracao', 'dj', 'sonorização',
                'sonorizacao', 'confeitaria', 'bolo', 'pizzaria', 'food truck',
                'bar', 'drinks', 'espaço para festas', 'espaco para festas',
            ],
            'profissional' => [
                'profissional', 'educação', 'educacao', 'advogado', 'contador',
                'psicólogo', 'psicologo', 'coach', 'personal trainer', 'personal',
                'professor', 'aula', 'idiomas', 'inglês', 'ingles', 'curso',
                'consultoria', 'ti', 'designer', 'marketing', 'administrador',
                'engenheiro', 'arquiteto', 'agência de viagens', 'corretor', 'seguro',
            ],
            // ── slugs legados (compatibilidade com links antigos) ─────────────
            'estetica'              => ['estética', 'estetica', 'beleza', 'estética facial', 'estetica facial', 'estética corporal', 'estetica corporal'],
            'clinicas'              => ['clínica', 'clinica', 'clínicas', 'clinicas', 'consultório', 'consultorio', 'saúde', 'saude'],
            'saloes'                => ['salão', 'salao', 'salões', 'saloes', 'beleza', 'cabelo'],
            'barbearias'            => ['barbearia', 'barba', 'corte', 'corte masculino'],
            'lava-jato'             => ['lava jato', 'lava-jato', 'car wash', 'lavagem', 'estética automotiva', 'estetica automotiva'],
            'pet-shop'              => ['pet shop', 'pet', 'banho e tosa', 'veterinário', 'veterinario'],
            'servicos-domiciliares' => ['domicílio', 'domicilio', 'domiciliar', 'atendimento domiciliar'],
            'consultorias'          => ['consultoria', 'consultor', 'assessoria'],
        ];

        return $synonyms[$slug] ?? [];
    }
}
