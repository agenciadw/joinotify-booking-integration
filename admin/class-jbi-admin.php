<?php
/**
 * Classe para interface administrativa
 *
 * @package JoinotifyBookingIntegration
 */

// Evita acesso direto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe JBI_Admin
 */
class JBI_Admin {

    /**
     * Instância única da classe
     */
    private static $instance = null;

    /**
     * Obtém a instância única da classe
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Construtor privado
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Inicializa os hooks
     */
    private function init_hooks() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'wp_ajax_jbi_test_notification', array( $this, 'ajax_test_notification' ) );
        add_action( 'wp_ajax_jbi_clear_logs', array( $this, 'ajax_clear_logs' ) );
        add_filter( 'plugin_action_links_' . JBI_PLUGIN_BASENAME, array( $this, 'add_plugin_action_links' ) );
    }

    /**
     * Adiciona menu administrativo
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'Joinotify Booking Integration', 'joinotify-booking-integration' ),
            __( 'Joinotify Booking', 'joinotify-booking-integration' ),
            'manage_woocommerce',
            'joinotify-booking-integration',
            array( $this, 'admin_page' )
        );
    }

    /**
     * Registra configurações
     */
    public function register_settings() {
        register_setting( 'jbi_settings_group', 'jbi_settings', array( $this, 'sanitize_settings' ) );
    }

    /**
     * Sanitiza configurações
     */
    public function sanitize_settings( $input ) {
        return JBI_Settings::sanitize_settings( $input );
    }

    /**
     * Carrega scripts administrativos
     */
    public function enqueue_admin_scripts( $hook ) {
        if ( 'woocommerce_page_joinotify-booking-integration' !== $hook ) {
            return;
        }

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 
            'jbi-admin', 
            JBI_PLUGIN_URL . 'assets/js/admin.js', 
            array( 'jquery' ), 
            JBI_VERSION, 
            true 
        );

        wp_localize_script( 'jbi-admin', 'jbi_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'jbi_admin_nonce' ),
            'strings' => array(
                'test_notification_success' => __( 'Notificação de teste enviada com sucesso!', 'joinotify-booking-integration' ),
                'test_notification_error' => __( 'Erro ao enviar notificação de teste.', 'joinotify-booking-integration' ),
                'logs_cleared' => __( 'Logs limpos com sucesso!', 'joinotify-booking-integration' ),
                'confirm_clear_logs' => __( 'Tem certeza que deseja limpar todos os logs?', 'joinotify-booking-integration' )
            )
        ));

        wp_enqueue_style( 
            'jbi-admin', 
            JBI_PLUGIN_URL . 'assets/css/admin.css', 
            array(), 
            JBI_VERSION 
        );
    }

    /**
     * Página administrativa principal
     */
    public function admin_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'settings';
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=joinotify-booking-integration&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php _e( 'Configurações', 'joinotify-booking-integration' ); ?>
                </a>
                <a href="?page=joinotify-booking-integration&tab=test" class="nav-tab <?php echo $active_tab === 'test' ? 'nav-tab-active' : ''; ?>">
                    <?php _e( 'Teste', 'joinotify-booking-integration' ); ?>
                </a>
                <a href="?page=joinotify-booking-integration&tab=logs" class="nav-tab <?php echo $active_tab === 'logs' ? 'nav-tab-active' : ''; ?>">
                    <?php _e( 'Logs', 'joinotify-booking-integration' ); ?>
                </a>
                <a href="?page=joinotify-booking-integration&tab=help" class="nav-tab <?php echo $active_tab === 'help' ? 'nav-tab-active' : ''; ?>">
                    <?php _e( 'Ajuda', 'joinotify-booking-integration' ); ?>
                </a>
            </nav>

            <div class="tab-content">
                <?php
                switch ( $active_tab ) {
                    case 'settings':
                        $this->settings_tab();
                        break;
                    case 'test':
                        $this->test_tab();
                        break;
                    case 'logs':
                        $this->logs_tab();
                        break;
                    case 'help':
                        $this->help_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Aba de configurações
     */
    private function settings_tab() {
        $settings = JBI_Settings::get_settings();
        $available_statuses = JBI_Settings::get_available_statuses();
        
        ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'jbi_settings_group' ); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="sender_number"><?php _e( 'Número do Remetente', 'joinotify-booking-integration' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="sender_number" name="jbi_settings[sender_number]" value="<?php echo esc_attr( $settings['sender_number'] ); ?>" class="regular-text" placeholder="5541912345678" />
                        <p class="description"><?php _e( 'Número de telefone registrado em sua conta MeuMouse.com (formato internacional)', 'joinotify-booking-integration' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e( 'Status para Notificação', 'joinotify-booking-integration' ); ?></th>
                    <td>
                        <fieldset>
                            <?php foreach ( $available_statuses as $status => $label ) : ?>
                                <label>
                                    <input type="checkbox" name="jbi_settings[notification_statuses][]" value="<?php echo esc_attr( $status ); ?>" <?php checked( in_array( $status, $settings['notification_statuses'] ) ); ?> />
                                    <?php echo esc_html( $label ); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php _e( 'Selecione os status de pedido que devem enviar notificações automáticas', 'joinotify-booking-integration' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="notification_delay"><?php _e( 'Atraso da Notificação', 'joinotify-booking-integration' ); ?></label>
                    </th>
                    <td>
                        <input type="number" id="notification_delay" name="jbi_settings[notification_delay]" value="<?php echo esc_attr( $settings['notification_delay'] ); ?>" min="0" max="300" /> <?php _e( 'segundos', 'joinotify-booking-integration' ); ?>
                        <p class="description"><?php _e( 'Tempo de espera antes de enviar a notificação (0 para envio imediato)', 'joinotify-booking-integration' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e( 'Integração com Email', 'joinotify-booking-integration' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="jbi_settings[enable_email_integration]" value="1" <?php checked( $settings['enable_email_integration'] ); ?> />
                            <?php _e( 'Adicionar detalhes de reserva aos emails do WooCommerce', 'joinotify-booking-integration' ); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e( 'Logs de Debug', 'joinotify-booking-integration' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="jbi_settings[enable_debug_logs]" value="1" <?php checked( $settings['enable_debug_logs'] ); ?> />
                            <?php _e( 'Ativar logs detalhados para debugging', 'joinotify-booking-integration' ); ?>
                        </label>
                        <p class="description"><?php _e( 'Ative apenas para resolução de problemas. Pode gerar muitos dados.', 'joinotify-booking-integration' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="message_template"><?php _e( 'Template de Mensagem', 'joinotify-booking-integration' ); ?></label>
                    </th>
                    <td>
                        <textarea id="message_template" name="jbi_settings[message_template]" rows="10" cols="50" class="large-text"><?php echo esc_textarea( $settings['message_template'] ); ?></textarea>
                        <p class="description">
                            <?php _e( 'Personalize a mensagem usando os placeholders disponíveis:', 'joinotify-booking-integration' ); ?><br>
                            <code>{{ customer_name }}</code> - <?php _e( 'Nome do cliente', 'joinotify-booking-integration' ); ?><br>
                            <code>{{ order_number }}</code> - <?php _e( 'Número do pedido', 'joinotify-booking-integration' ); ?><br>
                            <code>{{ booking_details }}</code> - <?php _e( 'Detalhes da reserva', 'joinotify-booking-integration' ); ?><br>
                            <code>{{ product_addons }}</code> - <?php _e( 'Add-ons selecionados', 'joinotify-booking-integration' ); ?><br>
                            <code>{{ order_total }}</code> - <?php _e( 'Valor total do pedido', 'joinotify-booking-integration' ); ?><br>
                            <code>{{ site_name }}</code> - <?php _e( 'Nome do site', 'joinotify-booking-integration' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }

    /**
     * Aba de teste
     */
    private function test_tab() {
        ?>
        <div class="jbi-test-section">
            <h3><?php _e( 'Teste de Notificação', 'joinotify-booking-integration' ); ?></h3>
            <p><?php _e( 'Teste o envio de notificações usando um pedido existente com reservas.', 'joinotify-booking-integration' ); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="test_order_id"><?php _e( 'ID do Pedido', 'joinotify-booking-integration' ); ?></label>
                    </th>
                    <td>
                        <input type="number" id="test_order_id" class="regular-text" placeholder="123" />
                        <button type="button" id="send_test_notification" class="button button-secondary"><?php _e( 'Enviar Teste', 'joinotify-booking-integration' ); ?></button>
                        <p class="description"><?php _e( 'Digite o ID de um pedido que contenha reservas para testar a notificação', 'joinotify-booking-integration' ); ?></p>
                    </td>
                </tr>
            </table>
            
            <div id="test_result" style="margin-top: 20px;"></div>
        </div>

        <div class="jbi-placeholders-section" style="margin-top: 40px;">
            <h3><?php _e( 'Placeholders Disponíveis', 'joinotify-booking-integration' ); ?></h3>
            <p><?php _e( 'Use estes placeholders em suas mensagens personalizadas do Joinotify:', 'joinotify-booking-integration' ); ?></p>
            
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e( 'Placeholder', 'joinotify-booking-integration' ); ?></th>
                        <th><?php _e( 'Descrição', 'joinotify-booking-integration' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>{{ booking_details }}</code></td>
                        <td><?php _e( 'Detalhes completos de todas as reservas', 'joinotify-booking-integration' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>{{ booking_dates }}</code></td>
                        <td><?php _e( 'Datas e horários das reservas', 'joinotify-booking-integration' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>{{ booking_persons }}</code></td>
                        <td><?php _e( 'Informações de pessoas nas reservas', 'joinotify-booking-integration' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>{{ booking_resources }}</code></td>
                        <td><?php _e( 'Recursos/locais selecionados', 'joinotify-booking-integration' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>{{ product_addons }}</code></td>
                        <td><?php _e( 'Lista de add-ons selecionados', 'joinotify-booking-integration' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>{{ addons_summary }}</code></td>
                        <td><?php _e( 'Resumo dos add-ons com valores', 'joinotify-booking-integration' ); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Aba de logs
     */
    private function logs_tab() {
        global $wpdb;
        
        $logs = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}jbi_logs ORDER BY created_at DESC LIMIT 100"
        );
        
        ?>
        <div class="jbi-logs-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3><?php _e( 'Logs do Sistema', 'joinotify-booking-integration' ); ?></h3>
                <button type="button" id="clear_logs" class="button button-secondary"><?php _e( 'Limpar Logs', 'joinotify-booking-integration' ); ?></button>
            </div>
            
            <?php if ( empty( $logs ) ) : ?>
                <p><?php _e( 'Nenhum log encontrado.', 'joinotify-booking-integration' ); ?></p>
            <?php else : ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e( 'Data/Hora', 'joinotify-booking-integration' ); ?></th>
                            <th><?php _e( 'Pedido', 'joinotify-booking-integration' ); ?></th>
                            <th><?php _e( 'Tipo', 'joinotify-booking-integration' ); ?></th>
                            <th><?php _e( 'Status', 'joinotify-booking-integration' ); ?></th>
                            <th><?php _e( 'Mensagem', 'joinotify-booking-integration' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $logs as $log ) : ?>
                            <tr>
                                <td><?php echo esc_html( $log->created_at ); ?></td>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $log->order_id . '&action=edit' ) ); ?>">
                                        #<?php echo esc_html( $log->order_id ); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html( $log->message_type ); ?></td>
                                <td>
                                    <span class="status-<?php echo esc_attr( $log->status ); ?>">
                                        <?php echo esc_html( $log->status ); ?>
                                    </span>
                                </td>
                                <td>
                                    <details>
                                        <summary><?php _e( 'Ver detalhes', 'joinotify-booking-integration' ); ?></summary>
                                        <pre><?php echo esc_html( $log->message_content ); ?></pre>
                                    </details>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Aba de ajuda
     */
    private function help_tab() {
        $hpos_info = JBI_Settings::get_hpos_info();
        
        ?>
        <div class="jbi-help-section">
            <h3><?php _e( 'Como Usar', 'joinotify-booking-integration' ); ?></h3>
            
            <div class="jbi-help-cards">
                <div class="jbi-help-card">
                    <h4><?php _e( '1. Configuração Inicial', 'joinotify-booking-integration' ); ?></h4>
                    <p><?php _e( 'Configure seu número de telefone registrado no MeuMouse.com na aba Configurações.', 'joinotify-booking-integration' ); ?></p>
                </div>
                
                <div class="jbi-help-card">
                    <h4><?php _e( '2. Placeholders Automáticos', 'joinotify-booking-integration' ); ?></h4>
                    <p><?php _e( 'Os placeholders são adicionados automaticamente ao Joinotify. Use-os em suas mensagens personalizadas.', 'joinotify-booking-integration' ); ?></p>
                </div>
                
                <div class="jbi-help-card">
                    <h4><?php _e( '3. Notificações Automáticas', 'joinotify-booking-integration' ); ?></h4>
                    <p><?php _e( 'As notificações são enviadas automaticamente quando o status do pedido muda para os status configurados.', 'joinotify-booking-integration' ); ?></p>
                </div>
                
                <div class="jbi-help-card">
                    <h4><?php _e( '4. Compatibilidade HPOS', 'joinotify-booking-integration' ); ?></h4>
                    <p>
                        <?php _e( 'Este plugin é totalmente compatível com o High-Performance Order Storage (HPOS) do WooCommerce.', 'joinotify-booking-integration' ); ?>
                        <br><br>
                        <strong><?php _e( 'Status HPOS:', 'joinotify-booking-integration' ); ?></strong>
                        <?php if ( $hpos_info['enabled'] ) : ?>
                            <span style="color: #46b450;">✅ <?php _e( 'Habilitado', 'joinotify-booking-integration' ); ?></span>
                        <?php else : ?>
                            <span style="color: #ffb900;">⚠️ <?php _e( 'Desabilitado', 'joinotify-booking-integration' ); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <h3><?php _e( 'Requisitos', 'joinotify-booking-integration' ); ?></h3>
            <ul>
                <li><?php _e( 'WooCommerce ativo', 'joinotify-booking-integration' ); ?></li>
                <li><?php _e( 'WooCommerce Bookings ativo', 'joinotify-booking-integration' ); ?></li>
                <li><?php _e( 'WooCommerce Product Add-ons ativo', 'joinotify-booking-integration' ); ?></li>
                <li><?php _e( 'Joinotify ativo e configurado', 'joinotify-booking-integration' ); ?></li>
                <li><?php _e( 'Conta ativa no MeuMouse.com', 'joinotify-booking-integration' ); ?></li>
            </ul>
            
            <h3><?php _e( 'Compatibilidade HPOS', 'joinotify-booking-integration' ); ?></h3>
            <div class="jbi-hpos-status">
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e( 'Recurso', 'joinotify-booking-integration' ); ?></th>
                            <th><?php _e( 'Status', 'joinotify-booking-integration' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php _e( 'HPOS Habilitado na Loja', 'joinotify-booking-integration' ); ?></td>
                            <td>
                                <?php if ( $hpos_info['enabled'] ) : ?>
                                    <span style="color: #46b450;">✅ <?php _e( 'Sim', 'joinotify-booking-integration' ); ?></span>
                                <?php else : ?>
                                    <span style="color: #ffb900;">⚠️ <?php _e( 'Não', 'joinotify-booking-integration' ); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e( 'Plugin Compatível com HPOS', 'joinotify-booking-integration' ); ?></td>
                            <td><span style="color: #46b450;">✅ <?php _e( 'Sim', 'joinotify-booking-integration' ); ?></span></td>
                        </tr>
                        <tr>
                            <td><?php _e( 'APIs HPOS Disponíveis', 'joinotify-booking-integration' ); ?></td>
                            <td>
                                <?php if ( $hpos_info['class_exists'] ) : ?>
                                    <span style="color: #46b450;">✅ <?php _e( 'Sim', 'joinotify-booking-integration' ); ?></span>
                                <?php else : ?>
                                    <span style="color: #dc3232;">❌ <?php _e( 'Não', 'joinotify-booking-integration' ); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php if ( ! $hpos_info['enabled'] ) : ?>
                    <p class="description">
                        <?php _e( 'O HPOS não está habilitado em sua loja, mas o plugin funcionará normalmente com as tabelas tradicionais do WordPress.', 'joinotify-booking-integration' ); ?>
                        <a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=advanced&section=features' ); ?>" target="_blank">
                            <?php _e( 'Habilitar HPOS', 'joinotify-booking-integration' ); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
            
            <h3><?php _e( 'Suporte', 'joinotify-booking-integration' ); ?></h3>
            <p><?php _e( 'Para suporte técnico, consulte a documentação oficial dos plugins ou entre em contato com desenvolvedores especializados.', 'joinotify-booking-integration' ); ?></p>
        </div>
        <?php
    }

    /**
     * AJAX para teste de notificação
     */
    public function ajax_test_notification() {
        // Verifica nonce
        if ( ! check_ajax_referer( 'jbi_admin_nonce', 'nonce', false ) ) {
            wp_send_json( array( 
                'success' => false, 
                'message' => __( 'Token de segurança inválido. Recarregue a página e tente novamente.', 'joinotify-booking-integration' ) 
            ) );
        }
        
        // Verifica permissões
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json( array( 
                'success' => false, 
                'message' => __( 'Permissão negada', 'joinotify-booking-integration' ) 
            ) );
        }
        
        // Verifica se order_id foi enviado
        if ( ! isset( $_POST['order_id'] ) ) {
            wp_send_json( array( 
                'success' => false, 
                'message' => __( 'ID do pedido não fornecido', 'joinotify-booking-integration' ) 
            ) );
        }
        
        $order_id = intval( $_POST['order_id'] );
        
        // Validação adicional
        if ( $order_id <= 0 ) {
            wp_send_json( array( 
                'success' => false, 
                'message' => __( 'ID do pedido deve ser um número positivo', 'joinotify-booking-integration' ) 
            ) );
        }
        
        try {
            $result = JBI_Notifications::send_test_notification( $order_id );
            wp_send_json( $result );
        } catch ( Exception $e ) {
            wp_send_json( array( 
                'success' => false, 
                'message' => sprintf( __( 'Erro inesperado: %s', 'joinotify-booking-integration' ), $e->getMessage() )
            ) );
        }
    }

    /**
     * AJAX para limpar logs
     */
    public function ajax_clear_logs() {
        // Verifica nonce
        if ( ! check_ajax_referer( 'jbi_admin_nonce', 'nonce', false ) ) {
            wp_send_json( array( 
                'success' => false, 
                'message' => __( 'Token de segurança inválido. Recarregue a página e tente novamente.', 'joinotify-booking-integration' ) 
            ) );
        }
        
        // Verifica permissões
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json( array( 
                'success' => false, 
                'message' => __( 'Permissão negada', 'joinotify-booking-integration' ) 
            ) );
        }
        
        try {
            global $wpdb;
            $result = $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}jbi_logs" );
            
            wp_send_json( array( 
                'success' => $result !== false,
                'message' => $result !== false ? 
                    __( 'Logs limpos com sucesso', 'joinotify-booking-integration' ) : 
                    __( 'Erro ao limpar logs', 'joinotify-booking-integration' )
            ) );
        } catch ( Exception $e ) {
            wp_send_json( array( 
                'success' => false, 
                'message' => sprintf( __( 'Erro ao limpar logs: %s', 'joinotify-booking-integration' ), $e->getMessage() )
            ) );
        }
    }

    /**
     * Adiciona links de ação ao plugin
     */
    public function add_plugin_action_links( $links ) {
        $settings_link = '<a href="' . admin_url( 'admin.php?page=joinotify-booking-integration' ) . '">' . __( 'Configurações', 'joinotify-booking-integration' ) . '</a>';
        array_unshift( $links, $settings_link );
        
        return $links;
    }
}

