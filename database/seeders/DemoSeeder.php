<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\BusinessNiche;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $starterPlan = Plan::where('slug', 'starter')->first();
        $proPlan     = Plan::where('slug', 'pro')->first();

        $nicheConsultorio = BusinessNiche::where('key', 'consultorio')->first();
        $nicheSalao       = BusinessNiche::where('key', 'salao')->first();
        $nicheCarWash     = BusinessNiche::where('key', 'car_wash')->first();

        // -----------------------------------------------------------------------
        // TENANT 1 — Clínica Bem Estar (Consultório)
        // -----------------------------------------------------------------------
        $clinica = Tenant::updateOrCreate(['slug' => 'clinica-bem-estar'], [
            'name'             => 'Clínica Bem Estar',
            'email'            => 'contato@clinicabemestar.com.br',
            'phone'            => '11987654321',
            'city'             => 'São Paulo',
            'timezone'         => 'America/Sao_Paulo',
            'business_niche_id'=> $nicheConsultorio?->id,
            'plan'             => 'pro',
            'active'           => true,
        ]);

        TenantSetting::updateOrCreate(['tenant_id' => $clinica->id], [
            'settings' => [
                'description'   => 'Clínica especializada em saúde e bem-estar. Atendemos com carinho e profissionalismo, cuidando de você e de toda a sua família.',
                'whatsapp'      => '5511987654321',
                'address'       => 'Av. Paulista, 1578 — Bela Vista, São Paulo/SP',
                'instagram'     => 'https://instagram.com/clinicabemestar',
                'facebook'      => 'https://facebook.com/clinicabemestar',
                'primary_color' => '#0ea5e9',
                'show_prices'   => true,
                'show_team'     => true,
            ],
        ]);

        $gestorClinica = User::updateOrCreate(['email' => 'gestor@clinicabemestar.com.br'], [
            'name'      => 'Dra. Ana Lima',
            'password'  => Hash::make('demo1234'),
            'role'      => User::ROLE_GESTOR,
            'tenant_id' => $clinica->id,
        ]);

        $profissionaisClinica = [
            ['name' => 'Dr. Carlos Mendes',  'specialty' => 'Clínica Geral',       'email' => 'carlos@clinicabemestar.com.br',  'color' => '#3b82f6'],
            ['name' => 'Dra. Fernanda Costa', 'specialty' => 'Nutrição',            'email' => 'fernanda@clinicabemestar.com.br','color' => '#10b981'],
            ['name' => 'Dr. Roberto Alves',   'specialty' => 'Psicologia',           'email' => 'roberto@clinicabemestar.com.br', 'color' => '#f59e0b'],
        ];

        $profsClinica = [];
        foreach ($profissionaisClinica as $p) {
            $prof = Professional::updateOrCreate(
                ['tenant_id' => $clinica->id, 'email' => $p['email']],
                array_merge($p, ['tenant_id' => $clinica->id, 'active' => true])
            );
            $profsClinica[] = $prof;

            User::updateOrCreate(['email' => $p['email']], [
                'name'            => $p['name'],
                'password'        => Hash::make('demo1234'),
                'role'            => User::ROLE_PROFISSIONAL,
                'tenant_id'       => $clinica->id,
                'professional_id' => $prof->id,
            ]);
        }

        $servicosClinica = [
            ['name' => 'Consulta Inicial',   'duration_minutes' => 60, 'price' => 250.00, 'color' => '#3b82f6', 'description' => 'Primeira consulta com anamnese completa.'],
            ['name' => 'Retorno',             'duration_minutes' => 30, 'price' => 150.00, 'color' => '#10b981', 'description' => 'Consulta de retorno e acompanhamento.'],
            ['name' => 'Avaliação Nutricional','duration_minutes' => 45, 'price' => 200.00, 'color' => '#f59e0b', 'description' => 'Avaliação completa com plano alimentar personalizado.'],
        ];

        $servsClinica = [];
        foreach ($servicosClinica as $s) {
            $servsClinica[] = Service::updateOrCreate(
                ['tenant_id' => $clinica->id, 'name' => $s['name']],
                array_merge($s, ['tenant_id' => $clinica->id, 'active' => true, 'buffer_minutes' => 10])
            );
        }

        $clientesClinica = [
            ['name' => 'Maria Aparecida Santos', 'email' => 'maria.santos@email.com',  'phone' => '11911111111', 'birth_date' => '1985-03-15'],
            ['name' => 'João Pedro Oliveira',    'email' => 'joao.oliveira@email.com', 'phone' => '11922222222', 'birth_date' => '1978-07-22'],
            ['name' => 'Lucia Helena Ferreira',  'email' => 'lucia.ferreira@email.com','phone' => '11933333333', 'birth_date' => '1992-11-08'],
        ];

        $cltsClinica = [];
        foreach ($clientesClinica as $c) {
            $cltsClinica[] = Customer::updateOrCreate(
                ['tenant_id' => $clinica->id, 'email' => $c['email']],
                array_merge($c, ['tenant_id' => $clinica->id, 'active' => true])
            );
        }

        $agendamentosClinica = [
            ['customer' => 0, 'service' => 0, 'professional' => 0, 'date' => now()->subDays(5)->format('Y-m-d'),  'start' => '09:00', 'end' => '10:00', 'status' => 'Atendido',   'notes' => 'Paciente relatou dores de cabeça frequentes.'],
            ['customer' => 1, 'service' => 1, 'professional' => 1, 'date' => now()->addDays(2)->format('Y-m-d'),  'start' => '14:00', 'end' => '14:30', 'status' => 'Agendado',   'notes' => 'Retorno após exames laboratoriais.'],
            ['customer' => 2, 'service' => 2, 'professional' => 1, 'date' => now()->addDays(7)->format('Y-m-d'),  'start' => '10:00', 'end' => '10:45', 'status' => 'Confirmado', 'notes' => 'Primeira avaliação nutricional.'],
        ];

        foreach ($agendamentosClinica as $a) {
            Appointment::updateOrCreate(
                [
                    'tenant_id'       => $clinica->id,
                    'customer_id'     => $cltsClinica[$a['customer']]->id,
                    'professional_id' => $profsClinica[$a['professional']]->id,
                    'date'            => $a['date'],
                    'start_time'      => $a['start'],
                ],
                [
                    'service_id'      => $servsClinica[$a['service']]->id,
                    'end_time'        => $a['end'],
                    'status'          => $a['status'],
                    'notes'           => $a['notes'],
                ]
            );
        }

        $subClinica = Subscription::updateOrCreate(['tenant_id' => $clinica->id], [
            'plan_id'            => $proPlan?->id,
            'status'             => Subscription::STATUS_ACTIVE,
            'billing_type'       => 'CREDIT_CARD',
            'trial_ends_at'      => null,
            'current_period_end' => now()->addDays(28),
        ]);

        foreach ([1, 2, 3] as $i) {
            Payment::updateOrCreate(
                ['tenant_id' => $clinica->id, 'due_date' => now()->subMonths($i)->startOfMonth()->toDateString()],
                [
                    'subscription_id' => $subClinica->id,
                    'value'           => 99.90,
                    'status'          => 'RECEIVED',
                    'billing_type'    => 'CREDIT_CARD',
                    'paid_at'         => now()->subMonths($i)->startOfMonth()->addDays(2),
                ]
            );
        }

        // -----------------------------------------------------------------------
        // TENANT 2 — Salão Bella Donna (Salão / Barbearia)
        // -----------------------------------------------------------------------
        $salao = Tenant::updateOrCreate(['slug' => 'salao-bella-donna'], [
            'name'             => 'Salão Bella Donna',
            'email'            => 'contato@belladonna.com.br',
            'phone'            => '21987654321',
            'city'             => 'Rio de Janeiro',
            'timezone'         => 'America/Sao_Paulo',
            'business_niche_id'=> $nicheSalao?->id,
            'plan'             => 'starter',
            'active'           => true,
        ]);

        TenantSetting::updateOrCreate(['tenant_id' => $salao->id], [
            'settings' => [
                'description'   => 'Salão de beleza completo em Ipanema. Especialistas em coloração, corte e tratamentos capilares de alto padrão.',
                'whatsapp'      => '5521987654321',
                'address'       => 'Rua Visconde de Pirajá, 287 — Ipanema, Rio de Janeiro/RJ',
                'instagram'     => 'https://instagram.com/salaobelladonna',
                'facebook'      => 'https://facebook.com/salaobelladonna',
                'primary_color' => '#ec4899',
                'show_prices'   => true,
                'show_team'     => true,
            ],
        ]);

        $gestorSalao = User::updateOrCreate(['email' => 'gestor@belladonna.com.br'], [
            'name'      => 'Camila Rodrigues',
            'password'  => Hash::make('demo1234'),
            'role'      => User::ROLE_GESTOR,
            'tenant_id' => $salao->id,
        ]);

        $profissionaisSalao = [
            ['name' => 'Beatriz Nunes',   'specialty' => 'Colorista',        'email' => 'beatriz@belladonna.com.br', 'color' => '#ec4899'],
            ['name' => 'Priscila Moraes', 'specialty' => 'Cabeleireira',     'email' => 'priscila@belladonna.com.br','color' => '#a855f7'],
            ['name' => 'Rafael Barbosa',  'specialty' => 'Maquiador',        'email' => 'rafael@belladonna.com.br',  'color' => '#f97316'],
        ];

        $profsSalao = [];
        foreach ($profissionaisSalao as $p) {
            $prof = Professional::updateOrCreate(
                ['tenant_id' => $salao->id, 'email' => $p['email']],
                array_merge($p, ['tenant_id' => $salao->id, 'active' => true])
            );
            $profsSalao[] = $prof;

            User::updateOrCreate(['email' => $p['email']], [
                'name'            => $p['name'],
                'password'        => Hash::make('demo1234'),
                'role'            => User::ROLE_PROFISSIONAL,
                'tenant_id'       => $salao->id,
                'professional_id' => $prof->id,
            ]);
        }

        $servicosSalao = [
            ['name' => 'Corte Feminino', 'duration_minutes' => 45, 'price' => 80.00,  'color' => '#ec4899', 'description' => 'Corte personalizado com técnica avançada.'],
            ['name' => 'Coloração',      'duration_minutes' => 90, 'price' => 180.00, 'color' => '#a855f7', 'description' => 'Coloração completa com produtos profissionais.'],
            ['name' => 'Maquiagem',      'duration_minutes' => 60, 'price' => 120.00, 'color' => '#f97316', 'description' => 'Maquiagem profissional para eventos e casamentos.'],
        ];

        $servsSalao = [];
        foreach ($servicosSalao as $s) {
            $servsSalao[] = Service::updateOrCreate(
                ['tenant_id' => $salao->id, 'name' => $s['name']],
                array_merge($s, ['tenant_id' => $salao->id, 'active' => true, 'buffer_minutes' => 10])
            );
        }

        $clientesSalao = [
            ['name' => 'Amanda Sousa',     'email' => 'amanda.sousa@email.com',    'phone' => '21944444444', 'birth_date' => '1995-05-20'],
            ['name' => 'Patrícia Gomes',   'email' => 'patricia.gomes@email.com',  'phone' => '21955555555', 'birth_date' => '1988-09-14'],
            ['name' => 'Fernanda Meireles','email' => 'fernanda.meireles@email.com','phone' => '21966666666', 'birth_date' => '2001-02-28'],
        ];

        $cltsSalao = [];
        foreach ($clientesSalao as $c) {
            $cltsSalao[] = Customer::updateOrCreate(
                ['tenant_id' => $salao->id, 'email' => $c['email']],
                array_merge($c, ['tenant_id' => $salao->id, 'active' => true])
            );
        }

        $agendamentosSalao = [
            ['customer' => 0, 'service' => 0, 'professional' => 1, 'date' => now()->subDays(3)->format('Y-m-d'),  'start' => '10:00', 'end' => '10:45', 'status' => 'Finalizado', 'notes' => 'Corte médio com franja.'],
            ['customer' => 1, 'service' => 1, 'professional' => 0, 'date' => now()->format('Y-m-d'),              'start' => '14:00', 'end' => '15:30', 'status' => 'Em Atendimento', 'notes' => 'Loiro platinado.'],
            ['customer' => 2, 'service' => 2, 'professional' => 2, 'date' => now()->addDays(5)->format('Y-m-d'),  'start' => '09:00', 'end' => '10:00', 'status' => 'Agendado',    'notes' => 'Maquiagem para casamento.'],
        ];

        foreach ($agendamentosSalao as $a) {
            Appointment::updateOrCreate(
                [
                    'tenant_id'       => $salao->id,
                    'customer_id'     => $cltsSalao[$a['customer']]->id,
                    'professional_id' => $profsSalao[$a['professional']]->id,
                    'date'            => $a['date'],
                    'start_time'      => $a['start'],
                ],
                [
                    'service_id'  => $servsSalao[$a['service']]->id,
                    'end_time'    => $a['end'],
                    'status'      => $a['status'],
                    'notes'       => $a['notes'],
                ]
            );
        }

        $subSalao = Subscription::updateOrCreate(['tenant_id' => $salao->id], [
            'plan_id'            => $starterPlan?->id,
            'status'             => Subscription::STATUS_ACTIVE,
            'billing_type'       => 'PIX',
            'trial_ends_at'      => null,
            'current_period_end' => now()->addDays(15),
        ]);

        foreach ([1, 2, 3] as $i) {
            Payment::updateOrCreate(
                ['tenant_id' => $salao->id, 'due_date' => now()->subMonths($i)->startOfMonth()->toDateString()],
                [
                    'subscription_id' => $subSalao->id,
                    'value'           => 49.90,
                    'status'          => 'RECEIVED',
                    'billing_type'    => 'PIX',
                    'paid_at'         => now()->subMonths($i)->startOfMonth()->addDays(1),
                ]
            );
        }

        // -----------------------------------------------------------------------
        // TENANT 3 — Clean Car Wash (Car Wash)
        // -----------------------------------------------------------------------
        $carwash = Tenant::updateOrCreate(['slug' => 'clean-car-wash'], [
            'name'             => 'Clean Car Wash',
            'email'            => 'contato@cleancarwash.com.br',
            'phone'            => '51987654321',
            'city'             => 'Porto Alegre',
            'timezone'         => 'America/Sao_Paulo',
            'business_niche_id'=> $nicheCarWash?->id,
            'plan'             => 'starter',
            'active'           => true,
        ]);

        TenantSetting::updateOrCreate(['tenant_id' => $carwash->id], [
            'settings' => [
                'description'   => 'Lavagem e estética automotiva com produtos premium. Seu carro merece o melhor tratamento — agendamento fácil e rápido.',
                'whatsapp'      => '5551987654321',
                'address'       => 'Av. Ipiranga, 1440 — Jardim Botânico, Porto Alegre/RS',
                'instagram'     => 'https://instagram.com/cleancarwash',
                'facebook'      => 'https://facebook.com/cleancarwash',
                'primary_color' => '#0284c7',
                'show_prices'   => true,
                'show_team'     => false,
            ],
        ]);

        $gestorCarwash = User::updateOrCreate(['email' => 'gestor@cleancarwash.com.br'], [
            'name'      => 'Marcelo Teixeira',
            'password'  => Hash::make('demo1234'),
            'role'      => User::ROLE_GESTOR,
            'tenant_id' => $carwash->id,
        ]);

        $profissionaisCarwash = [
            ['name' => 'Thiago Andrade', 'specialty' => 'Lavador',      'email' => 'thiago@cleancarwash.com.br', 'color' => '#0284c7'],
            ['name' => 'Lucas Pereira',  'specialty' => 'Polidor',       'email' => 'lucas@cleancarwash.com.br',  'color' => '#0891b2'],
            ['name' => 'Gustavo Dias',   'specialty' => 'Higienizador',  'email' => 'gustavo@cleancarwash.com.br','color' => '#0369a1'],
        ];

        $profsCarwash = [];
        foreach ($profissionaisCarwash as $p) {
            $prof = Professional::updateOrCreate(
                ['tenant_id' => $carwash->id, 'email' => $p['email']],
                array_merge($p, ['tenant_id' => $carwash->id, 'active' => true])
            );
            $profsCarwash[] = $prof;

            User::updateOrCreate(['email' => $p['email']], [
                'name'            => $p['name'],
                'password'        => Hash::make('demo1234'),
                'role'            => User::ROLE_PROFISSIONAL,
                'tenant_id'       => $carwash->id,
                'professional_id' => $prof->id,
            ]);
        }

        $servicosCarwash = [
            ['name' => 'Lavagem Simples',     'duration_minutes' => 45,  'price' => 50.00,  'color' => '#0284c7', 'description' => 'Lavagem externa completa com shampoo neutro.'],
            ['name' => 'Lavagem Completa',     'duration_minutes' => 90,  'price' => 90.00,  'color' => '#0891b2', 'description' => 'Lavagem externa e interna com aspiração.'],
            ['name' => 'Higienização Interna', 'duration_minutes' => 120, 'price' => 180.00, 'color' => '#0369a1', 'description' => 'Higienização profunda de estofados e carpetes.'],
        ];

        $servsCarwash = [];
        foreach ($servicosCarwash as $s) {
            $servsCarwash[] = Service::updateOrCreate(
                ['tenant_id' => $carwash->id, 'name' => $s['name']],
                array_merge($s, ['tenant_id' => $carwash->id, 'active' => true, 'buffer_minutes' => 15])
            );
        }

        $clientesCarwash = [
            ['name' => 'Paulo Henrique Mota', 'email' => 'paulo.mota@email.com',    'phone' => '51977777777', 'birth_date' => '1983-06-10',
             'custom_data' => ['vehicle_plate' => 'ABC1D23', 'vehicle_model' => 'Civic', 'vehicle_color' => 'Preto']],
            ['name' => 'Renata Cardoso',      'email' => 'renata.cardoso@email.com','phone' => '51988888888', 'birth_date' => '1990-12-03',
             'custom_data' => ['vehicle_plate' => 'XYZ9A87', 'vehicle_model' => 'HRV',   'vehicle_color' => 'Prata']],
            ['name' => 'Eduardo Fonseca',     'email' => 'eduardo.fonseca@email.com','phone' => '51999999999','birth_date' => '1975-04-25',
             'custom_data' => ['vehicle_plate' => 'QRS5B45', 'vehicle_model' => 'Corolla','vehicle_color' => 'Branco']],
        ];

        $cltsCarwash = [];
        foreach ($clientesCarwash as $c) {
            $cltsCarwash[] = Customer::updateOrCreate(
                ['tenant_id' => $carwash->id, 'email' => $c['email']],
                array_merge($c, ['tenant_id' => $carwash->id, 'active' => true])
            );
        }

        $agendamentosCarwash = [
            ['customer' => 0, 'service' => 0, 'professional' => 0, 'date' => now()->subDays(1)->format('Y-m-d'), 'start' => '08:00', 'end' => '08:45', 'status' => 'Entregue',  'notes' => 'Honda Civic — cliente VIP.'],
            ['customer' => 1, 'service' => 1, 'professional' => 1, 'date' => now()->format('Y-m-d'),             'start' => '11:00', 'end' => '12:30', 'status' => 'Em Lavagem','notes' => 'HRV prata, cuidar dos tapetes.'],
            ['customer' => 2, 'service' => 2, 'professional' => 2, 'date' => now()->addDays(3)->format('Y-m-d'), 'start' => '09:00', 'end' => '11:00', 'status' => 'Agendado',  'notes' => 'Corolla branco, banco de couro.'],
        ];

        foreach ($agendamentosCarwash as $a) {
            Appointment::updateOrCreate(
                [
                    'tenant_id'       => $carwash->id,
                    'customer_id'     => $cltsCarwash[$a['customer']]->id,
                    'professional_id' => $profsCarwash[$a['professional']]->id,
                    'date'            => $a['date'],
                    'start_time'      => $a['start'],
                ],
                [
                    'service_id'  => $servsCarwash[$a['service']]->id,
                    'end_time'    => $a['end'],
                    'status'      => $a['status'],
                    'notes'       => $a['notes'],
                ]
            );
        }

        $subCarwash = Subscription::updateOrCreate(['tenant_id' => $carwash->id], [
            'plan_id'            => $starterPlan?->id,
            'status'             => Subscription::STATUS_TRIAL,
            'billing_type'       => 'BOLETO',
            'trial_ends_at'      => now()->addDays(10),
            'current_period_end' => now()->addDays(10),
        ]);

        foreach ([1, 2] as $i) {
            Payment::updateOrCreate(
                ['tenant_id' => $carwash->id, 'due_date' => now()->subMonths($i)->startOfMonth()->toDateString()],
                [
                    'subscription_id' => $subCarwash->id,
                    'value'           => 49.90,
                    'status'          => 'RECEIVED',
                    'billing_type'    => 'BOLETO',
                    'paid_at'         => now()->subMonths($i)->startOfMonth()->addDays(3),
                ]
            );
        }
        // Pagamento pendente (mês atual)
        Payment::updateOrCreate(
            ['tenant_id' => $carwash->id, 'due_date' => now()->startOfMonth()->toDateString()],
            [
                'subscription_id' => $subCarwash->id,
                'value'           => 49.90,
                'status'          => 'PENDING',
                'billing_type'    => 'BOLETO',
                'paid_at'         => null,
            ]
        );

        $this->command->info('Demo data seeded successfully!');
        $this->command->table(
            ['Tenant', 'Gestor', 'Senha', 'URL pública'],
            [
                ['Clínica Bem Estar',  'gestor@clinicabemestar.com.br', 'demo1234', '/clinica-bem-estar'],
                ['Salão Bella Donna',  'gestor@belladonna.com.br',      'demo1234', '/salao-bella-donna'],
                ['Clean Car Wash',     'gestor@cleancarwash.com.br',    'demo1234', '/clean-car-wash'],
            ]
        );
    }
}
