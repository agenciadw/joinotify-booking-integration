# Manual do Plugin Joinotify Booking Integration

Este manual fornece uma visão detalhada de todas as funcionalidades e configurações do plugin **Joinotify Booking Integration**.

## Visão Geral

O **Joinotify Booking Integration** é uma ferramenta poderosa que automatiza a comunicação com seus clientes via WhatsApp, integrando-se perfeitamente com o WooCommerce, WooCommerce Bookings e WooCommerce Product Add-ons. Ele permite que você envie notificações personalizadas e detalhadas sobre reservas, incluindo informações sobre datas, horários, pessoas, recursos e add-ons selecionados.

## Funcionalidades Principais

*   **Integração Dinâmica:** Funciona com qualquer produto de reserva e add-on, sem necessidade de configuração manual para cada novo item.
*   **Notificações Automáticas:** Envia mensagens de WhatsApp automaticamente quando o status de um pedido é alterado.
*   **Placeholders Inteligentes:** Utilize placeholders para inserir informações dinâmicas nas suas mensagens, como detalhes da reserva, nome do cliente, número do pedido, etc.
*   **Personalização de Mensagens:** Crie templates de mensagem personalizados para se adequar à sua marca e às suas necessidades de comunicação.
*   **Logs Detalhados:** Monitore todas as notificações enviadas e identifique problemas com o sistema de logs integrado.
*   **Teste de Notificações:** Envie notificações de teste para pedidos existentes para garantir que tudo esteja funcionando corretamente antes de entrar em produção.
*   **Compatibilidade com HPOS:** Totalmente compatível com o High-Performance Order Storage (HPOS) do WooCommerce, garantindo performance e escalabilidade.

## Configurações do Plugin

Para acessar as configurações do plugin, navegue até `WooCommerce > Joinotify Booking` no painel administrativo do WordPress.

### Aba de Configurações

*   **Número do Remetente:** Insira o número de telefone do WhatsApp que será usado para enviar as notificações. Este número deve estar registrado e ativo na sua conta MeuMouse.com.
*   **Status de Notificação:** Selecione os status de pedido que acionarão o envio de notificações. Por exemplo, você pode querer enviar uma notificação quando um pedido é `Processando` ou `Concluído`.
*   **Atraso da Notificação:** Defina um atraso em segundos para o envio da notificação após a mudança de status do pedido. Isso pode ser útil para evitar o envio de notificações em caso de mudanças rápidas de status.
*   **Habilitar Integração com E-mail:** Se habilitado, os detalhes da reserva e dos add-ons também serão adicionados aos e-mails transacionais do WooCommerce.
*   **Habilitar Logs de Depuração:** Habilite esta opção para registrar informações detalhadas sobre o envio de notificações, o que pode ser útil para solucionar problemas.
*   **Template de Mensagem:** Personalize o conteúdo das suas mensagens do WhatsApp. Utilize os placeholders disponíveis para inserir informações dinâmicas.

### Aba de Teste de Notificação

Nesta aba, você pode testar o envio de notificações para um pedido existente:

1.  **ID do Pedido:** Insira o ID do pedido que você deseja usar para o teste.
2.  **Enviar Notificação de Teste:** Clique neste botão para enviar uma notificação de teste para o número de telefone do cliente associado ao pedido.

### Aba de Logs

Nesta aba, você pode visualizar um histórico de todas as notificações enviadas, incluindo:

*   **ID do Pedido:** O ID do pedido associado à notificação.
*   **Tipo de Mensagem:** O tipo de mensagem enviada (ex: `whatsapp_notification`).
*   **Status:** O status do envio da notificação (`sent`, `error`, etc.).
*   **Conteúdo da Mensagem:** O conteúdo exato da mensagem enviada.
*   **Dados de Resposta:** A resposta recebida do servidor do Joinotify.
*   **Data de Criação:** A data e hora em que a notificação foi enviada.

## Placeholders Disponíveis

Utilize os seguintes placeholders no seu template de mensagem para inserir informações dinâmicas:

*   `{{ customer_name }}`: Nome do cliente.
*   `{{ order_number }}`: Número do pedido.
*   `{{ booking_id }}`: ID da reserva.
*   `{{ booking_details }}`: Detalhes completos da reserva, incluindo ID da reserva, nome do produto, datas, horários, pessoas e recursos.
*   `{{ booking_dates }}`: Apenas as datas e horários da reserva.
*   `{{ booking_persons }}`: Informações sobre as pessoas na reserva.
*   `{{ booking_resources }}`: Informações sobre os recursos/locais da reserva.
*   `{{ product_addons }}`: Lista dos add-ons selecionados com ícones.
*   `{{ addons_summary }}`: Resumo dos add-ons com valores.
*   `{{ order_total }}`: Valor total do pedido.
*   `{{ site_name }}`: Nome do seu site.

## Perguntas Frequentes (FAQ)

*   **O plugin funciona com qualquer tema do WordPress?**
    *   Sim, o plugin é compatível com qualquer tema do WordPress que siga as boas práticas de desenvolvimento do WordPress e do WooCommerce.

*   **Posso personalizar a aparência das mensagens do WhatsApp?**
    *   Sim, você pode personalizar completamente o conteúdo das mensagens do WhatsApp usando o campo `Template de Mensagem` nas configurações do plugin. Você pode usar formatação de texto básica do WhatsApp (negrito, itálico, etc.) e os placeholders disponíveis.

*   **O que acontece se um cliente não tiver WhatsApp?**
    *   O plugin tentará enviar a notificação para o número de telefone fornecido no pedido. Se o número não for um número de WhatsApp válido, a notificação não será entregue. É importante garantir que seus clientes forneçam um número de telefone válido com WhatsApp.

*   **Como posso obter suporte para o plugin?**
    *   Para suporte técnico, você pode consultar a documentação oficial do Joinotify e do WooCommerce, ou entrar em contato com o desenvolvedor do plugin através dos canais de suporte apropriados.

## Changelog (Histórico de Versões)

O changelog completo do plugin pode ser encontrado no arquivo `readme.txt` e no arquivo principal do plugin (`joinotify-booking-integration.php`).

Esperamos que este manual seja útil para você aproveitar ao máximo o plugin **Joinotify Booking Integration**!

