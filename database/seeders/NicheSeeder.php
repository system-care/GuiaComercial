<?php

namespace Database\Seeders;

use App\Models\BusinessNiche;
use App\Models\NicheTemplate;
use Illuminate\Database\Seeder;

class NicheSeeder extends Seeder
{
    public function run(): void
    {
        $niches = [
            [
                'key'  => 'consultorio',
                'name' => 'Consultório',
                'icon' => 'heroicon-o-heart',
                'template' => [
                    'labels' => [
                        'customer'    => 'Paciente',
                        'appointment' => 'Consulta',
                        'service'     => 'Procedimento',
                        'resource'    => 'Profissional',
                    ],
                    'custom_fields' => [
                        ['entity' => 'customer', 'name' => 'cpf',         'label' => 'CPF',              'type' => 'text',   'required' => false],
                        ['entity' => 'customer', 'name' => 'birth_date',  'label' => 'Data de Nascimento','type' => 'date',   'required' => false],
                        ['entity' => 'customer', 'name' => 'convenio',    'label' => 'Convênio',          'type' => 'text',   'required' => false],
                        ['entity' => 'customer', 'name' => 'observation', 'label' => 'Observações',       'type' => 'textarea','required' => false],
                    ],
                    'default_statuses' => ['Agendado', 'Confirmado', 'Em Atendimento', 'Atendido', 'Faltou', 'Cancelado'],
                    'default_services' => [
                        ['name' => 'Consulta Inicial',  'duration_minutes' => 60],
                        ['name' => 'Retorno',           'duration_minutes' => 30],
                        ['name' => 'Procedimento',      'duration_minutes' => 45],
                        ['name' => 'Avaliação',         'duration_minutes' => 30],
                    ],
                    'automations' => [
                        ['trigger' => 'appointment_created',  'channel' => 'email', 'template' => 'confirmacao_consulta'],
                        ['trigger' => 'appointment_reminder', 'channel' => 'email', 'template' => 'lembrete_consulta', 'hours_before' => 24],
                    ],
                ],
            ],
            [
                'key'  => 'salao',
                'name' => 'Salão / Barbearia',
                'icon' => 'heroicon-o-scissors',
                'template' => [
                    'labels' => [
                        'customer'    => 'Cliente',
                        'appointment' => 'Horário',
                        'service'     => 'Serviço',
                        'resource'    => 'Profissional',
                    ],
                    'custom_fields' => [
                        ['entity' => 'customer', 'name' => 'hair_type',   'label' => 'Tipo de Cabelo', 'type' => 'text',    'required' => false],
                        ['entity' => 'customer', 'name' => 'preferences', 'label' => 'Preferências',   'type' => 'textarea','required' => false],
                    ],
                    'default_statuses' => ['Agendado', 'Confirmado', 'Em Atendimento', 'Finalizado', 'Faltou', 'Cancelado'],
                    'default_services' => [
                        ['name' => 'Corte',        'duration_minutes' => 30],
                        ['name' => 'Barba',        'duration_minutes' => 20],
                        ['name' => 'Escova',       'duration_minutes' => 45],
                        ['name' => 'Coloração',    'duration_minutes' => 90],
                        ['name' => 'Manicure',     'duration_minutes' => 40],
                    ],
                    'automations' => [
                        ['trigger' => 'appointment_created',  'channel' => 'email', 'template' => 'confirmacao_horario'],
                        ['trigger' => 'appointment_reminder', 'channel' => 'email', 'template' => 'lembrete_horario', 'hours_before' => 24],
                    ],
                ],
            ],
            [
                'key'  => 'car_wash',
                'name' => 'Car Wash',
                'icon' => 'heroicon-o-truck',
                'template' => [
                    'labels' => [
                        'customer'    => 'Cliente',
                        'appointment' => 'Lavagem',
                        'service'     => 'Tipo de Lavagem',
                        'resource'    => 'Box',
                    ],
                    'custom_fields' => [
                        ['entity' => 'customer', 'name' => 'vehicle_plate', 'label' => 'Placa',   'type' => 'text', 'required' => true],
                        ['entity' => 'customer', 'name' => 'vehicle_model', 'label' => 'Modelo',  'type' => 'text', 'required' => false],
                        ['entity' => 'customer', 'name' => 'vehicle_color', 'label' => 'Cor',     'type' => 'text', 'required' => false],
                        ['entity' => 'customer', 'name' => 'vehicle_type',  'label' => 'Tipo',    'type' => 'select', 'required' => false,
                            'options' => ['Carro', 'SUV', 'Caminhonete', 'Moto', 'Caminhão']],
                    ],
                    'default_statuses' => ['Agendado', 'Aguardando', 'Em Lavagem', 'Finalizado', 'Entregue', 'Cancelado'],
                    'default_services' => [
                        ['name' => 'Lavagem Simples',    'duration_minutes' => 45],
                        ['name' => 'Lavagem Completa',   'duration_minutes' => 90],
                        ['name' => 'Higienização Interna','duration_minutes' => 120],
                        ['name' => 'Polimento',          'duration_minutes' => 180],
                    ],
                    'automations' => [
                        ['trigger' => 'appointment_created',  'channel' => 'email', 'template' => 'confirmacao_lavagem'],
                        ['trigger' => 'appointment_finished', 'channel' => 'email', 'template' => 'veiculo_pronto'],
                    ],
                ],
            ],
        ];

        foreach ($niches as $data) {
            $template = $data['template'];
            unset($data['template']);

            $niche = BusinessNiche::updateOrCreate(['key' => $data['key']], $data);

            NicheTemplate::updateOrCreate(
                ['business_niche_id' => $niche->id],
                [
                    'labels'           => $template['labels'],
                    'custom_fields'    => $template['custom_fields'],
                    'default_statuses' => $template['default_statuses'],
                    'default_services' => $template['default_services'],
                    'automations'      => $template['automations'],
                ]
            );
        }
    }
}
