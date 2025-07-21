<?php
/**
 * Classe para gerenciar placeholders do Joinotify
 *
 * @package JoinotifyBookingIntegration
 */

// Evita acesso direto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe JBI_Placeholders
 */
class JBI_Placeholders {

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
        add_filter( 'Joinotify/Builder/Placeholders_List', array( $this, 'add_booking_placeholders' ), 10, 2 );
    }

    /**
     * Adiciona placeholders dinâmicos para WooCommerce Booking e Product Add-ons
     */
    public function add_booking_placeholders( $placeholders, $payload ) {
        $order = isset( $payload['order_id'] ) ? wc_get_order( $payload['order_id'] ) : null;
        
        if ( ! class_exists( '\MeuMouse\Joinotify\Builder\Core' ) ) {
            return $placeholders;
        }
        
        $trigger_names = \MeuMouse\Joinotify\Builder\Core::get_trigger_names('woocommerce');
        
        // Placeholders para WooCommerce Booking
        $placeholders['woocommerce']['{{ booking_details }}'] = array(
            'triggers' => $trigger_names,
            'description' => __( 'Detalhes completos de todas as reservas (funciona com qualquer produto)', 'joinotify-booking-integration' ),
            'replacement' => array(
                'production' => isset( $order ) ? JBI_Booking_Data::get_booking_details( $order ) : '',
                'sandbox' => $this->get_sandbox_booking_details(),
            ),
        );
        
        $placeholders['woocommerce']['{{ booking_dates }}'] = array(
            'triggers' => $trigger_names,
            'description' => __( 'Datas e horários de todas as reservas', 'joinotify-booking-integration' ),
            'replacement' => array(
                'production' => isset( $order ) ? JBI_Booking_Data::get_booking_dates( $order ) : '',
                'sandbox' => $this->get_sandbox_booking_dates(),
            ),
        );
        
        $placeholders['woocommerce']['{{ booking_persons }}'] = array(
            'triggers' => $trigger_names,
            'description' => __( 'Informações de pessoas em todas as reservas', 'joinotify-booking-integration' ),
            'replacement' => array(
                'production' => isset( $order ) ? JBI_Booking_Data::get_booking_persons( $order ) : '',
                'sandbox' => $this->get_sandbox_booking_persons(),
            ),
        );
        
        $placeholders['woocommerce']['{{ booking_resources }}'] = array(
            'triggers' => $trigger_names,
            'description' => __( 'Recursos/locais selecionados nas reservas', 'joinotify-booking-integration' ),
            'replacement' => array(
                'production' => isset( $order ) ? JBI_Booking_Data::get_booking_resources( $order ) : '',
                'sandbox' => $this->get_sandbox_booking_resources(),
            ),
        );
        
        $placeholders['woocommerce']['{{ booking_id }}'] = array(
            'triggers' => $trigger_names,
            'description' => __( 'ID da reserva (se houver apenas uma reserva no pedido)', 'joinotify-booking-integration' ),
            'replacement' => array(
                'production' => isset( $order ) ? JBI_Booking_Data::get_single_booking_id( $order ) : '',
                'sandbox' => '12345',
            ),
        );
        
        // Placeholders para Product Add-ons
        $placeholders['woocommerce']['{{ product_addons }}'] = array(
            'triggers' => $trigger_names,
            'description' => __( 'Lista de todos os add-ons selecionados (detecta automaticamente)', 'joinotify-booking-integration' ),
            'replacement' => array(
                'production' => isset( $order ) ? JBI_Addon_Data::get_product_addons( $order ) : '',
                'sandbox' => $this->get_sandbox_product_addons(),
            ),
        );
        
        $placeholders['woocommerce']['{{ addons_summary }}'] = array(
            'triggers' => $trigger_names,
            'description' => __( 'Resumo dos add-ons com valores (calcula automaticamente)', 'joinotify-booking-integration' ),
            'replacement' => array(
                'production' => isset( $order ) ? JBI_Addon_Data::get_addons_summary( $order ) : '',
                'sandbox' => $this->get_sandbox_addons_summary(),
            ),
        );
        
        return $placeholders;
    }

    /**
     * Retorna exemplo de detalhes de reserva para sandbox
     */
    private function get_sandbox_booking_details() {
        return "📅 Reserva: Produto de Exemplo\n🏠 Local: Local Principal\n🗓️ Data: 26/07/2025\n⏰ Horário: 11:15 - 11:30\n👥 Pessoas: 18 adultos, 41 crianças";
    }

    /**
     * Retorna exemplo de datas de reserva para sandbox
     */
    private function get_sandbox_booking_dates() {
        return "🗓️ Data: 26/07/2025\n⏰ Horário: 11:15 - 11:30";
    }

    /**
     * Retorna exemplo de pessoas para sandbox
     */
    private function get_sandbox_booking_persons() {
        return "👥 Pessoas: 18 adultos, 41 crianças";
    }

    /**
     * Retorna exemplo de recursos para sandbox
     */
    private function get_sandbox_booking_resources() {
        return "🏠 Local: Local Principal";
    }

    /**
     * Retorna exemplo de add-ons para sandbox
     */
    private function get_sandbox_product_addons() {
        return "✅ Add-on Exemplo 1: Sim\n🍽️ Add-on Exemplo 2: Opção Selecionada\n📝 Observações: Texto personalizado";
    }

    /**
     * Retorna exemplo de resumo de add-ons para sandbox
     */
    private function get_sandbox_addons_summary() {
        return "💰 Add-ons selecionados:\n• Add-on Exemplo 1: R$ 10,00\n• Add-on Exemplo 2: R$ 15,00\n💵 Total add-ons: R$ 25,00";
    }
}



