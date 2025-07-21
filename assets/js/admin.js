jQuery(document).ready(function($) {
    
    // Teste de notificação
    $('#send_test_notification').on('click', function() {
        var button = $(this);
        var orderId = $('#test_order_id').val();
        var resultDiv = $('#test_result');
        
        // Validação no frontend
        if (!orderId || orderId.trim() === '') {
            alert('Por favor, digite um ID de pedido válido.');
            return;
        }
        
        if (isNaN(orderId) || parseInt(orderId) <= 0) {
            alert('O ID do pedido deve ser um número positivo.');
            return;
        }
        
        button.addClass('loading').prop('disabled', true);
        resultDiv.hide();
        
        // Debug: log da requisição
        console.log('Enviando requisição AJAX:', {
            url: jbi_ajax.ajax_url,
            action: 'jbi_test_notification',
            order_id: orderId,
            nonce: jbi_ajax.nonce
        });
        
        $.ajax({
            url: jbi_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            timeout: 30000, // 30 segundos de timeout
            data: {
                action: 'jbi_test_notification',
                order_id: orderId,
                nonce: jbi_ajax.nonce
            },
            success: function(response) {
                console.log('Resposta recebida:', response);
                
                if (response && typeof response === 'object') {
                    if (response.success) {
                        resultDiv.removeClass('error').addClass('success')
                               .html('<strong>Sucesso:</strong> ' + response.message)
                               .show();
                    } else {
                        resultDiv.removeClass('success').addClass('error')
                               .html('<strong>Erro:</strong> ' + (response.message || 'Erro desconhecido'))
                               .show();
                    }
                } else {
                    resultDiv.removeClass('success').addClass('error')
                           .html('<strong>Erro:</strong> Resposta inválida do servidor.')
                           .show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro AJAX:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                
                var errorMessage = 'Falha na comunicação com o servidor.';
                
                if (status === 'timeout') {
                    errorMessage = 'Timeout: O servidor demorou muito para responder.';
                } else if (status === 'parsererror') {
                    errorMessage = 'Erro ao processar resposta do servidor.';
                } else if (xhr.status === 403) {
                    errorMessage = 'Acesso negado. Verifique suas permissões.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Endpoint não encontrado. Verifique se o plugin está ativo.';
                } else if (xhr.status >= 500) {
                    errorMessage = 'Erro interno do servidor (código ' + xhr.status + ').';
                }
                
                resultDiv.removeClass('success').addClass('error')
                       .html('<strong>Erro:</strong> ' + errorMessage)
                       .show();
            },
            complete: function() {
                button.removeClass('loading').prop('disabled', false);
            }
        });
    });
    
    // Limpar logs
    $('#clear_logs').on('click', function() {
        if (!confirm(jbi_ajax.strings.confirm_clear_logs)) {
            return;
        }
        
        var button = $(this);
        button.addClass('loading').prop('disabled', true);
        
        $.ajax({
            url: jbi_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            timeout: 15000,
            data: {
                action: 'jbi_clear_logs',
                nonce: jbi_ajax.nonce
            },
            success: function(response) {
                if (response && response.success) {
                    alert(jbi_ajax.strings.logs_cleared);
                    location.reload();
                } else {
                    alert('Erro ao limpar logs: ' + (response.message || 'Erro desconhecido'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro ao limpar logs:', {status: status, error: error});
                alert('Erro na comunicação com o servidor ao limpar logs.');
            },
            complete: function() {
                button.removeClass('loading').prop('disabled', false);
            }
        });
    });
    
    // Validação do número de telefone
    $('#sender_number').on('blur', function() {
        var phone = $(this).val();
        var cleanPhone = phone.replace(/[^0-9]/g, '');
        
        if (cleanPhone.length > 0 && cleanPhone.length < 10) {
            $(this).css('border-color', '#dc3232');
            $(this).next('.description').html('<span style="color: #dc3232;">Número muito curto. Use o formato internacional (ex: 5541912345678)</span>');
        } else if (cleanPhone.length > 0 && !cleanPhone.startsWith('55')) {
            $(this).css('border-color', '#ffb900');
            $(this).next('.description').html('<span style="color: #ffb900;">Recomendado usar código do país 55 para Brasil</span>');
        } else {
            $(this).css('border-color', '');
            $(this).next('.description').html('Número de telefone registrado em sua conta MeuMouse.com (formato internacional)');
        }
    });
    
    // Copiar placeholder para clipboard
    $('.jbi-placeholders-section code').on('click', function() {
        var text = $(this).text();
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showTooltip($(this), 'Copiado!');
            }.bind(this));
        } else {
            // Fallback para navegadores mais antigos
            var textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showTooltip($(this), 'Copiado!');
        }
    });
    
    // Função para mostrar tooltip
    function showTooltip(element, message) {
        var tooltip = $('<div class="jbi-tooltip">' + message + '</div>');
        tooltip.css({
            position: 'absolute',
            background: '#333',
            color: '#fff',
            padding: '5px 10px',
            borderRadius: '4px',
            fontSize: '12px',
            zIndex: 9999,
            pointerEvents: 'none'
        });
        
        $('body').append(tooltip);
        
        var offset = element.offset();
        tooltip.css({
            top: offset.top - tooltip.outerHeight() - 5,
            left: offset.left + (element.outerWidth() / 2) - (tooltip.outerWidth() / 2)
        });
        
        setTimeout(function() {
            tooltip.fadeOut(function() {
                tooltip.remove();
            });
        }, 2000);
    }
    
    // Adicionar cursor pointer aos códigos
    $('.jbi-placeholders-section code').css('cursor', 'pointer');
    
    // Validação do formulário antes do envio
    $('form').on('submit', function() {
        var senderNumber = $('#sender_number').val();
        
        if (senderNumber) {
            var cleanPhone = senderNumber.replace(/[^0-9]/g, '');
            if (cleanPhone.length < 10) {
                alert('Por favor, insira um número de telefone válido.');
                return false;
            }
        }
        
        return true;
    });
    
    // Auto-save das configurações (opcional)
    var autoSaveTimeout;
    $('.form-table input, .form-table select, .form-table textarea').on('change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            // Aqui você pode implementar auto-save se desejar
            console.log('Configurações alteradas');
        }, 2000);
    });
    
    // Expandir/colapsar detalhes dos logs
    $('.jbi-logs-section details').on('toggle', function() {
        if (this.open) {
            $(this).find('pre').css('max-height', 'none');
        }
    });
    
    // Filtro de logs por tipo (se implementado no futuro)
    if ($('#log_filter').length) {
        $('#log_filter').on('change', function() {
            var filterValue = $(this).val();
            $('.jbi-logs-section tbody tr').each(function() {
                var logType = $(this).find('td:nth-child(3)').text();
                if (filterValue === '' || logType === filterValue) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    }
    
    // Debug: verificar se as variáveis AJAX estão disponíveis
    if (typeof jbi_ajax === 'undefined') {
        console.error('Variáveis AJAX não carregadas. Verifique se o script está sendo carregado corretamente.');
    } else {
        console.log('Variáveis AJAX carregadas:', jbi_ajax);
    }
});

