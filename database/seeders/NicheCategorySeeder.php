<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NicheCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['key' => 'saude',      'name' => 'Saúde e Bem-estar',              'icon' => 'heroicon-o-heart',              'sort_order' => 1],
            ['key' => 'beleza',     'name' => 'Beleza e Estética',               'icon' => 'heroicon-o-sparkles',           'sort_order' => 2],
            ['key' => 'pets',       'name' => 'Pets e Veterinária',              'icon' => 'heroicon-o-face-smile',         'sort_order' => 3],
            ['key' => 'automotivo', 'name' => 'Automotivo',                      'icon' => 'heroicon-o-truck',              'sort_order' => 4],
            ['key' => 'casa',       'name' => 'Casa, Reformas e Manutenção',     'icon' => 'heroicon-o-home',               'sort_order' => 5],
            ['key' => 'eventos',    'name' => 'Eventos, Festas e Alimentação',   'icon' => 'heroicon-o-cake',               'sort_order' => 6],
            ['key' => 'profissional','name' => 'Educação e Serviços Profissionais','icon' => 'heroicon-o-briefcase',        'sort_order' => 7],
        ];

        foreach ($categories as $cat) {
            DB::table('niche_categories')->updateOrInsert(['key' => $cat['key']], array_merge($cat, [
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $catId = fn(string $key) => DB::table('niche_categories')->where('key', $key)->value('id');

        $niches = [
            // ── Saúde e Bem-estar ─────────────────────────────────────────
            ['key' => 'consultorio',            'name' => 'Clínicas e Consultórios',         'icon' => 'heroicon-o-heart',            'cat' => 'saude'],
            ['key' => 'psicologia',             'name' => 'Psicólogos',                      'icon' => 'heroicon-o-chat-bubble-left', 'cat' => 'saude'],
            ['key' => 'psiquiatria',            'name' => 'Psiquiatras',                     'icon' => 'heroicon-o-brain',            'cat' => 'saude'],
            ['key' => 'nutricao',               'name' => 'Nutricionistas',                  'icon' => 'heroicon-o-scale',            'cat' => 'saude'],
            ['key' => 'fisioterapia',           'name' => 'Fisioterapeutas',                 'icon' => 'heroicon-o-user',             'cat' => 'saude'],
            ['key' => 'odontologia',            'name' => 'Dentistas / Odontologia',         'icon' => 'heroicon-o-face-smile',       'cat' => 'saude'],
            ['key' => 'fonoaudiologia',         'name' => 'Fonoaudiólogos',                  'icon' => 'heroicon-o-megaphone',        'cat' => 'saude'],
            ['key' => 'terapia_ocupacional',    'name' => 'Terapia Ocupacional',             'icon' => 'heroicon-o-hand-raised',      'cat' => 'saude'],
            ['key' => 'podologia',              'name' => 'Podologia',                       'icon' => 'heroicon-o-star',             'cat' => 'saude'],
            ['key' => 'massoterapia',           'name' => 'Massoterapia',                    'icon' => 'heroicon-o-hand-raised',      'cat' => 'saude'],
            ['key' => 'acupuntura',             'name' => 'Acupuntura',                      'icon' => 'heroicon-o-bolt',             'cat' => 'saude'],
            ['key' => 'quiropraxia',            'name' => 'Quiropraxia',                     'icon' => 'heroicon-o-user',             'cat' => 'saude'],
            ['key' => 'osteopatia',             'name' => 'Osteopatia',                      'icon' => 'heroicon-o-user',             'cat' => 'saude'],
            ['key' => 'pilates',                'name' => 'Pilates',                         'icon' => 'heroicon-o-arrow-path',       'cat' => 'saude'],
            ['key' => 'personal_trainer',       'name' => 'Personal Trainer',                'icon' => 'heroicon-o-fire',             'cat' => 'saude'],
            ['key' => 'academia',               'name' => 'Academias e Studios Fitness',     'icon' => 'heroicon-o-fire',             'cat' => 'saude'],
            ['key' => 'terapias_integrativas',  'name' => 'Terapias Integrativas',           'icon' => 'heroicon-o-sparkles',         'cat' => 'saude'],
            ['key' => 'estetica_terapeutica',   'name' => 'Estética Terapêutica',            'icon' => 'heroicon-o-sparkles',         'cat' => 'saude'],
            ['key' => 'enfermagem_domiciliar',  'name' => 'Enfermagem Domiciliar',           'icon' => 'heroicon-o-home',             'cat' => 'saude'],
            ['key' => 'home_care',              'name' => 'Home Care / Cuidadores',          'icon' => 'heroicon-o-home',             'cat' => 'saude'],

            // ── Beleza e Estética ─────────────────────────────────────────
            ['key' => 'salao',                  'name' => 'Salões de Beleza',                'icon' => 'heroicon-o-scissors',         'cat' => 'beleza'],
            ['key' => 'barbearia',              'name' => 'Barbearias',                      'icon' => 'heroicon-o-scissors',         'cat' => 'beleza'],
            ['key' => 'esmalteria',             'name' => 'Esmalterias / Manicure e Pedicure','icon' => 'heroicon-o-sparkles',        'cat' => 'beleza'],
            ['key' => 'design_sobrancelha',     'name' => 'Design de Sobrancelhas',          'icon' => 'heroicon-o-eye',              'cat' => 'beleza'],
            ['key' => 'lash_design',            'name' => 'Lash Design / Extensão de Cílios','icon' => 'heroicon-o-eye',             'cat' => 'beleza'],
            ['key' => 'depilacao',              'name' => 'Depilação',                       'icon' => 'heroicon-o-sparkles',         'cat' => 'beleza'],
            ['key' => 'maquiagem',              'name' => 'Maquiagem Profissional',          'icon' => 'heroicon-o-sparkles',         'cat' => 'beleza'],
            ['key' => 'micropigmentacao',       'name' => 'Micropigmentação',               'icon' => 'heroicon-o-pencil',           'cat' => 'beleza'],
            ['key' => 'estetica_facial',        'name' => 'Estética Facial',                'icon' => 'heroicon-o-face-smile',       'cat' => 'beleza'],
            ['key' => 'estetica_corporal',      'name' => 'Estética Corporal',              'icon' => 'heroicon-o-user',             'cat' => 'beleza'],
            ['key' => 'bronzeamento',           'name' => 'Bronzeamento',                   'icon' => 'heroicon-o-sun',              'cat' => 'beleza'],
            ['key' => 'spa',                    'name' => 'Spa Urbano',                     'icon' => 'heroicon-o-sparkles',         'cat' => 'beleza'],
            ['key' => 'trancistas',             'name' => 'Trancistas',                     'icon' => 'heroicon-o-scissors',         'cat' => 'beleza'],
            ['key' => 'cabeleireiro',           'name' => 'Cabeleireiros Especializados',   'icon' => 'heroicon-o-scissors',         'cat' => 'beleza'],
            ['key' => 'consultoria_imagem',     'name' => 'Consultoria de Imagem',          'icon' => 'heroicon-o-user',             'cat' => 'beleza'],

            // ── Pets e Veterinária ────────────────────────────────────────
            ['key' => 'veterinaria',            'name' => 'Clínicas Veterinárias',          'icon' => 'heroicon-o-heart',            'cat' => 'pets'],
            ['key' => 'pet_shop',               'name' => 'Pet Shop',                       'icon' => 'heroicon-o-shopping-bag',     'cat' => 'pets'],
            ['key' => 'banho_tosa',             'name' => 'Banho e Tosa',                   'icon' => 'heroicon-o-sparkles',         'cat' => 'pets'],
            ['key' => 'adestramento',           'name' => 'Adestradores',                   'icon' => 'heroicon-o-academic-cap',     'cat' => 'pets'],
            ['key' => 'passeador_caes',         'name' => 'Passeadores de Cães',            'icon' => 'heroicon-o-map',              'cat' => 'pets'],
            ['key' => 'creche_pet',             'name' => 'Creche Pet',                     'icon' => 'heroicon-o-home',             'cat' => 'pets'],
            ['key' => 'hotel_pet',              'name' => 'Hotel para Pets',                'icon' => 'heroicon-o-home',             'cat' => 'pets'],
            ['key' => 'vet_domiciliar',         'name' => 'Atendimento Veterinário Domiciliar','icon' => 'heroicon-o-home',          'cat' => 'pets'],
            ['key' => 'taxi_pet',               'name' => 'Táxi Pet',                       'icon' => 'heroicon-o-truck',            'cat' => 'pets'],
            ['key' => 'fotografia_pet',         'name' => 'Fotografia Pet',                 'icon' => 'heroicon-o-camera',           'cat' => 'pets'],

            // ── Automotivo ────────────────────────────────────────────────
            ['key' => 'car_wash',               'name' => 'Lava-Jato',                      'icon' => 'heroicon-o-truck',            'cat' => 'automotivo'],
            ['key' => 'estetica_automotiva',    'name' => 'Estética Automotiva',            'icon' => 'heroicon-o-sparkles',         'cat' => 'automotivo'],
            ['key' => 'higienizacao_interna',   'name' => 'Higienização Interna',           'icon' => 'heroicon-o-sparkles',         'cat' => 'automotivo'],
            ['key' => 'polimento',              'name' => 'Polimento Automotivo',           'icon' => 'heroicon-o-star',             'cat' => 'automotivo'],
            ['key' => 'mecanica',               'name' => 'Oficinas Mecânicas',             'icon' => 'heroicon-o-wrench',           'cat' => 'automotivo'],
            ['key' => 'borracharia',            'name' => 'Borracharias',                   'icon' => 'heroicon-o-wrench',           'cat' => 'automotivo'],
            ['key' => 'troca_oleo',             'name' => 'Troca de Óleo',                  'icon' => 'heroicon-o-arrow-path',       'cat' => 'automotivo'],
            ['key' => 'eletrica_automotiva',    'name' => 'Elétrica Automotiva',            'icon' => 'heroicon-o-bolt',             'cat' => 'automotivo'],
            ['key' => 'funilaria',              'name' => 'Funilaria e Pintura',            'icon' => 'heroicon-o-paint-brush',      'cat' => 'automotivo'],
            ['key' => 'martelinho_ouro',        'name' => 'Martelinho de Ouro',             'icon' => 'heroicon-o-wrench',           'cat' => 'automotivo'],
            ['key' => 'guincho',                'name' => 'Guincho / Reboque',              'icon' => 'heroicon-o-truck',            'cat' => 'automotivo'],
            ['key' => 'som_acessorios',         'name' => 'Som e Acessórios Automotivos',   'icon' => 'heroicon-o-musical-note',     'cat' => 'automotivo'],
            ['key' => 'insulfilm',              'name' => 'Insulfilm Automotivo',           'icon' => 'heroicon-o-square-2-stack',   'cat' => 'automotivo'],
            ['key' => 'vistoria_veicular',      'name' => 'Vistoria Veicular',              'icon' => 'heroicon-o-clipboard',        'cat' => 'automotivo'],
            ['key' => 'chaveiro_auto',          'name' => 'Chaveiro Automotivo',            'icon' => 'heroicon-o-key',              'cat' => 'automotivo'],

            // ── Casa, Reformas e Manutenção ───────────────────────────────
            ['key' => 'diarista',               'name' => 'Diaristas e Faxineiras',         'icon' => 'heroicon-o-home',             'cat' => 'casa'],
            ['key' => 'limpeza_pos_obra',        'name' => 'Limpeza Pós-obra',              'icon' => 'heroicon-o-home',             'cat' => 'casa'],
            ['key' => 'eletricista',            'name' => 'Eletricistas',                   'icon' => 'heroicon-o-bolt',             'cat' => 'casa'],
            ['key' => 'encanador',              'name' => 'Encanadores',                    'icon' => 'heroicon-o-wrench',           'cat' => 'casa'],
            ['key' => 'pintor',                 'name' => 'Pintores',                       'icon' => 'heroicon-o-paint-brush',      'cat' => 'casa'],
            ['key' => 'pedreiro',               'name' => 'Pedreiros e Reformas',           'icon' => 'heroicon-o-home',             'cat' => 'casa'],
            ['key' => 'gesseiro',               'name' => 'Gesseiros / Drywall',            'icon' => 'heroicon-o-home',             'cat' => 'casa'],
            ['key' => 'marceneiro',             'name' => 'Marceneiros',                    'icon' => 'heroicon-o-wrench',           'cat' => 'casa'],
            ['key' => 'montador_moveis',        'name' => 'Montadores de Móveis',           'icon' => 'heroicon-o-wrench',           'cat' => 'casa'],
            ['key' => 'jardinagem',             'name' => 'Jardinagem',                     'icon' => 'heroicon-o-sun',              'cat' => 'casa'],
            ['key' => 'dedetizacao',            'name' => 'Dedetização',                    'icon' => 'heroicon-o-shield-check',     'cat' => 'casa'],
            ['key' => 'limpeza_caixa_dagua',    'name' => 'Limpeza de Caixa d\'Água',      'icon' => 'heroicon-o-beaker',           'cat' => 'casa'],
            ['key' => 'ar_condicionado',        'name' => 'Ar-condicionado',               'icon' => 'heroicon-o-wind',             'cat' => 'casa'],
            ['key' => 'cameras_seguranca',      'name' => 'Câmeras de Segurança',          'icon' => 'heroicon-o-camera',           'cat' => 'casa'],
            ['key' => 'seguranca_eletronica',   'name' => 'Segurança Eletrônica',          'icon' => 'heroicon-o-shield-check',     'cat' => 'casa'],
            ['key' => 'serralheria',            'name' => 'Serralheria',                    'icon' => 'heroicon-o-wrench',           'cat' => 'casa'],
            ['key' => 'vidracaria',             'name' => 'Vidraçaria',                     'icon' => 'heroicon-o-squares-2x2',      'cat' => 'casa'],
            ['key' => 'chaveiro',               'name' => 'Chaveiros',                      'icon' => 'heroicon-o-key',              'cat' => 'casa'],
            ['key' => 'piscineiro',             'name' => 'Piscineiros',                    'icon' => 'heroicon-o-beaker',           'cat' => 'casa'],
            ['key' => 'energia_solar',          'name' => 'Energia Solar',                 'icon' => 'heroicon-o-sun',              'cat' => 'casa'],

            // ── Eventos, Festas e Alimentação ─────────────────────────────
            ['key' => 'fotografo',              'name' => 'Fotógrafos',                     'icon' => 'heroicon-o-camera',           'cat' => 'eventos'],
            ['key' => 'videomaker',             'name' => 'Filmagem / Videomaker',          'icon' => 'heroicon-o-video-camera',     'cat' => 'eventos'],
            ['key' => 'buffet',                 'name' => 'Buffet para Festas',             'icon' => 'heroicon-o-cake',             'cat' => 'eventos'],
            ['key' => 'decoracao_festas',       'name' => 'Decoração de Festas',            'icon' => 'heroicon-o-sparkles',         'cat' => 'eventos'],
            ['key' => 'cerimonialista',         'name' => 'Cerimonialista / Assessoria',    'icon' => 'heroicon-o-clipboard',        'cat' => 'eventos'],
            ['key' => 'dj',                     'name' => 'DJ',                             'icon' => 'heroicon-o-musical-note',     'cat' => 'eventos'],
            ['key' => 'som_iluminacao',         'name' => 'Som e Iluminação',              'icon' => 'heroicon-o-bolt',             'cat' => 'eventos'],
            ['key' => 'aluguel_brinquedos',     'name' => 'Aluguel de Brinquedos',         'icon' => 'heroicon-o-gift',             'cat' => 'eventos'],
            ['key' => 'recreacao_infantil',     'name' => 'Recreação Infantil',            'icon' => 'heroicon-o-face-smile',       'cat' => 'eventos'],
            ['key' => 'churrasqueiro',          'name' => 'Churrasqueiro',                 'icon' => 'heroicon-o-fire',             'cat' => 'eventos'],
            ['key' => 'personal_chef',          'name' => 'Personal Chef',                 'icon' => 'heroicon-o-cake',             'cat' => 'eventos'],
            ['key' => 'confeitaria',            'name' => 'Confeitaria / Bolos Personalizados','icon' => 'heroicon-o-cake',         'cat' => 'eventos'],
            ['key' => 'doces_personalizados',   'name' => 'Doces Personalizados',          'icon' => 'heroicon-o-cake',             'cat' => 'eventos'],
            ['key' => 'bartender',              'name' => 'Bartender para Eventos',        'icon' => 'heroicon-o-beaker',           'cat' => 'eventos'],
            ['key' => 'espaco_eventos',         'name' => 'Espaços para Eventos',          'icon' => 'heroicon-o-building-office',  'cat' => 'eventos'],

            // ── Educação e Serviços Profissionais ─────────────────────────
            ['key' => 'aulas_particulares',     'name' => 'Aulas Particulares / Reforço',  'icon' => 'heroicon-o-academic-cap',     'cat' => 'profissional'],
            ['key' => 'idiomas',                'name' => 'Cursos de Idiomas',             'icon' => 'heroicon-o-language',         'cat' => 'profissional'],
            ['key' => 'contabilidade',          'name' => 'Contadores',                    'icon' => 'heroicon-o-calculator',       'cat' => 'profissional'],
            ['key' => 'advocacia',              'name' => 'Advogados',                     'icon' => 'heroicon-o-scale',            'cat' => 'profissional'],
            ['key' => 'marketing_digital',      'name' => 'Marketing Digital / Social Media','icon' => 'heroicon-o-megaphone',       'cat' => 'profissional'],
            ['key' => 'desenvolvimento_web',    'name' => 'Desenvolvimento de Sites e Sistemas','icon' => 'heroicon-o-code-bracket','cat' => 'profissional'],
            ['key' => 'design_grafico',         'name' => 'Design Gráfico',               'icon' => 'heroicon-o-paint-brush',      'cat' => 'profissional'],
            ['key' => 'trafego_pago',           'name' => 'Tráfego Pago',                 'icon' => 'heroicon-o-arrow-trending-up','cat' => 'profissional'],
            ['key' => 'consultoria_empresarial','name' => 'Consultoria Empresarial',       'icon' => 'heroicon-o-briefcase',        'cat' => 'profissional'],
            ['key' => 'consultoria_financeira', 'name' => 'Consultoria Financeira',        'icon' => 'heroicon-o-banknotes',        'cat' => 'profissional'],
            ['key' => 'arquitetura',            'name' => 'Arquitetos',                    'icon' => 'heroicon-o-building-office',  'cat' => 'profissional'],
            ['key' => 'engenharia',             'name' => 'Engenheiros',                   'icon' => 'heroicon-o-wrench',           'cat' => 'profissional'],
            ['key' => 'despachante',            'name' => 'Despachantes',                  'icon' => 'heroicon-o-clipboard',        'cat' => 'profissional'],
            ['key' => 'imobiliaria',            'name' => 'Imobiliárias / Corretores',     'icon' => 'heroicon-o-home',             'cat' => 'profissional'],
            ['key' => 'lavanderia',             'name' => 'Lavanderias',                   'icon' => 'heroicon-o-sparkles',         'cat' => 'profissional'],
            ['key' => 'costureira',             'name' => 'Costureiras',                   'icon' => 'heroicon-o-scissors',         'cat' => 'profissional'],
            ['key' => 'conserto_roupas',        'name' => 'Conserto de Roupas',            'icon' => 'heroicon-o-scissors',         'cat' => 'profissional'],
            ['key' => 'conserto_calcados',      'name' => 'Conserto de Calçados',          'icon' => 'heroicon-o-wrench',           'cat' => 'profissional'],
            ['key' => 'assistencia_celular',    'name' => 'Assistência Técnica de Celular','icon' => 'heroicon-o-device-phone-mobile','cat' => 'profissional'],
            ['key' => 'assistencia_informatica','name' => 'Assistência Técnica de Informática','icon' => 'heroicon-o-computer-desktop','cat' => 'profissional'],
            ['key' => 'manutencao_notebook',    'name' => 'Manutenção de Notebook',        'icon' => 'heroicon-o-computer-desktop', 'cat' => 'profissional'],
            ['key' => 'instalacao_internet',    'name' => 'Instalação de Internet/Rede',   'icon' => 'heroicon-o-wifi',             'cat' => 'profissional'],
            ['key' => 'motoboy',                'name' => 'Motoboy / Entregas Rápidas',    'icon' => 'heroicon-o-truck',            'cat' => 'profissional'],
            ['key' => 'mudancas_fretes',        'name' => 'Mudanças e Fretes',             'icon' => 'heroicon-o-truck',            'cat' => 'profissional'],
            ['key' => 'transporte_executivo',   'name' => 'Transportes Executivos',        'icon' => 'heroicon-o-truck',            'cat' => 'profissional'],
        ];

        foreach ($niches as $niche) {
            $catKey  = $niche['cat'];
            $catIdVal = $catId($catKey);

            DB::table('business_niches')->updateOrInsert(
                ['key' => $niche['key']],
                [
                    'niche_category_id' => $catIdVal,
                    'name'              => $niche['name'],
                    'icon'              => $niche['icon'],
                    'active'            => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]
            );
        }
    }
}
