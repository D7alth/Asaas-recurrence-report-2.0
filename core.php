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

function api_get_request_and_table_display_shortcode() {
if ( isset( $_POST['cpf'] ) ) {
    // Prepare the headers for the API request
    $api_key = 'Sua api key';
    $cpf = $_POST['cpf'];
    $cpf = preg_replace("/[^0-9]/", "", $cpf);

    $headers = array(
        'Content-Type' => 'application/json',
        'access_token' => $api_key,
    );

    $response1 = wp_remote_get( 'https://www.asaas.com/api/v3/customers?cpfCnpj=' . sanitize_text_field( $cpf ), array( 'headers' => $headers ) );


    if ( wp_remote_retrieve_response_code( $response1 ) === 200 && is_wp_error( $response1 ) != true ){
        $body1 = json_decode($response1['body'], true);
        $id = $body1['data'][0]['id'];
        
        $response2 = wp_remote_get( 'https://www.asaas.com/api/v3/payments?customer=' . $id, array(
                'headers' => array(
                   'Content-Type' => 'application/json',
                    'access_token' => $api_key,
                )
            ));
        if (is_wp_error($response2)) {
            return 'Erro ao fazer a requisição: ' . $response2->get_error_message();
        }else {
            
            $status = array();
            $formated_date = array();
            
            $body2 = json_decode($response2['body'], true);
            $j = 0; 
            
            
            for($i = 0; $i < ($body2['totalCount']); $i++){
                if($body2['data'][($j + $i)]['status'] == "ACTIVE"){
                   array_push($status, "Ativo"); 
                    
                } elseif($body2['data'][($j + $i)]['status'] == "PENDING"){
                    array_push($status, "Pendente"); 
                }
            }
            for($i = 0; $i < ($body2['totalCount']); $i++){
                array_push($formated_date, date("d/m/Y", strtotime($body2['data'][($i)]['dueDate'])));
            }
            
            $table = '<table class="styled-table">';
            $table .= '<tr class="theader1">';
            $table .= '<th>Vencimento do boleto</th>';
            $table .= '<th>Status do pagamento</th>';
            $table .= '<th>Link de pagamento</th>';
            $table .= '</tr>';
            $table .= '<tbody>';
            // Add table rows for each item in the data array
            for ( $i = 1; $i <= $body2['totalCount']; $i++ ) {
                $table .= '<tr class="theader2">';
                $table .= '<td>' . $formated_date[($i - 1)] . '</td>';
                $table .= '<td>' . $status[($i - 1)] . '</td>';
                $table .= '<td><button class="btn btn-outline-secondary" style="width: 100% !important; height: 100% !important;padding: 8px; border-radius: 10px; background: #96cdf3; color: white;"><a  style="color:#ffffff;" href="' . $body2['data'][($i - 1)]['invoiceUrl'] . '">Acessar boleto</a></button></td>';
                $table .= '</tr>';
            }
            $table .= '</tbody>';
            $table .= '</table>';
    
            
            return $table;
        }
        
    } else {
        return 'Error: API request failed.';
    }
} else {
        
        /*<div class="input-group mb-3">
  <input type="text" class="form-control" placeholder="Recipient's username" aria-label="Recipient's username" aria-describedby="button-addon2">
  <button class="btn btn-outline-secondary" type="button" id="button-addon2">Button</button>
</div>*/
        
        
        $form = '<form name="form1" id="form1" method="post">';
        $form .= '<label for="cpf">CPF</label>';
        $form .= '<div class="input-group mb-3" style="display:flex;">';
        $form .= '<input type="text" id="cpf" name="cpf" class="form-control" placeholder="000.000.000-00" aria-describedby="button-addon2" data-mask="000.000.000-00" pattern="(\d{3}\.?\d{3}\.?\d{3}-?\d{2})|(\d{2}\.?\d{3}\.?\d{3}/?\d{4}-?\d{2})" required>';
        $form .= '<button class="btn btn-outline-secondary" type="submit" form="form1" id="button-addon2" value="Submit">Enviar</button>';
        $form .= '</div>';
        $form .= '</form>';

        return $form;
    }
}
add_shortcode( 'asaas_api_table', 'api_get_request_and_table_display_shortcode' );
?>
