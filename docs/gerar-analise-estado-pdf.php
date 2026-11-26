<?php

declare(strict_types=1);

/**
 * Gera o PDF da analise do estado atual do site. Uso: php docs/gerar-analise-estado-pdf.php
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Somente CLI.\n");
    exit(1);
}

$base = dirname(__DIR__);
require $base . '/app/core/SimplePdf.php';

final class AnaliseEstadoPdf extends SimplePdf
{
    public function writeDocument(): void
    {
        $this->publicLetterhead();
        $this->setY(841.89 - 118);
        $this->title('Estado atual do site hasoftware-template');
        $this->muted('Analise do repositorio em 18/11/2026. Somente leitura do codigo.');
        $this->muted('main e origin/main: commit 0e512c9 (responsividade). Ha alteracoes locais ainda nao enviadas ao remoto.');
        $this->gap(8);

        $this->h2('1. Proposta e tipo de uso do site');
        $this->p('O site e a vitrine da H. A Consultoria & Desenvolvimento: o visitante conhece a empresa, pede contato e, no codigo atual, tambem experimenta um painel e uma calculadora.');
        $this->p('A area restrita e so para a equipe (admin, editor, viewer): editar textos da home, receber solicitacoes, cuidar de usuarios e acompanhar propostas.');
        $this->p('Nao ha cadastro publico nem "minha conta" do cliente. O cliente so entra se alguem da equipe mandar um link privado de proposta. Isso existe no codigo local, nao no commit de producao.');

        $this->h2('2. O que vejo como visitante');
        $this->li('Pagina inicial (menu Inicio, Sobre, Servicos, Contato): hero com titulo, subtitulo, logo e botao vindos de Dados de inicio; Sobre nos com texto do banco; tres cards estaticos (Consultoria, Projetos, Suporte); formulario Nome / E-mail / Mensagem e o botao Enviar mensagem. Instagram, LinkedIn e Facebook no rodape apontam para # (nao abrem redes reais).');
        $this->li('Agendar consultoria (menu e bloco no fim da home): botao Agendar consultoria pelo WhatsApp, usando o numero de Dados de contato no codigo atual.');
        $this->li('Consultoria (na home, em Servicos, ancora #consultoria): calculadora Vezes por semana e Minutos por execucao, Calcular, resultado como estimativa de tempo, e Quero automatizar essa tarefa, que leva ao formulario de Contato com um resumo editavel. So no working copy; nao esta em origin/main.');
        $this->li('Projetos (menu Projetos): painel de vendas de demonstracao, filtro Aplicar por mes, indicadores e Quero um painel assim (tambem preenche o contato). So no working copy.');
        $this->li('Proposta por link (/proposta?t=...): escopo, prazo, valor e Enviar pedido de ajuste, sem login. Sem o token, a pagina diz que o link e invalido. So no working copy. Login da equipe: icone de cadeado no menu (Area restrita).');

        $this->h2('3. O que vejo como administrador');
        $this->p('Depois do login, a tela Area Restrita / Dashboard mostra Mensagens hoje (contagem real do dia) e Acessos Hoje (numero fixo de demonstracao, 143). O menu depende do perfil; a trava de verdade esta no servidor, nao so no menu.');
        $this->li('Dados de inicio, Sobre nos, Dados de contato, Propostas de clientes: admin e editor. Em Dados de contato: Titulo e Descricao (texto do formulario publico), E-mail de notificacao (destino e remetente do aviso por e-mail), Telefone e WhatsApp (este ultimo alimenta Agendar consultoria pelo WhatsApp), botao Atualizar / Salvar (com confirmacao).');
        $this->li('Configuracao de Usuarios (so admin): Criar usuario, Salvar o tipo (viewer / editor / admin), Excluir. Nao da para alterar o proprio tipo nem excluir a si mesmo; o sistema impede apagar o ultimo admin.');
        $this->li('Solicitacoes: todos os logados leem a lista; Detalhes abre a mensagem. Admin/editor salvam status, Anotacao interna, Data do proximo contato e usam o filtro Retornos pendentes. Viewer so consulta. Atendimento com status/historico: working copy. Em producao (origin/main) a tela ainda e a listagem simples Mensagens de contato.');
        $this->li('Propostas de clientes: Nova proposta, Editar, Excluir, Gerar proposta PDF / Baixar PDF, e no codigo local Gerar link do cliente e Revogar, alem dos Pedidos de ajuste enviados pelo cliente.');
        $this->p('Editor: mesmo conteudo e propostas que o admin, inclusive Dados de contato; nao entra em Configuracao de Usuarios.');
        $this->p('Viewer: dashboard + Solicitacoes em leitura; POST de atendimento, contato, propostas e usuarios e recusado no servidor.');

        $this->h2('4. O que ja foi desenvolvido');
        $this->p('Completo no commit de producao (origin/main, 0e512c9): home institucional editavel (inicio, sobre, contato); formulario publico que grava a mensagem e tenta e-mail; login com senha e limite de tentativas; dashboard; usuarios (criar, tipo, excluir); funil de propostas com PDF; confirmacao antes de salvar/excluir; site responsivo; migrations/seeders via terminal.');
        $this->p('Completo no codigo local, ainda nao no remoto: menu Projetos (demo de painel); calculadora em Consultoria; Solicitacoes com status e historico; link privado da proposta (hash, validade, revogacao) e Solicitar ajuste.');
        $this->p('Parcial: Acessos Hoje — o card existe, o numero nao e medido de verdade. E-mail de notificacao — grava e chama mail(); a entrega depende do servidor de e-mail. Redes sociais — os nomes aparecem, os links nao vao a perfis reais. Servicos — os tres cards existem, o texto nao se edita no painel. Logout — o icone Sair funciona, mas e um GET, sem o mesmo cuidado de seguranca dos outros envios.');
        $this->p('So planejado / citado e sem produto: SaaS multi-tenant, API publica, CMS generico, e-mail marketing, cadastro ou "esqueci a senha" para visitante. O README antigo fala nisso; o codigo nao tem.');
        $this->p('A documentacao ainda diz que o WhatsApp da home esta fixo e que os anos de experiencia usam 2026 na view; no working copy o WhatsApp vem de Dados de contato e os anos saem do calculo a partir de 2024. Em producao (origin/main) vale o ultimo commit publicado, sem Projetos, calculadora, atendimento de solicitacoes nem link de proposta, ate essas mudancas serem commitadas e implantadas.');

        $this->h2('Como usar esta analise nas proximas entregas');
        $this->p('Ao concluir cada entrega, terminar com um resumo de ate quatro linhas:');
        $this->li('Desenvolvido — o que mudou.');
        $this->li('Visitante — o que vou ver ou conseguir fazer, e onde.');
        $this->li('Admin — o que vou ver ou conseguir fazer, e em qual menu.');
        $this->li('Verificacao — o que foi testado e o que ficou pendente.');
        $this->p('Usar os nomes reais dos botoes, telas e menus. Priorizar a experiencia de uso e citar detalhes tecnicos somente quando ajudarem a entender como a funcionalidade funciona.');

        $this->drawEndFooter();
    }

    private function publicLetterhead(): void
    {
        $m = new ReflectionMethod(SimplePdf::class, 'drawLetterhead');
        $m->setAccessible(true);
        $m->invoke($this, '');
    }

    private function setY(float $y): void
    {
        $p = new ReflectionProperty(SimplePdf::class, 'y');
        $p->setAccessible(true);
        $p->setValue($this, $y);
    }

    private function title(string $text): void
    {
        $this->call('ensureSpace', 28);
        $this->call('writeTextAt', 48.0, $this->y(), $text, 16.0, true, 0.08, 0.08, 0.1);
        $this->setY($this->y() - 22);
        $this->call('accentRule');
        $this->setY($this->y() - 6);
    }

    private function h2(string $text): void
    {
        $this->call('ensureSpace', 36);
        $this->setY($this->y() - 10);
        $this->call('writeTextAt', 48.0, $this->y(), $text, 12.0, true, 0.39, 0.4, 0.95);
        $this->setY($this->y() - 16);
    }

    private function p(string $text): void
    {
        $this->call('paragraph', $text, 10.0);
        $this->setY($this->y() - 4);
    }

    private function muted(string $text): void
    {
        $this->call('ensureSpace', 16);
        $this->call('writeTextAt', 48.0, $this->y(), $text, 9.0, false, 0.35, 0.35, 0.4);
        $this->setY($this->y() - 13);
    }

    private function li(string $text): void
    {
        $this->call('ensureSpace', 16);
        $this->call('writeTextAt', 48.0, $this->y(), '-', 10.0, true, 0.39, 0.4, 0.95);
        $maxChars = (int) max(20, floor((595.28 - 70) / 5.0));
        $chunks = $this->call('wrap', $text, $maxChars);
        foreach ($chunks as $i => $chunk) {
            $this->call('ensureSpace', 14);
            $this->call('writeTextAt', 62.0, $this->y(), $chunk, 10.0, false, 0.12, 0.12, 0.14);
            $this->setY($this->y() - 13);
        }
        $this->setY($this->y() - 4);
    }

    private function gap(float $n): void
    {
        $this->setY($this->y() - $n);
    }

    private function y(): float
    {
        $p = new ReflectionProperty(SimplePdf::class, 'y');
        $p->setAccessible(true);
        return (float) $p->getValue($this);
    }

    private function call(string $method, mixed ...$args): mixed
    {
        $m = new ReflectionMethod(SimplePdf::class, $method);
        $m->setAccessible(true);
        return $m->invoke($this, ...$args);
    }

    private function drawEndFooter(): void
    {
        $this->call(
            'drawFooter',
            'H. A Consultoria & Desenvolvimento — documento interno de analise do produto',
            date('d/m/Y')
        );
    }
}

$pdf = new AnaliseEstadoPdf();
$pdf->writeDocument();
$out = $base . '/docs/Analise-estado-atual-hasoftware-template.pdf';
file_put_contents($out, $pdf->bytes());
echo $out . "\n" . filesize($out) . " bytes\n";
