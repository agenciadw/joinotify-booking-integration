<?php
/**
 * Classe para manipulação de dados de add-ons
 *
 * @package JoinotifyBookingIntegration
 */

// Evita acesso direto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe JBI_Addon_Data
 */
class JBI_Addon_Data {

    /**
     * Obtém todos os add-ons de um pedido de forma dinâmica
     */
    public static function get_product_addons( $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return '';
        }
        
        $addons_list = array();
        
        foreach ( $order->get_items() as $item_id => $item ) {
            $pao_ids = $item->get_meta( '_pao_ids' );
            
            if ( ! empty( $pao_ids ) && is_array( $pao_ids ) ) {
                foreach ( $pao_ids as $addon_data ) {
                    if ( isset( $addon_data['key'] ) && isset( $addon_data['value'] ) ) {
                        $icon = self::get_dynamic_addon_icon( $addon_data );
                        $formatted_value = self::format_addon_value( $addon_data );
                        
                        $addons_list[] = $icon . " " . $addon_data['key'] . ": " . $formatted_value;
                    }
                }
            }
        }
        
        return implode( "\n", $addons_list );
    }

    /**
     * Obtém resumo dos add-ons com valores
     */
    public static function get_addons_summary( $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return '';
        }
        
        $addons_summary = array();
        $total_addons = 0;
        $has_addons = false;
        
        foreach ( $order->get_items() as $item_id => $item ) {
            $pao_ids = $item->get_meta( '_pao_ids' );
            $pao_total = $item->get_meta( '_pao_total' );
            
            if ( ! empty( $pao_ids ) && is_array( $pao_ids ) ) {
                $has_addons = true;
                
                foreach ( $pao_ids as $addon_data ) {
                    if ( isset( $addon_data['key'] ) && isset( $addon_data['value'] ) ) {
                        $price = isset( $addon_data['raw_price'] ) ? floatval( $addon_data['raw_price'] ) : 0;
                        
                        if ( $price > 0 ) {
                            $formatted_price = wc_price( $price );
                            $addons_summary[] = "• " . $addon_data['key'] . ": " . $formatted_price;
                        } else {
                            $addons_summary[] = "• " . $addon_data['key'] . ": " . __( 'Incluído', 'joinotify-booking-integration' );
                        }
                    }
                }
            }
            
            if ( $pao_total ) {
                $total_addons += floatval( $pao_total );
            }
        }
        
        if ( ! $has_addons ) {
            return '';
        }
        
        $summary = array();
        $summary[] = "💰 " . __( 'Add-ons selecionados:', 'joinotify-booking-integration' );
        $summary = array_merge( $summary, $addons_summary );
        
        if ( $total_addons > 0 ) {
            $summary[] = "💵 " . __( 'Total add-ons:', 'joinotify-booking-integration' ) . " " . wc_price( $total_addons );
        }
        
        return implode( "\n", $summary );
    }

    /**
     * Detecta ícone apropriado para add-on de forma dinâmica
     */
    public static function get_dynamic_addon_icon( $addon_data ) {
        $addon_name = strtolower( $addon_data['key'] );
        $addon_value = strtolower( $addon_data['value'] );
        
        // Detecta por tipo de preço
        if ( isset( $addon_data['price_type'] ) ) {
            switch ( $addon_data['price_type'] ) {
                case 'custom_price':
                    return '💰';
                case 'percentage_based':
                    return '📊';
            }
        }
        
        // Palavras-chave para categorização
        $categories = self::get_addon_categories();
        
        foreach ( $categories as $category => $data ) {
            foreach ( $data['keywords'] as $keyword ) {
                if ( strpos( $addon_name, $keyword ) !== false ) {
                    return $data['icon'];
                }
            }
        }
        
        // Detecta por tipo de valor
        if ( is_numeric( $addon_value ) ) {
            return '🔢';
        }
        
        $positive_values = array( 'sim', 'yes', 'true', '1', 'incluído', 'included' );
        $negative_values = array( 'não', 'no', 'false', '0', 'excluído', 'excluded' );
        
        if ( in_array( $addon_value, $positive_values ) ) {
            return '✅';
        }
        
        if ( in_array( $addon_value, $negative_values ) ) {
            return '❌';
        }
        
        // Ícone padrão
        return '🔹';
    }

    /**
     * Formata o valor do add-on de forma apropriada
     */
    public static function format_addon_value( $addon_data ) {
        $value = $addon_data['value'];
        
        // Se é um timestamp (datepicker), formata como data
        if ( isset( $addon_data['timestamp'] ) && is_numeric( $addon_data['timestamp'] ) ) {
            return date_i18n( get_option( 'date_format' ), $addon_data['timestamp'] );
        }
        
        // Se é um preço customizado, formata como moeda
        if ( isset( $addon_data['price_type'] ) && $addon_data['price_type'] === 'custom_price' ) {
            $price = isset( $addon_data['raw_value'] ) ? $addon_data['raw_value'] : $value;
            if ( is_numeric( $price ) ) {
                return wc_price( $price );
            }
        }
        
        return $value;
    }

    /**
     * Retorna categorias de add-ons com palavras-chave e ícones
     */
    private static function get_addon_categories() {
        return apply_filters( 'jbi_addon_categories', array(
            'food' => array(
                'icon' => '🍽️',
                'keywords' => array( 
                    'comida', 'alimento', 'refeição', 'lanche', 'café', 'almoço', 
                    'jantar', 'bebida', 'drink', 'oficina', 'recreativ', 'culinária'
                )
            ),
            'equipment' => array(
                'icon' => '🎯',
                'keywords' => array( 
                    'colete', 'equipamento', 'material', 'kit', 'uniforme', 
                    'roupa', 'ferramenta', 'acessório'
                )
            ),
            'information' => array(
                'icon' => '📝',
                'keywords' => array( 
                    'informação', 'observ', 'comentário', 'nota', 'detalhe', 
                    'especial', 'observação', 'anotação'
                )
            ),
            'transport' => array(
                'icon' => '🚌',
                'keywords' => array( 
                    'transporte', 'ônibus', 'van', 'carro', 'transfer', 
                    'condução', 'veículo'
                )
            ),
            'insurance' => array(
                'icon' => '🛡️',
                'keywords' => array( 
                    'seguro', 'proteção', 'cobertura', 'garantia'
                )
            ),
            'time' => array(
                'icon' => '⏰',
                'keywords' => array( 
                    'horário', 'tempo', 'duração', 'período', 'hora'
                )
            ),
            'location' => array(
                'icon' => '📍',
                'keywords' => array( 
                    'local', 'lugar', 'endereço', 'localização', 'destino'
                )
            ),
            'person' => array(
                'icon' => '👤',
                'keywords' => array( 
                    'pessoa', 'participante', 'convidado', 'acompanhante'
                )
            ),
            'service' => array(
                'icon' => '🔧',
                'keywords' => array( 
                    'serviço', 'atendimento', 'suporte', 'assistência'
                )
            ),
            'entertainment' => array(
                'icon' => '🎪',
                'keywords' => array( 
                    'entretenimento', 'diversão', 'atividade', 'brincadeira', 
                    'jogo', 'animação'
                )
            )
        ));
    }

    /**
     * Verifica se um pedido tem add-ons
     */
    public static function order_has_addons( $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return false;
        }
        
        foreach ( $order->get_items() as $item_id => $item ) {
            $pao_ids = $item->get_meta( '_pao_ids' );
            if ( ! empty( $pao_ids ) && is_array( $pao_ids ) ) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtém estatísticas de add-ons de um pedido
     */
    public static function get_addon_stats( $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return array();
        }
        
        $stats = array(
            'total_addons' => 0,
            'total_value' => 0,
            'categories' => array()
        );
        
        foreach ( $order->get_items() as $item_id => $item ) {
            $pao_ids = $item->get_meta( '_pao_ids' );
            $pao_total = $item->get_meta( '_pao_total' );
            
            if ( ! empty( $pao_ids ) && is_array( $pao_ids ) ) {
                $stats['total_addons'] += count( $pao_ids );
                
                foreach ( $pao_ids as $addon_data ) {
                    $category = self::categorize_addon( $addon_data );
                    if ( ! isset( $stats['categories'][ $category ] ) ) {
                        $stats['categories'][ $category ] = 0;
                    }
                    $stats['categories'][ $category ]++;
                }
            }
            
            if ( $pao_total ) {
                $stats['total_value'] += floatval( $pao_total );
            }
        }
        
        return $stats;
    }

    /**
     * Categoriza um add-on baseado em suas características
     */
    private static function categorize_addon( $addon_data ) {
        $addon_name = strtolower( $addon_data['key'] );
        $categories = self::get_addon_categories();
        
        foreach ( $categories as $category => $data ) {
            foreach ( $data['keywords'] as $keyword ) {
                if ( strpos( $addon_name, $keyword ) !== false ) {
                    return $category;
                }
            }
        }
        
        return 'other';
    }
}

