<?php
/**
 * Plugin Name: Plugin de teste de integração com o asaas
 * Plugin URI: https://github.com/D7alth/Asaas-recurrence-report-2.0
 * Description: This plugin makes a GET request to an API, passes the headers correctly and displays the data in a table with only 2 columns "Numero do boleto" and "Status do pagamento" using shortcode.
 * Version: 1.0
 * Author: alberth henrique
 * Author URI: https://alberthhls.com.br
 * License: GPL2
 */

function meu_formulario_de_api() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cpf = "61636168396";
        
        //$_POST['cpf'];
        
        $url_api_1 = "https://www.asaas.com/api/v3/customers?cpfCnpj=" . $cpf;
        $api_key = "Sua apikey";
        
        $headers = array(
            'Content-Type' => 'application/json',
            'access_token' => $api_key,
        );
        
        $resposta_api_1 = wp_remote_get($url_api_1,  array( 'headers' => $headers ) );
        $resposta_api_1 = json_decode( $resposta_api_1['body'], true );
        
        if( is_wp_error($resposta_api_1) ){
            return  $response->get_error_message();
        }else {
            $data_customer_id = $resposta_api_1['data'][0]['id'];
            
            $url_api_2 = "https://www.asaas.com/api/v3/subscriptions?customer=" . $data_customer_id;
            $resposta_api_2 = wp_remote_get($url_api_2,  array( 'headers' => $headers ) );
            $resposta_api_2 =  json_decode( $resposta_api_2['body'], true );
            if( is_wp_error($resposta_api_2) ){
            return  $response->get_error_message();
            }else{
            global $data_customer_total_count;
            global $data_customer_status;
            
            $data_customer_total_count =  $resposta_api_2['totalCount'];
            $data_customer_status = $resposta_api_2['data'][0]['status'];
            }
        }
        $tabela = "<table>";
        $tabela .= "<tr><td>" . $data_customer_total_count . "</td><td>" . $data_customer_status . "</td></tr>";
        $tabela .= "</table>";
    }
}

function show_response_api_request(){
    $form = '<form method="post">';
    $form .= '<label>CPF:</label>';
    $form .= '<input type="text" name="cpf">';
    $form .= '<input type="submit" value="Enviar">';
    $form .= '</form>';
    $form .= $tabela;
    return $form;
}

function registration_request_shortcode(){
  ob_start();
  show_response_api_request();
  return ob_get_clean();
}

add_shortcode( 'request_api', 'registration_request_shortcode' );
add_action( 'init', 'register_shortcode' );
?>
