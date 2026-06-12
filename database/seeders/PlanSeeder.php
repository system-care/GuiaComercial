<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'                   => 'Trial Gratuito',
                'slug'                   => 'free_trial',
                'price'                  => 0.00,
                'billing_cycle'          => 'monthly',
                'trial_days'             => 14,
                'max_appointments_month' => 30,
                'max_professionals'      => 1,
                'max_services'           => 3,
                'features'               => ['Até 30 agendamentos/mês', '1 profissional', '3 serviços', 'Página pública de agendamento'],
                'active'                 => true,
                'sort_order'             => 0,
            ],
            [
                'name'                   => 'Starter',
                'slug'                   => 'starter',
                'price'                  => 49.90,
                'billing_cycle'          => 'monthly',
                'trial_days'             => 0,
                'max_appointments_month' => 200,
                'max_professionals'      => 3,
                'max_services'           => 10,
                'features'               => ['Até 200 agendamentos/mês', '3 profissionais', '10 serviços', 'Página pública de agendamento', 'E-mail de confirmação'],
                'active'                 => true,
                'sort_order'             => 1,
            ],
            [
                'name'                   => 'Pro',
                'slug'                   => 'pro',
                'price'                  => 99.90,
                'billing_cycle'          => 'monthly',
                'trial_days'             => 0,
                'max_appointments_month' => 0,
                'max_professionals'      => 10,
                'max_services'           => 30,
                'features'               => ['Agendamentos ilimitados', '10 profissionais', '30 serviços', 'Página pública de agendamento', 'E-mail de confirmação', 'Relatórios avançados'],
                'active'                 => true,
                'sort_order'             => 2,
            ],
            [
                'name'                   => 'Ultra',
                'slug'                   => 'ultra',
                'price'                  => 199.90,
                'billing_cycle'          => 'monthly',
                'trial_days'             => 0,
                'max_appointments_month' => 0,
                'max_professionals'      => 0,
                'max_services'           => 0,
                'features'               => ['Agendamentos ilimitados', 'Profissionais ilimitados', 'Serviços ilimitados', 'Página pública de agendamento', 'E-mail de confirmação', 'Relatórios avançados', 'Suporte prioritário', 'Multi-nicho'],
                'active'                 => true,
                'sort_order'             => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
