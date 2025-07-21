<?php
/**
 * Classe para gerenciar configurações do plugin
 *
 * @package JoinotifyBookingIntegration
 */

// Evita acesso direto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe JBI_Settings
 */
class JBI_Settings {

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
        // Configurações são gerenciadas pela classe principal
    }

    /**
     * Obtém configurações padrão
     */
    public static function get_default_settings() {
        return array(
            'sender_number' => '',
            'notification_statuses' => array( 'processing', 'completed', 'confirmed' ),
            'notification_delay' => 30,
            'enable_email_integration' => false,
            'message_template' => self::get_default_message_template()
        );
    }

    /**
     * Obtém o template padrão de mensagem
     */
    public static function get_default_message_template() {
        return "🎉 Olá {{ customer_name }}!\n\n✅ Sua reserva foi confirmada com sucesso!\n📋 **Detalhes da Reserva #{{ booking_id }}**\n\n{{ booking_details }}\n\n🎁 **Extras selecionados:**\n{{ product_addons }}\n\n💰 **Valor total:** {{ order_total }}\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado por escolher {{ site_name }}! 🙏";
    }

    /**
     * Obtém configurações atuais
     */
    public static function get_settings() {
        $defaults = self::get_default_settings();
        $settings = get_option( 'jbi_settings', array() );
        
        return wp_parse_args( $settings, $defaults );
    }

    /**
     * Atualiza configurações
     */
    public static function update_settings( $new_settings ) {
        $current_settings = self::get_settings();
        $updated_settings = wp_parse_args( $new_settings, $current_settings );
        
        // Sanitiza configurações
        $updated_settings = self::sanitize_settings( $updated_settings );
        
        return update_option( 'jbi_settings', $updated_settings );
    }

    /**
     * Sanitiza configurações
     */
    public static function sanitize_settings( $settings ) {
        $sanitized = array();
        
        // Número do remetente
        $sanitized['sender_number'] = sanitize_text_field( $settings['sender_number'] );
        
        // Status de notificação
        if ( isset( $settings['notification_statuses'] ) && is_array( $settings['notification_statuses'] ) ) {
            $sanitized['notification_statuses'] = array_map( 'sanitize_text_field', $settings['notification_statuses'] );
        } else {
            $sanitized['notification_statuses'] = array( 'processing', 'completed', 'confirmed' );
        }
        
        // Atraso de notificação
        $sanitized['notification_delay'] = absint( $settings['notification_delay'] );
        
        // Integração com email
        $sanitized['enable_email_integration'] = ! empty( $settings['enable_email_integration'] );
        
        // Template de mensagem
        $sanitized['message_template'] = isset( $settings['message_template'] ) ? 
            wp_kses_post( $settings['message_template'] ) : 
            self::get_default_message_template();
        
        // Logs de debug
        $sanitized['enable_debug_logs'] = ! empty( $settings['enable_debug_logs'] );
        
        // Template de mensagem personalizada
        $sanitized['custom_message_template'] = wp_kses_post( $settings['custom_message_template'] );
        
        // Ícones personalizados
        $sanitized['enable_custom_icons'] = ! empty( $settings['enable_custom_icons'] );
        
        // Categorias de add-ons
        if ( isset( $settings['addon_categories'] ) && is_array( $settings['addon_categories'] ) ) {
            $sanitized['addon_categories'] = $settings['addon_categories'];
        } else {
            $sanitized['addon_categories'] = array();
        }
        
        return $sanitized;
    }

    /**
     * Obtém status disponíveis do WooCommerce
     */
    public static function get_available_statuses() {
        $statuses = wc_get_order_statuses();
        
        // Remove prefixo 'wc-' dos status
        $clean_statuses = array();
        foreach ( $statuses as $key => $label ) {
            $clean_key = str_replace( 'wc-', '', $key );
            $clean_statuses[ $clean_key ] = $label;
        }
        
        return $clean_statuses;
    }

    /**
     * Valida número de telefone
     */
    public static function validate_phone_number( $phone ) {
        // Remove caracteres não numéricos
        $clean_phone = preg_replace( '/[^0-9]/', '', $phone );
        
        // Verifica se tem pelo menos 10 dígitos
        if ( strlen( $clean_phone ) < 10 ) {
            return false;
        }
        
        // Verifica se começa com código do país
        if ( strlen( $clean_phone ) >= 12 && substr( $clean_phone, 0, 2 ) === '55' ) {
            return true;
        }
        
        // Se não tem código do país, adiciona o do Brasil
        if ( strlen( $clean_phone ) === 11 || strlen( $clean_phone ) === 10 ) {
            return '55' . $clean_phone;
        }
        
        return false;
    }

    /**
     * Obtém template de mensagem
     */
    public static function get_message_template() {
        $settings = self::get_settings();
        
        if ( ! empty( $settings['custom_message_template'] ) ) {
            return $settings['custom_message_template'];
        }
        
        return self::get_default_message_template();
    }



    /**
     * Exporta configurações
     */
    public static function export_settings() {
        $settings = self::get_settings();
        
        return array(
            'version' => JBI_VERSION,
            'export_date' => current_time( 'mysql' ),
            'settings' => $settings
        );
    }

    /**
     * Importa configurações
     */
    public static function import_settings( $import_data ) {
        if ( ! isset( $import_data['settings'] ) || ! is_array( $import_data['settings'] ) ) {
            return false;
        }
        
        $settings = $import_data['settings'];
        
        // Valida versão se disponível
        if ( isset( $import_data['version'] ) ) {
            // Aqui você pode adicionar lógica de compatibilidade entre versões
        }
        
        return self::update_settings( $settings );
    }

    /**
     * Reseta configurações para padrão
     */
    public static function reset_settings() {
        $default_settings = self::get_default_settings();
        return update_option( 'jbi_settings', $default_settings );
    }

    /**
     * Obtém estatísticas de uso
     */
    public static function get_usage_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Total de notificações enviadas
        $stats['total_notifications'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}jbi_logs WHERE message_type = 'whatsapp_notification' AND status = 'sent'"
        );
        
        // Notificações dos últimos 30 dias
        $stats['notifications_last_30_days'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}jbi_logs 
                WHERE message_type = 'whatsapp_notification' 
                AND status = 'sent' 
                AND created_at >= %s",
                date( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
            )
        );
        
        // Erros dos últimos 7 dias
        $stats['errors_last_7_days'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}jbi_logs 
                WHERE status = 'error' 
                AND created_at >= %s",
                date( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
            )
        );
        
        // Status do HPOS
        $stats['hpos_enabled'] = self::is_hpos_enabled();
        
        return $stats;
    }

    /**
     * Verifica se HPOS está habilitado
     */
    public static function is_hpos_enabled() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
            return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }
        return false;
    }

    /**
     * Obtém informações sobre compatibilidade HPOS
     */
    public static function get_hpos_info() {
        return array(
            'enabled' => self::is_hpos_enabled(),
            'compatible' => true, // Nosso plugin é compatível
            'class_exists' => class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ),
            'features_util_exists' => class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' )
        );
    }
}

