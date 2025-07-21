<?php
/**
 * Classe para gerenciar notificações automáticas
 *
 * @package JoinotifyBookingIntegration
 */

// Evita acesso direto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe JBI_Notifications
 */
class JBI_Notifications {

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
        add_action( 'woocommerce_order_status_changed', array( $this, 'send_booking_notification' ), 10, 4 );
        add_action( 'woocommerce_email_order_details', array( $this, 'add_booking_details_to_email' ), 20, 4 );
    }

    /**
     * Envia notificação WhatsApp para reservas
     */
    public function send_booking_notification( $order_id, $old_status, $new_status, $order ) {
        // Obtém o pedido usando API compatível com HPOS
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            $order = wc_get_order( $order_id );
        }
        
        if ( ! $order ) {
            Joinotify_Booking_Integration::log( 
                $order_id, 
                'notification_error', 
                'error', 
                'Pedido não encontrado' 
            );
            return;
        }

        // Verifica se o pedido tem reservas
        if ( ! JBI_Booking_Data::order_has_bookings( $order ) ) {
            return;
        }

        $settings = Joinotify_Booking_Integration::get_settings();
        
        // Verifica se o status deve enviar notificação
        $notification_statuses = isset( $settings['notification_statuses'] ) ? 
            $settings['notification_statuses'] : 
            array( 'processing', 'completed', 'confirmed' );
        
        if ( ! in_array( $new_status, $notification_statuses ) ) {
            return;
        }

        $billing_phone = $order->get_billing_phone();
        if ( ! $billing_phone ) {
            Joinotify_Booking_Integration::log( 
                $order_id, 
                'notification_error', 
                'error', 
                'Telefone de cobrança não encontrado' 
            );
            return;
        }

        // Formatar o número de telefone
        $receiver = self::format_phone_number( $billing_phone );

        // Montar mensagem dinâmica
        $message = $this->build_notification_message( $order, $new_status );

        if ( empty( $message ) ) {
            Joinotify_Booking_Integration::log( 
                $order_id, 
                'notification_error', 
                'error', 
                'Mensagem vazia gerada' 
            );
            return;
        }

        // Número do remetente
        $sender = isset( $settings['sender_number'] ) ? $settings['sender_number'] : '';
        
        if ( empty( $sender ) ) {
            Joinotify_Booking_Integration::log( 
                $order_id, 
                'notification_error', 
                'error', 
                'Número do remetente não configurado' 
            );
            return;
        }

        // Atraso configurável
        $delay = isset( $settings['notification_delay'] ) ? intval( $settings['notification_delay'] ) : 30;

        // Enviar a mensagem
        if ( function_exists( 'joinotify_send_whatsapp_message_text' ) ) {
            $result = joinotify_send_whatsapp_message_text( $sender, $receiver, $message, $delay );
            
            Joinotify_Booking_Integration::log( 
                $order_id, 
                'whatsapp_notification', 
                'sent', 
                $message, 
                json_encode( array( 'result' => $result, 'receiver' => $receiver ) )
            );
        } else {
            Joinotify_Booking_Integration::log( 
                $order_id, 
                'notification_error', 
                'error', 
                'Função joinotify_send_whatsapp_message_text não encontrada' 
            );
        }
    }

    /**
     * Constrói mensagem de notificação usando template personalizável
     */
    private function build_notification_message( $order, $status ) {
        $settings = Joinotify_Booking_Integration::get_settings();
        $template = isset( $settings['message_template'] ) ? $settings['message_template'] : JBI_Settings::get_default_message_template();
        
        // Dados para substituição
        $customer_name = $order->get_billing_first_name();
        $order_number = $order->get_order_number();
        $booking_details = JBI_Booking_Data::get_booking_details( $order );
        $product_addons = JBI_Addon_Data::get_product_addons( $order );
        
        // Valor total sem HTML
        $total_formatted = strip_tags( $order->get_formatted_order_total() );
        $total_formatted = html_entity_decode( $total_formatted, ENT_QUOTES, 'UTF-8' );
        
        $site_name = get_bloginfo( 'name' );
        
        // Placeholders para substituição
        $placeholders = array(
            '{{ customer_name }}' => $customer_name,
            '{{ order_number }}' => $order_number,
            '{{ booking_details }}' => $booking_details ? $booking_details : __( 'Nenhum detalhe de reserva disponível', 'joinotify-booking-integration' ),
            '{{ product_addons }}' => $product_addons ? $product_addons : '',
            '{{ order_total }}' => $total_formatted,
            '{{ site_name }}' => $site_name
        );
        
        // Substitui placeholders no template
        $message = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $template );
        
        // Remove linhas vazias extras
        $message = preg_replace( '/\n\s*\n\s*\n/', "\n\n", $message );
        
        return trim( $message );
    }

    /**
     * Adiciona informações de reserva aos emails do WooCommerce
     */
    public function add_booking_details_to_email( $order, $sent_to_admin, $plain_text, $email ) {
        $settings = Joinotify_Booking_Integration::get_settings();
        
        // Verifica se a integração com email está habilitada
        if ( empty( $settings['enable_email_integration'] ) ) {
            return;
        }

        // Verifica se o pedido tem reservas
        if ( ! JBI_Booking_Data::order_has_bookings( $order ) ) {
            return;
        }

        $booking_details = JBI_Booking_Data::get_booking_details( $order );
        $addons_info = JBI_Addon_Data::get_product_addons( $order );

        if ( $plain_text ) {
            echo "\n" . "=== " . __( 'DETALHES DA RESERVA', 'joinotify-booking-integration' ) . " ===" . "\n";
            if ( $booking_details ) {
                echo $booking_details . "\n\n";
            }
            if ( $addons_info ) {
                echo __( 'EXTRAS SELECIONADOS:', 'joinotify-booking-integration' ) . "\n";
                echo $addons_info . "\n\n";
            }
        } else {
            echo '<h3>' . __( 'Detalhes da Reserva', 'joinotify-booking-integration' ) . '</h3>';
            if ( $booking_details ) {
                echo '<p>' . nl2br( esc_html( $booking_details ) ) . '</p>';
            }
            if ( $addons_info ) {
                echo '<h4>' . __( 'Extras Selecionados', 'joinotify-booking-integration' ) . '</h4>';
                echo '<p>' . nl2br( esc_html( $addons_info ) ) . '</p>';
            }
        }
    }

    /**
     * Formata número de telefone para WhatsApp
     */
    private static function format_phone_number( $phone ) {
        // Remove caracteres não numéricos
        $phone = preg_replace( '/[^0-9]/', '', $phone );
        
        // Se não começar com código do país, assume Brasil (+55)
        if ( strlen( $phone ) === 11 && substr( $phone, 0, 1 ) !== '5' ) {
            $phone = '55' . $phone;
        } elseif ( strlen( $phone ) === 10 && substr( $phone, 0, 1 ) !== '5' ) {
            $phone = '55' . $phone;
        }
        
        return $phone;
    }

    /**
     * Envia notificação de teste
     */
    public static function send_test_notification( $order_id ) {
        try {
            // Validação básica
            if ( empty( $order_id ) || ! is_numeric( $order_id ) ) {
                return array( 
                    'success' => false, 
                    'message' => __( 'ID do pedido inválido', 'joinotify-booking-integration' ) 
                );
            }

            $order = wc_get_order( $order_id );
            
            if ( ! $order ) {
                return array( 
                    'success' => false, 
                    'message' => sprintf( __( 'Pedido #%d não encontrado', 'joinotify-booking-integration' ), $order_id )
                );
            }

            // Verifica se tem reservas
            if ( ! JBI_Booking_Data::order_has_bookings( $order ) ) {
                return array( 
                    'success' => false, 
                    'message' => sprintf( __( 'Pedido #%d não possui reservas', 'joinotify-booking-integration' ), $order_id )
                );
            }

            // Verifica configurações
            $settings = Joinotify_Booking_Integration::get_settings();
            
            if ( empty( $settings['sender_number'] ) ) {
                return array( 
                    'success' => false, 
                    'message' => __( 'Número do remetente não configurado. Configure nas Configurações.', 'joinotify-booking-integration' ) 
                );
            }

            // Verifica se o Joinotify está ativo
            if ( ! function_exists( 'joinotify_send_whatsapp_message_text' ) ) {
                return array( 
                    'success' => false, 
                    'message' => __( 'Plugin Joinotify não está ativo ou função não disponível', 'joinotify-booking-integration' ) 
                );
            }

            // Verifica telefone do cliente
            $billing_phone = $order->get_billing_phone();
            if ( empty( $billing_phone ) ) {
                return array( 
                    'success' => false, 
                    'message' => sprintf( __( 'Pedido #%d não possui telefone de cobrança', 'joinotify-booking-integration' ), $order_id )
                );
            }

            // Log do teste
            Joinotify_Booking_Integration::log( 
                $order_id, 
                'test_notification', 
                'started', 
                'Iniciando teste de notificação'
            );

            // Envia a notificação
            $instance = self::get_instance();
            $instance->send_booking_notification( $order_id, 'pending', 'completed', $order );

            return array( 
                'success' => true, 
                'message' => sprintf( 
                    __( 'Notificação de teste enviada para o pedido #%d (telefone: %s)', 'joinotify-booking-integration' ), 
                    $order_id, 
                    $billing_phone 
                )
            );

        } catch ( Exception $e ) {
            // Log do erro
            Joinotify_Booking_Integration::log( 
                $order_id, 
                'test_notification_error', 
                'error', 
                'Erro no teste: ' . $e->getMessage()
            );

            return array( 
                'success' => false, 
                'message' => sprintf( __( 'Erro interno: %s', 'joinotify-booking-integration' ), $e->getMessage() )
            );
        }
    }
}

