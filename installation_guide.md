# Guia de Instalação do Joinotify Booking Integration

Este guia detalha o processo de instalação e configuração inicial do plugin **Joinotify Booking Integration** para WordPress.

## Requisitos Mínimos

Antes de instalar o plugin, certifique-se de que seu ambiente atende aos seguintes requisitos:

*   **WordPress:** Versão 5.0 ou superior
*   **WooCommerce:** Versão 6.0 ou superior
*   **WooCommerce Bookings:** Plugin oficial do WooCommerce Bookings instalado e ativo
*   **WooCommerce Product Add-ons:** Plugin oficial do WooCommerce Product Add-ons instalado e ativo
*   **Joinotify:** Plugin Joinotify instalado e ativo
*   **Conta Ativa no MeuMouse.com:** Para o funcionamento do Joinotify, é necessário ter uma conta ativa e configurada no MeuMouse.com.

## Passos para Instalação

Siga os passos abaixo para instalar o plugin:

1.  **Download do Plugin:**
    *   Baixe o arquivo ZIP do plugin fornecido.

2.  **Upload via WordPress:**
    *   Acesse o painel administrativo do seu WordPress.
    *   Navegue até `Plugins > Adicionar Novo`.
    *   Clique no botão `Fazer Upload do Plugin`.
    *   Clique em `Escolher arquivo` e selecione o arquivo ZIP do `joinotify-booking-integration.zip` que você baixou.
    *   Clique em `Instalar Agora`.

3.  **Ativação do Plugin:**
    *   Após a instalação, clique em `Ativar Plugin`.

4.  **Instalação Manual (Alternativa - via FTP/SFTP):**
    *   Descompacte o arquivo `joinotify-booking-integration.zip`.
    *   Conecte-se ao seu servidor WordPress via FTP ou SFTP.
    *   Navegue até o diretório `/wp-content/plugins/`.
    *   Faça upload da pasta descompactada `joinotify-booking-integration` para este diretório.
    *   Acesse o painel administrativo do seu WordPress.
    *   Navegue até `Plugins > Plugins Instalados`.
    *   Localize o **Joinotify Booking Integration** na lista e clique em `Ativar`.

## Configuração Inicial

Após a instalação e ativação, siga estes passos para configurar o plugin:

1.  **Acessar as Configurações:**
    *   No painel administrativo do WordPress, navegue até `WooCommerce > Joinotify Booking`.

2.  **Configurar Número do Remetente:**
    *   No campo `Número do Remetente`, insira o número de telefone registrado na sua conta MeuMouse.com (com código do país, ex: `5511987654321`). Este será o número que enviará as mensagens do WhatsApp.

3.  **Status de Notificação:**
    *   Selecione os status de pedido do WooCommerce para os quais você deseja que as notificações do WhatsApp sejam enviadas. Por padrão, `Processando`, `Concluído` e `Confirmado` já vêm selecionados.

4.  **Atraso da Notificação (Opcional):**
    *   Defina um atraso em segundos para o envio das notificações após a mudança de status do pedido. O padrão é 30 segundos.

5.  **Integração com E-mail (Opcional):**
    *   Marque a opção `Habilitar Integração com E-mail` se desejar que os detalhes da reserva e dos add-ons também sejam incluídos nos e-mails transacionais do WooCommerce.

6.  **Logs de Depuração (Opcional):**
    *   Marque `Habilitar Logs de Depuração` para registrar informações detalhadas sobre o envio de notificações. Útil para solucionar problemas.

7.  **Template de Mensagem (Importante!):**
    *   No campo `Template de Mensagem`, você pode personalizar o conteúdo das mensagens do WhatsApp. Utilize os placeholders disponíveis para inserir informações dinâmicas do pedido e da reserva. Os placeholders disponíveis são:
        *   `{{ customer_name }}`: Nome do cliente
        *   `{{ order_number }}`: Número do pedido
        *   `{{ booking_id }}`: ID da reserva (novo!)
        *   `{{ booking_details }}`: Detalhes completos da reserva (incluindo ID da reserva, nome do produto, datas, horários, pessoas e recursos)
        *   `{{ booking_dates }}`: Apenas as datas e horários da reserva
        *   `{{ booking_persons }}`: Informações sobre as pessoas na reserva
        *   `{{ booking_resources }}`: Informações sobre os recursos/locais da reserva
        *   `{{ product_addons }}`: Lista dos add-ons selecionados com ícones
        *   `{{ addons_summary }}`: Resumo dos add-ons com valores
        *   `{{ order_total }}`: Valor total do pedido
        *   `{{ site_name }}`: Nome do seu site

    *   **Exemplo de Template Padrão:**
        ```
        🎉 Olá {{ customer_name }}!

        ✅ Sua reserva foi confirmada com sucesso!

        📋 **Detalhes da Reserva #{{ booking_id }}**

        {{ booking_details }}

        🎁 **Extras selecionados:**
        {{ product_addons }}

        💰 **Valor total:** {{ order_total }}

        📞 Em caso de dúvidas, entre em contato conosco.

        Obrigado por escolher {{ site_name }}! 🙏
        ```

8.  **Salvar Alterações:**
    *   Após configurar todas as opções, clique em `Salvar Alterações`.

## Testando o Funcionamento

É altamente recomendável testar o funcionamento do plugin após a configuração:

1.  **Crie um Pedido de Teste:**
    *   Faça um pedido de teste no seu site que inclua um produto com reserva e, se aplicável, add-ons.

2.  **Envie uma Notificação de Teste:**
    *   No painel de configurações do Joinotify Booking Integration, na aba `Teste de Notificação` (ou similar, dependendo da versão do plugin).
    *   Insira o ID do pedido de teste que você acabou de criar.
    *   Clique em `Enviar Notificação de Teste`.

3.  **Verifique o WhatsApp:**
    *   Confirme se a mensagem do WhatsApp foi recebida no número de telefone do cliente do pedido de teste, com todos os detalhes formatados corretamente.

## Solução de Problemas Comuns

*   **Mensagens não são enviadas:**
    *   Verifique se o plugin Joinotify está ativo e configurado corretamente.
    *   Confira se o número do remetente está correto nas configurações do Joinotify Booking Integration e se corresponde ao número registrado no MeuMouse.com.
    *   Verifique os logs de depuração do plugin para identificar erros (`WooCommerce > Status > Logs` ou na aba de logs do plugin, se disponível).
    *   Certifique-se de que o status do pedido aciona a notificação (verifique as configurações de `Status de Notificação`).

*   **Placeholders não são substituídos:**
    *   Verifique se os placeholders estão digitados corretamente (ex: `{{ booking_details }}`).
    *   Certifique-se de que o pedido de teste possui os dados correspondentes (ex: uma reserva real para `{{ booking_details }}`).

*   **Erro fatal após atualização:**
    *   Se ocorrer um erro fatal após a atualização, desative e reative o plugin. Se o problema persistir, verifique os logs de erro do seu servidor ou entre em contato com o suporte.

Para suporte adicional, consulte a documentação oficial do Joinotify e do WooCommerce Bookings, ou entre em contato com o desenvolvedor do plugin.

