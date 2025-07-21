=== Joinotify Booking Integration ===
Contributors: David William da Costa
Tags: woocommerce, booking, whatsapp, notifications, joinotify, hpos
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integração dinâmica entre WooCommerce Booking, Product Add-ons e Joinotify para notificações WhatsApp completas e automáticas. Totalmente compatível com HPOS.

== Description ==

O **Joinotify Booking Integration** é um plugin que conecta automaticamente o WooCommerce Booking e WooCommerce Product Add-ons com o Joinotify, permitindo o envio de notificações WhatsApp ricas em detalhes sobre reservas e add-ons selecionados.

### 🚀 Características Principais

* **100% Dinâmico** - Funciona com qualquer produto de reserva, independente do nome ou configuração
* **Detecção Automática** - Reconhece automaticamente todos os tipos de add-ons
* **Zero Manutenção** - Novos produtos e add-ons são detectados automaticamente
* **Placeholders Inteligentes** - 6 novos placeholders para usar no Joinotify
* **Notificações Automáticas** - Envio automático baseado em mudanças de status
* **Ícones Contextuais** - Ícones automáticos baseados no tipo de add-on
* **Interface Amigável** - Painel administrativo completo com testes e logs
* **✅ Compatível com HPOS** - Totalmente compatível com High-Performance Order Storage

### 📱 Placeholders Disponíveis

* `{{ booking_details }}` - Detalhes completos das reservas
* `{{ booking_dates }}` - Datas e horários
* `{{ booking_persons }}` - Informações de pessoas
* `{{ booking_resources }}` - Recursos/locais selecionados
* `{{ product_addons }}` - Lista de add-ons com ícones
* `{{ addons_summary }}` - Resumo com valores

### 🎯 Funcionalidades

* Notificações automáticas via WhatsApp
* Integração com emails do WooCommerce
* Sistema de logs detalhado
* Teste de notificações
* Configurações flexíveis
* Suporte a múltiplas reservas por pedido
* Formatação inteligente de valores e datas

### 📋 Requisitos

* WordPress 5.0+
* WooCommerce 6.0+
* WooCommerce Bookings
* WooCommerce Product Add-ons
* Joinotify
* Conta ativa no MeuMouse.com

== Installation ==

1. Faça upload do plugin para o diretório `/wp-content/plugins/`
2. Ative o plugin através do menu 'Plugins' no WordPress
3. Acesse WooCommerce > Joinotify Booking para configurar
4. Configure seu número de telefone registrado no MeuMouse.com
5. Teste o funcionamento com um pedido existente

== Frequently Asked Questions ==

= O plugin funciona com qualquer produto de reserva? =

Sim! O plugin é 100% dinâmico e funciona automaticamente com qualquer produto que use o WooCommerce Booking, independente do nome ou configuração.

= Preciso configurar algo para novos add-ons? =

Não! O plugin detecta automaticamente qualquer tipo de add-on criado no WooCommerce Product Add-ons, incluindo ícones contextuais baseados no conteúdo.

= Como uso os placeholders no Joinotify? =

Após ativar o plugin, os placeholders aparecem automaticamente na lista de variáveis do Joinotify. Basta selecioná-los ao criar suas mensagens.

= O plugin envia notificações automaticamente? =

Sim! As notificações são enviadas automaticamente quando o status do pedido muda para os status configurados (por padrão: processing, completed, confirmed).

= Posso testar antes de usar em produção? =

Sim! O plugin inclui uma funcionalidade de teste que permite enviar notificações de teste usando pedidos existentes.

== Screenshots ==

1. Painel de configurações do plugin
2. Aba de teste com placeholders disponíveis
3. Logs do sistema para monitoramento
4. Exemplo de mensagem WhatsApp gerada
5. Placeholders no construtor do Joinotify

= 1.0.8 =
* Corrigido erro fatal causado por declaração duplicada de função.
* Adicionado placeholder `{{ booking_id }}` para exibir o número da reserva.
* Atualizado o template de mensagem padrão para usar `{{ booking_id }}`.

= 1.0.7 =
* Nova funcionalidade: Template de mensagem personalizável nas configurações.
* Correção: Formatação de valores HTML removida (agora mostra "R$ 397,00" ao invés de código HTML).
* Melhoria: Sistema de placeholders mais robusto e flexível.
* Interface: Campo de template com documentação dos placeholders disponíveis.
* Personalização: Controle total sobre o formato das mensagens WhatsApp.

= 1.0.6 =
* Correção crítica: Removida dependência do método inexistente `prepare_receiver()` do Joinotify.
* Implementado método próprio `format_phone_number()` para formatação de números de telefone.
* Resolve erro fatal "Call to undefined method MeuMouse\Joinotify\API\Controller::prepare_receiver()".
* Melhoria na compatibilidade com diferentes versões do Joinotify.

= 1.0.5 =
* Melhoria na depuração: Adicionados logs detalhados na função `order_has_bookings` para identificar problemas na detecção de reservas.
* Ajuda no diagnóstico de placeholders vazios.

= 1.0.4 =
* Correção de erro 500: Adicionado tratamento de exceções e logs mais detalhados nas funções de obtenção de dados de reserva e add-ons.
* Melhoria na robustez do plugin ao lidar com dados de reserva e add-ons.

= 1.0.3 =
* Correção crítica: Melhorado tratamento de erros AJAX
* Adicionado debugging detalhado para facilitar diagnóstico
* Melhorada validação de dados no frontend e backend
* Timeout configurável para requisições AJAX (30s para testes)
* Mensagens de erro mais específicas e informativas
* Verificação robusta de nonce e permissões
* Logs detalhados para troubleshooting

= 1.0.2 =
* Correção crítica: Método sanitize_settings() alterado de private para public
* Resolve erro fatal "Call to private method JBI_Settings::sanitize_settings()"
* Melhoria na estabilidade do plugin
* Compatibilidade mantida com HPOS

= 1.0.1 =
* Adicionada compatibilidade total com HPOS (High-Performance Order Storage)
* Declaração de compatibilidade com custom_order_tables
* Uso de APIs compatíveis com HPOS para acesso a dados de pedidos
* Interface administrativa mostra status do HPOS
* Melhorias na detecção de pedidos e metadados
* Logs aprimorados com informações sobre HPOS

= 1.0.0 =
* Versão inicial
* Integração dinâmica com WooCommerce Booking
* Integração dinâmica com WooCommerce Product Add-ons
* 6 placeholders automáticos para Joinotify
* Notificações automáticas via WhatsApp
* Painel administrativo completo
* Sistema de logs e debugging
* Funcionalidade de teste
* Integração com emails do WooCommerce

== Upgrade Notice ==

= 1.0.8 =
Correção de erro fatal e adição do placeholder `{{ booking_id }}` para o número da reserva. Recomendado para todos os usuários.

= 1.0.7 =
Nova funcionalidade importante: Template de mensagem personalizável! Agora você pode editar completamente o formato das mensagens WhatsApp. Corrige também a formatação de valores HTML.

= 1.0.6 =
Correção crítica obrigatória: Resolve erro fatal "Call to undefined method prepare_receiver()". Remove dependência de método inexistente do Joinotify. Atualize imediatamente.

= 1.0.5 =
Melhoria na depuração: Adicionados logs detalhados na função `order_has_bookings` para identificar problemas na detecção de reservas. Ajuda no diagnóstico de placeholders vazios.

= 1.0.4 =
Correção importante: Resolve erro interno do servidor (código 500) ao processar dados de reserva e add-ons. Recomendado para todos os usuários.

= 1.0.3 =
Correção importante: Resolve problemas de comunicação AJAX e melhora debugging. Recomendado para todos os usuários que enfrentam erro "Falha na comunicação com o servidor".

= 1.0.2 =
Correção crítica obrigatória: Resolve erro fatal que impedia o funcionamento do plugin. Atualize imediatamente.

= 1.0.1 =
Atualização importante: Adiciona compatibilidade total com HPOS (High-Performance Order Storage) do WooCommerce. Recomendado para todas as lojas que usam ou planejam usar HPOS.

= 1.0.0 =
Versão inicial do plugin. Instale para começar a usar as integrações automáticas.

== Support ==

Para suporte técnico, consulte:
* Documentação oficial dos plugins WooCommerce
* Central de ajuda do Joinotify: https://ajuda.meumouse.com/
* Desenvolvedores especializados em WooCommerce

== Privacy Policy ==

Este plugin não coleta dados pessoais dos usuários. Todas as informações processadas são relacionadas aos pedidos do WooCommerce e são utilizadas apenas para envio de notificações conforme configurado pelo administrador do site.

