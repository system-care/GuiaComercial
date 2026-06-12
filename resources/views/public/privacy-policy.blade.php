@extends('public.layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-black text-slate-900">Política de Privacidade</h1>
    <p class="mt-2 text-sm text-slate-500">Última atualização: {{ date('d/m/Y') }}</p>

    <div class="mt-8 space-y-8 text-slate-700">

        <section>
            <h2 class="text-xl font-bold text-slate-900">1. Quem somos</h2>
            <p class="mt-3 leading-7">O <strong>Guia Comercial</strong> é uma plataforma de diretório local que conecta consumidores a empresas e profissionais que oferecem agendamento online. Nosso site é <a href="{{ url('/') }}" class="text-violet-600 hover:underline">{{ config('app.url') }}</a>.</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900">2. Dados que coletamos</h2>
            <p class="mt-3 leading-7">Coletamos informações necessárias para o funcionamento da plataforma:</p>
            <ul class="mt-3 list-disc space-y-2 pl-6 leading-7">
                <li><strong>Dados de cadastro:</strong> nome, e-mail, telefone (WhatsApp) e nome da empresa ao criar uma conta.</li>
                <li><strong>Dados de agendamento:</strong> data, horário, serviço e informações do cliente ao realizar ou receber um agendamento.</li>
                <li><strong>Login com Google:</strong> quando você usa o Google para autenticar, recebemos seu nome, e-mail e identificador único do Google. Não recebemos sua senha do Google.</li>
                <li><strong>Dados de uso:</strong> endereço IP, navegador e páginas visitadas para fins de segurança e melhoria do serviço.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900">3. Como usamos seus dados</h2>
            <ul class="mt-3 list-disc space-y-2 pl-6 leading-7">
                <li>Criar e gerenciar sua conta de empresa ou cliente.</li>
                <li>Processar e notificar agendamentos (via e-mail e WhatsApp).</li>
                <li>Autenticar seu acesso à plataforma.</li>
                <li>Enviar lembretes e confirmações de agendamento.</li>
                <li>Melhorar a segurança e a experiência de uso.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900">4. Compartilhamento de dados</h2>
            <p class="mt-3 leading-7">Não vendemos seus dados. Podemos compartilhá-los apenas com:</p>
            <ul class="mt-3 list-disc space-y-2 pl-6 leading-7">
                <li><strong>Prestadores de serviço:</strong> provedores de e-mail transacional, hospedagem e processamento de pagamento, exclusivamente para operação da plataforma.</li>
                <li><strong>Obrigação legal:</strong> quando exigido por lei ou autoridade competente.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900">5. Cookies</h2>
            <p class="mt-3 leading-7">Utilizamos cookies de sessão necessários para manter você autenticado e garantir o funcionamento seguro da plataforma. Não utilizamos cookies de rastreamento ou publicidade de terceiros.</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900">6. Seus direitos (LGPD)</h2>
            <p class="mt-3 leading-7">Em conformidade com a Lei Geral de Proteção de Dados (LGPD — Lei nº 13.709/2018), você tem direito a:</p>
            <ul class="mt-3 list-disc space-y-2 pl-6 leading-7">
                <li>Acessar os dados que temos sobre você.</li>
                <li>Corrigir dados incompletos ou desatualizados.</li>
                <li>Solicitar a exclusão de seus dados.</li>
                <li>Revogar o consentimento a qualquer momento.</li>
            </ul>
            <p class="mt-3 leading-7">Para exercer seus direitos, entre em contato: <a href="mailto:contato@guiacomercial.app" class="text-violet-600 hover:underline">contato@guiacomercial.app</a>.</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900">7. Segurança</h2>
            <p class="mt-3 leading-7">Adotamos medidas técnicas e organizacionais para proteger seus dados contra acesso não autorizado, perda ou divulgação indevida, incluindo criptografia HTTPS e autenticação segura.</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900">8. Retenção de dados</h2>
            <p class="mt-3 leading-7">Mantemos seus dados enquanto sua conta estiver ativa ou conforme necessário para cumprir obrigações legais. Ao solicitar a exclusão da conta, removeremos seus dados pessoais em até 30 dias.</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900">9. Alterações nesta política</h2>
            <p class="mt-3 leading-7">Podemos atualizar esta política periodicamente. Notificaremos mudanças relevantes por e-mail ou aviso na plataforma. O uso continuado após as alterações implica aceite dos novos termos.</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900">10. Contato</h2>
            <p class="mt-3 leading-7">Dúvidas sobre esta política? Entre em contato:<br>
            <a href="mailto:contato@guiacomercial.app" class="text-violet-600 hover:underline">contato@guiacomercial.app</a></p>
        </section>

    </div>
</div>
@endsection
