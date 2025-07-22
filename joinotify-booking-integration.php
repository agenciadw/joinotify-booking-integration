<?php
/**
 * Plugin Name: Joinotify Booking Integration
 * Plugin URI: https://github.com/agenciadw/joinotify-booking-integration
 * Description: Integração dinâmica entre WooCommerce Booking, Product Add-ons e Joinotify para notificações WhatsApp completas e automáticas.
 * Version: 1.1.0
 * Author: David William da Costa
 * Author URI: https://github.com/agenciadw
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: joinotify-booking-integration
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.5
 *
 * @package JoinotifyBookingIntegration
 */

// Adiciona links extras abaixo do nome do plugin
add_filter( 'plugin_row_meta', 'joinotify_booking_integration_row_meta', 10, 2 );
function joinotify_booking_integration_row_meta( $links, $file ) {
    if ( plugin_basename( __FILE__ ) === $file ) {
        $custom_links = array(
            '<a href="https://github.com/agenciadw/joinotify-booking-integration/blob/main/plugin_manual.md" target="_blank" rel="noopener noreferrer">Documentação</a>',
            '<a href="https://github.com/agenciadw/joinotify-booking-integration/blob/main/installation_guide.md" target="_blank" rel="noopener noreferrer">Guia de Instalação</a>',
        );
        // Coloca os links personalizados após os padrões
        return array_merge( $links, $custom_links );
    }
    return $links;
}

// Evita acesso direto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Declara compatibilidade com HPOS (High-Performance Order Storage)
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

// Define constantes do plugin
if ( ! defined( 'JBI_VERSION' ) ) {
    define( 'JBI_VERSION', '1.1.0' );
}
if ( ! defined( 'JBI_PLUGIN_FILE' ) ) {
    define( 'JBI_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'JBI_PLUGIN_DIR' ) ) {
    define( 'JBI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'JBI_PLUGIN_URL' ) ) {
    define( 'JBI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'JBI_PLUGIN_BASENAME' ) ) {
    define( 'JBI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * Classe principal do plugin
 */
class Joinotify_Booking_Integration {

    /**
     * Instância única do plugin
     */
    private static $instance = null;

    /**
     * Obtém a instância única do plugin
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Construtor privado para implementar singleton
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Inicializa os hooks do plugin
     */
    private function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        add_action( 'init', array( $this, 'load_textdomain' ) );
        
        // Hooks de ativação e desativação
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }

    /**
     * Inicializa o plugin
     */
    public function init() {
        // Verifica dependências
        if ( ! $this->check_dependencies() ) {
            return;
        }

        // Carrega arquivos necessários
        $this->load_includes();

        // Inicializa componentes
        $this->init_components();
    }

    /**
     * Carrega arquivos de tradução
     */
    public function load_textdomain() {
        load_plugin_textdomain( 
            'joinotify-booking-integration', 
            false, 
            dirname( plugin_basename( __FILE__ ) ) . '/languages/' 
        );
    }

    /**
     * Verifica se as dependências estão ativas
     */
    private function check_dependencies() {
        $dependencies = array(
            'woocommerce/woocommerce.php' => 'WooCommerce',
            'woocommerce-bookings/woocommerce-bookings.php' => 'WooCommerce Bookings',
            'woocommerce-product-addons/woocommerce-product-addons.php' => 'WooCommerce Product Add-ons',
            'joinotify/joinotify.php' => 'Joinotify'
        );

        $missing = array();

        foreach ( $dependencies as $plugin => $name ) {
            if ( ! is_plugin_active( $plugin ) ) {
                $missing[] = $name;
            }
        }

        if ( ! empty( $missing ) ) {
            add_action( 'admin_notices', function() use ( $missing ) {
                $this->show_dependency_notice( $missing );
            });
            return false;
        }

        return true;
    }

    /**
     * Exibe aviso sobre dependências faltantes
     */
    private function show_dependency_notice( $missing ) {
        $message = sprintf(
            __( 'O plugin %s requer os seguintes plugins para funcionar: %s', 'joinotify-booking-integration' ),
            '<strong>Joinotify Booking Integration</strong>',
            '<strong>' . implode( ', ', $missing ) . '</strong>'
        );

        echo '<div class="notice notice-error"><p>' . $message . '</p></div>';
    }

    /**
     * Carrega arquivos necessários
     */
    private function load_includes() {
        require_once JBI_PLUGIN_DIR . 'includes/class-jbi-placeholders.php';
        require_once JBI_PLUGIN_DIR . 'includes/class-jbi-notifications.php';
        require_once JBI_PLUGIN_DIR . 'includes/class-jbi-booking-data.php';
        require_once JBI_PLUGIN_DIR . 'includes/class-jbi-addon-data.php';
        require_once JBI_PLUGIN_DIR . 'includes/class-jbi-settings.php';
        
        if ( is_admin() ) {
            require_once JBI_PLUGIN_DIR . 'admin/class-jbi-admin.php';
        }
    }

    /**
     * Inicializa componentes do plugin
     */
    private function init_components() {
        // Inicializa classes principais
        JBI_Placeholders::get_instance();
        JBI_Notifications::get_instance();
        JBI_Settings::get_instance();
        
        if ( is_admin() ) {
            JBI_Admin::get_instance();
        }
    }

    /**
     * Ativação do plugin
     */
    public function activate() {
        // Define opções padrão
        $default_options = array(
            'sender_number' => '',
            'notification_statuses' => array( 'processing', 'completed', 'confirmed' ),
            'notification_delay' => 30,
            'enable_email_integration' => true,
            'enable_debug_logs' => false
        );

        add_option( 'jbi_settings', $default_options );

        // Cria tabela de logs se necessário
        $this->create_logs_table();

        // Limpa cache
        flush_rewrite_rules();
    }

    /**
     * Desativação do plugin
     */
    public function deactivate() {
        // Limpa cache
        flush_rewrite_rules();
    }

    /**
     * Cria tabela para logs do plugin
     */
    private function create_logs_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'jbi_logs';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            message_type varchar(50) NOT NULL,
            status varchar(20) NOT NULL,
            message_content text,
            response_data text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Obtém configurações do plugin
     */
    public static function get_settings() {
        return get_option( 'jbi_settings', array() );
    }

    /**
     * Atualiza configurações do plugin
     */
    public static function update_settings( $settings ) {
        return update_option( 'jbi_settings', $settings );
    }

    /**
     * Registra log do plugin
     */
    public static function log( $order_id, $message_type, $status, $message_content = '', $response_data = '' ) {
        $settings = self::get_settings();
        
        if ( empty( $settings['enable_debug_logs'] ) ) {
            return;
        }

        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'jbi_logs',
            array(
                'order_id' => $order_id,
                'message_type' => $message_type,
                'status' => $status,
                'message_content' => $message_content,
                'response_data' => $response_data
            ),
            array( '%d', '%s', '%s', '%s', '%s' )
        );
    }
}

/**
 * Função para acessar a instância do plugin
 */
function JBI() {
    return Joinotify_Booking_Integration::get_instance();
}

// Inicializa o plugin
JBI();



/*
 * Changelog
 * 1.1.0 - 2025-07-21
 *   - Alterado o formato de exibição do ID da reserva para 'Reserva nº: #NÚMERO'.
 * 1.0.9 - 2025-07-21
 *   - Adicionado o ID da reserva como primeiro item nos detalhes da reserva.
 * 1.0.8 - 2025-07-21
 *   - Corrigido erro fatal causado por declaração duplicada de função.
 *   - Adicionado placeholder `{{ booking_id }}` para exibir o número da reserva.
 *   - Atualizado o template de mensagem padrão para usar `{{ booking_id }}`.
 */