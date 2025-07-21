<?php
/**
 * Classe para manipulação de dados de reserva
 *
 * @package JoinotifyBookingIntegration
 */

// Evita acesso direto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe JBI_Booking_Data
 */
class JBI_Booking_Data {

    /**
     * Obtém detalhes completos de reservas de forma dinâmica
     */
    public static function get_booking_details( $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return '';
        }
        
        $booking_details = array();
        
        foreach ( $order->get_items() as $item_id => $item ) {
            if ( ! self::has_booking_support() ) {
                Joinotify_Booking_Integration::log( 
                    $order->get_id(), 
                    'booking_data_error', 
                    'error', 
                    'WooCommerce Bookings não está ativo ou classes não encontradas.' 
                );
                continue;
            }
            
            try {
                $booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
                
                if ( ! empty( $booking_ids ) ) {
                    foreach ( $booking_ids as $booking_id ) {
                        try {
                            $booking = new WC_Booking( $booking_id );
                            $detail = self::format_booking_detail( $booking, $item );
                            
                            if ( $detail ) {
                                $booking_details[] = $detail;
                            }
                            
                        } catch ( Exception $e ) {
                            Joinotify_Booking_Integration::log( 
                                $order->get_id(), 
                                'booking_data_error', 
                                'error', 
                                'Erro ao instanciar WC_Booking ou formatar detalhes: ' . $e->getMessage() 
                            );
                        }
                    }
                }
            } catch ( Exception $e ) {
                Joinotify_Booking_Integration::log( 
                    $order->get_id(), 
                    'booking_data_error', 
                    'error', 
                    'Erro ao obter booking IDs do item: ' . $e->getMessage() 
                );
            }
        }
        
        return implode( "\n\n", $booking_details );
    }

    /**
     * Obtém apenas as datas das reservas
     */
    public static function get_booking_dates( $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return '';
        }
        
        $dates = array();
        
        foreach ( $order->get_items() as $item_id => $item ) {
            if ( ! self::has_booking_support() ) {
                Joinotify_Booking_Integration::log( 
                    $order->get_id(), 
                    'booking_dates_error', 
                    'error', 
                    'WooCommerce Bookings não está ativo ou classes não encontradas.' 
                );
                continue;
            }
            
            try {
                $booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
                
                if ( ! empty( $booking_ids ) ) {
                    foreach ( $booking_ids as $booking_id ) {
                        try {
                            $booking = new WC_Booking( $booking_id );
                            $date_string = self::format_booking_dates( $booking );
                            
                            if ( $date_string ) {
                                $dates[] = $date_string;
                            }
                            
                        } catch ( Exception $e ) {
                            Joinotify_Booking_Integration::log( 
                                $order->get_id(), 
                                'booking_dates_error', 
                                'error', 
                                'Erro ao instanciar WC_Booking ou formatar datas: ' . $e->getMessage() 
                            );
                        }
                    }
                }
            } catch ( Exception $e ) {
                Joinotify_Booking_Integration::log( 
                    $order->get_id(), 
                    'booking_dates_error', 
                    'error', 
                    'Erro ao obter booking IDs do item para datas: ' . $e->getMessage() 
                );
            }
        }
        
        return implode( "\n", $dates );
    }

    /**
     * Obtém informações de pessoas das reservas
     */
    public static function get_booking_persons( $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return '';
        }
        
        $persons_info = array();
        
        foreach ( $order->get_items() as $item_id => $item ) {
            if ( ! self::has_booking_support() ) {
                Joinotify_Booking_Integration::log( 
                    $order->get_id(), 
                    'booking_persons_error', 
                    'error', 
                    'WooCommerce Bookings não está ativo ou classes não encontradas.' 
                );
                continue;
            }
            
            try {
                $booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
                
                if ( ! empty( $booking_ids ) ) {
                    foreach ( $booking_ids as $booking_id ) {
                        try {
                            $booking = new WC_Booking( $booking_id );
                            $person_info = self::format_person_info( $booking );
                            
                            if ( $person_info ) {
                                $persons_info[] = $person_info;
                            }
                            
                        } catch ( Exception $e ) {
                            Joinotify_Booking_Integration::log( 
                                $order->get_id(), 
                                'booking_persons_error', 
                                'error', 
                                'Erro ao instanciar WC_Booking ou formatar pessoas: ' . $e->getMessage() 
                            );
                        }
                    }
                }
            } catch ( Exception $e ) {
                Joinotify_Booking_Integration::log( 
                    $order->get_id(), 
                    'booking_persons_error', 
                    'error', 
                    'Erro ao obter booking IDs do item para pessoas: ' . $e->getMessage() 
                );
            }
        }
        
        return implode( "\n", $persons_info );
    }

    /**
     * Obtém informações de recursos das reservas
     */
    public static function get_booking_resources( $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return '';
        }
        
        $resources = array();
        
        foreach ( $order->get_items() as $item_id => $item ) {
            if ( ! self::has_booking_support() ) {
                Joinotify_Booking_Integration::log( 
                    $order->get_id(), 
                    'booking_resources_error', 
                    'error', 
                    'WooCommerce Bookings não está ativo ou classes não encontradas.' 
                );
                continue;
            }
            
            try {
                $booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
                
                if ( ! empty( $booking_ids ) ) {
                    foreach ( $booking_ids as $booking_id ) {
                        try {
                            $booking = new WC_Booking( $booking_id );
                            $resource_info = self::format_resource_info( $booking );
                            
                            if ( $resource_info ) {
                                $resources[] = $resource_info;
                            }
                            
                        } catch ( Exception $e ) {
                            Joinotify_Booking_Integration::log( 
                                $order->get_id(), 
                                'booking_resources_error', 
                                'error', 
                                'Erro ao instanciar WC_Booking ou formatar recursos: ' . $e->getMessage() 
                            );
                        }
                    }
                }
            } catch ( Exception $e ) {
                Joinotify_Booking_Integration::log( 
                    $order->get_id(), 
                    'booking_resources_error', 
                    'error', 
                    'Erro ao obter booking IDs do item para recursos: ' . $e->getMessage() 
                );
            }
        }
        
        return implode( "\n", $resources );
    }

    /**
     * Verifica se um pedido tem reservas
     */
    public static function order_has_bookings( $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            Joinotify_Booking_Integration::log( 
                0, 
                'booking_check', 
                'debug', 
                'order_has_bookings: Pedido inválido ou nulo.' 
            );
            return false;
        }
        
        if ( ! self::has_booking_support() ) {
            Joinotify_Booking_Integration::log( 
                $order->get_id(), 
                'booking_check', 
                'debug', 
                'order_has_bookings: WooCommerce Bookings não está ativo.' 
            );
            return false;
        }
        
        foreach ( $order->get_items() as $item_id => $item ) {
            try {
                $booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
                Joinotify_Booking_Integration::log( 
                    $order->get_id(), 
                    'booking_check', 
                    'debug', 
                    'order_has_bookings: Booking IDs para item ' . $item_id . ': ' . print_r( $booking_ids, true ) 
                );
                if ( ! empty( $booking_ids ) ) {
                    return true;
                }
            } catch ( Exception $e ) {
                Joinotify_Booking_Integration::log( 
                    $order->get_id(), 
                    'booking_check', 
                    'error', 
                    'order_has_bookings: Erro ao obter booking IDs para item ' . $item_id . ': ' . $e->getMessage() 
                );
            }
        }
        
        Joinotify_Booking_Integration::log( 
            $order->get_id(), 
            'booking_check', 
            'debug', 
            'order_has_bookings: Nenhuma reserva encontrada para o pedido.' 
        );
        return false;
    }

    /**
     * Verifica se o WooCommerce Booking está disponível
     */
    private static function has_booking_support() {
        return class_exists( 'WC_Booking_Data_Store' ) && class_exists( 'WC_Booking' );
    }

    /**
     * Verifica se HPOS está habilitado
     */
    private static function is_hpos_enabled() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
            return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }
        return false;
    }

    /**
     * Formata detalhes completos de uma reserva
     */
    private static function format_booking_detail( $booking, $item ) {
        $product_name = $item->get_name();
        
        // Informações de data/hora
        $date_info = self::format_booking_dates( $booking );
        
        // Informações de pessoas
        $person_info = self::format_person_info( $booking );
        
        // Informações de recursos
        $resource_info = self::format_resource_info( $booking );
        
        // Monta o bloco de detalhes
        $detail_parts = array();
        $detail_parts[] = "📅 Reserva: " . $product_name;
        
        if ( $resource_info ) {
            $detail_parts[] = $resource_info;
        }
        
        if ( $date_info ) {
            $detail_parts[] = $date_info;
        }
        
        if ( $person_info ) {
            $detail_parts[] = $person_info;
        }
        
        return implode( "\n", $detail_parts );
    }

    /**
     * Formata informações de data/hora de uma reserva
     */
    private static function format_booking_dates( $booking ) {
        $start_date = $booking->get_start_date( 'd/m/Y' );
        $start_time = $booking->get_start_date( '', 'H:i' );
        $end_date = $booking->get_end_date( 'd/m/Y' );
        $end_time = $booking->get_end_date( '', 'H:i' );
        
        $date_string = "🗓️ Data: " . $start_date;
        
        if ( $start_time && $end_time && $start_time !== $end_time ) {
            $date_string .= "\n⏰ Horário: " . $start_time . " - " . $end_time;
        } elseif ( $start_date !== $end_date ) {
            $date_string .= " até " . $end_date;
        }
        
        return $date_string;
    }

    /**
     * Formata informações de pessoas de uma reserva
     */
    private static function format_person_info( $booking ) {
        $persons = $booking->get_persons();
        $person_details = array();
        
        if ( ! empty( $persons ) && is_array( $persons ) ) {
            foreach ( $persons as $person_id => $count ) {
                if ( $count > 0 ) {
                    $person_type = get_the_title( $person_id );
                    if ( $person_type ) {
                        $person_details[] = $count . ' ' . strtolower( $person_type );
                    }
                }
            }
        }
        
        if ( ! empty( $person_details ) ) {
            return "👥 Pessoas: " . implode( ', ', $person_details );
        }
        
        return '';
    }

    /**
     * Formata informações de recursos de uma reserva
     */
    private static function format_resource_info( $booking ) {
        if ( $booking->get_resource_id() ) {
            $resource = $booking->get_resource();
            if ( $resource && method_exists( $resource, 'get_name' ) ) {
                return "🏠 Local: " . $resource->get_name();
            }
        }
        
        return '';
    }

    /**
     * Obtém o ID da primeira reserva encontrada no pedido.
     */
    public static function get_single_booking_id( $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return '';
        }

        if ( ! self::has_booking_support() ) {
            return '';
        }

        foreach ( $order->get_items() as $item_id => $item ) {
            try {
                $booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
                if ( ! empty( $booking_ids ) ) {
                    // Retorna o primeiro ID de reserva encontrado
                    return $booking_ids[0];
                }
            } catch ( Exception $e ) {
                Joinotify_Booking_Integration::log(
                    $order->get_id(),
                    'booking_data_error',
                    'error',
                    'Erro ao obter booking IDs para single booking ID: ' . $e->getMessage()
                );
            }
        }
        return '';
    }
}

