<?php

namespace App\Http\Controllers\Pagamento;

use App\Http\Controllers\Controller;

require(dirname(__FILE__).'/../../Libs/erede/Classloader.php');

use erede\model\EnvironmentType;
use erede\model\TransactionKind;
use erede\model\TransactionRequest;
use erede\model\IataRequest;
use erede\model\ThreeDSecureRequest;
use erede\model\UrlRequest;
use erede\model\UrlKind;
use erede\model\AvsRequest;
use erede\model\AddressRequest;
use erede\model\RefundRequest;
use erede\model\ThreeDSecureOnFailure;

use Illuminate\Http\Request;

class PagamentoRedeController extends Controller
{

	// REALIZANDO A COMPRA
	public function pagamentoCartaoRede(Array $request)
	{
		$ac = '';
		if ($request['production']) {
			$ac = new \Acquirer($request['pv'], $request['token'], EnvironmentType::PRODUCTION);
		} else {
			$ac = new \Acquirer($request['pv'], $request['token'], EnvironmentType::HOMOLOG);
		}
		$transactionRequest = new TransactionRequest();
		$transactionRequest->setCapture($request['capture']);
		if ($request['type'] == 'CREDIT') {
			$transactionRequest->setKind(TransactionKind::CREDIT);
		} else {
			$transactionRequest->setKind(TransactionKind::DEBIT);
		}
		$transactionRequest->setReference($request['reference'] . ((string)mt_rand(0, 999999999)));
		$transactionRequest->setAmount($request['amount']);
		$transactionRequest->setInstallments($request['installments']);
		$transactionRequest->setCardHolderName($request['name']);
		$transactionRequest->setCardNumber($request['number']);
		$transactionRequest->setExpirationMonth($request['month']);
		$transactionRequest->setExpirationYear($request['year']);
		$transactionRequest->setSecurityCode($request['code']);
		$transactionRequest->setOrigin($request['origin']);
		$v = $ac->authorize($transactionRequest);
		$ac->capture($v->getTid(), $transactionRequest);

		return $v;
	}

	// CONSULTANDO A COMPRA
	public function consultaPagamentoCartaoRede(Array $request)
	{
		$query = '';
		if ($request['production']) {
			$query = new \Query($request['pv'], $request['token'], EnvironmentType::PRODUCTION);
		} else {
			$query = new \Query($request['pv'], $request['token'], EnvironmentType::HOMOLOG);
		}
		$transactionRequest = new TransactionRequest();
		
		return $query->getTransactionByTid($request['tid']);
	}

	// CANCELANDO A COMPRA
	public function cancelaPagamentoCartaoRede(Array $request) {
		$acquirer = '';
		if ($request['production']) {
			$acquirer = new \Acquirer($request['pv'], $request['token'], EnvironmentType::PRODUCTION);
		} else {
			$acquirer = new \Acquirer($request['pv'], $request['token'], EnvironmentType::HOMOLOG);
		}
		$cancel = new RefundRequest();
		$cancel->setAmount($request['amount']);

		return $acquirer->refund($request['tid'], $cancel);
	}

	public function returnCancelamento($code) {
		$return = [];

		switch($code) {
			case '351':
				$return = ['type' => 'error','message' => 'Proibido'];
				break;
			case '353':
				$return = ['type' => 'error','message' => 'Transação não encontrada'];
				break;
			case '354':
				$return = ['type' => 'error','message' => 'Transacção com prazo expirado para reembolso'];
				break;
			case '355':
				$return = ['type' => 'error','message' => 'Transação já cancelada.'];
				break;
			case '357':
				$return = ['type' => 'error','message' => 'Soma dos reembolsos de quantia maiores que o valor da transação'];
				break;
			case '358':
				$return = ['type' => 'error','message' => 'Soma dos reembolsos de quantia maiores que o valor processado disponível para reembolso'];
				break;
			case '359':
				$return = ['type' => 'success','message' => 'Reembolso bem sucedido'];
				break;
			case '360':
				$return = ['type' => 'error','message' => 'O pedido de reembolso foi bem sucedido'];
				break;
			case '362':
				$return = ['type' => 'error','message' => 'ReembolsoId não encontrado'];
				break;
			case '363':
				$return = ['type' => 'error','message' => 'caracteres de URL de retorno de chamada excederam 500'];
				break;
			case '365':
				$return = ['type' => 'error','message' => 'Reembolso parcial indisponível.'];
				break;
			case '368':
				$return = ['type' => 'error','message' => 'Sem sucesso. Por favor, tente novamente'];
				break;
			case '369':
				$return = ['type' => 'error','message' => 'Reembolso não encontrado'];
				break;
			case '370':
				$return = ['type' => 'error','message' => 'Pedido falhou. Entre em contato com a Rede'];
				break;
			case '371':
				$return = ['type' => 'error','message' => 'Transação não disponível para reembolso. Tente novamente em algumas horas'];
				break;
		}
		return $return;
	}

	public function returnErroServidor($code) {
		$return = [];

		switch($code) {
			case '400':
				$return = ['type' => 'error','message' => 'Requisição mal formatada.'];
				break;
			case '401':
				$return = ['type' => 'error','message' => 'Requisição requer autenticação.'];
				break;
			case '403':
				$return = ['type' => 'error','message' => 'Requisição negada.'];
				break;
			case '404':
				$return = ['type' => 'error','message' => 'Recurso não encontrado.'];
				break;
			case '405':
				$return = ['type' => 'error','message' => 'Método não permitido.'];
				break;
			case '408':
				$return = ['type' => 'error','message' => 'Tempo esgotado para requisição.'];
				break;
			case '413':
				$return = ['type' => 'error','message' => 'Requisição excede o tamanho máximo permitido.'];
				break;
			case '415':
				$return = ['type' => 'error','message' => 'Tipo de mídia inválida (verificar o header content-type da requisição)'];
				break;
			case '422':
				$return = ['type' => 'error','message' => 'Exceção de negócio.'];
				break;
			case '429':
				$return = ['type' => 'error','message' => 'Requisição excede a quantidade máxima de chamadas permitidas à API.'];
				break;
			case '500':
				$return = ['type' => 'error','message' => 'Erro de servidor.'];
				break;
		}
		return $return;
	}

	public function returnIntegracao($code) {
		$return = [];

		switch($code) {
			case '1':
				$return = ['type' => 'error','message' => 'expirationYear: tamanho do parâmetro inválido.'];
				break;
			case '2':
				$return = ['type' => 'error','message' => 'expirationYear: formato de parâmetro inválido.'];
				break;
			case '3':
				$return = ['type' => 'error','message' => 'expirationYear: parâmetro obrigatório ausente.'];
				break;
			case '4':
				$return = ['type' => 'error','message' => 'cavv: tamanho do parâmetro inválido.'];
				break;
			case '5':
				$return = ['type' => 'error','message' => 'cavv: formato de parâmetro inválido.'];
				break;
			case '6':
				$return = ['type' => 'error','message' => 'postalCode: tamanho do parâmetro inválido.'];
				break;
			case '7':
				$return = ['type' => 'error','message' => 'postalCode: formato de parâmetro inválido.'];
				break;
			case '8':
				$return = ['type' => 'error','message' => 'postalCode: parâmetro obrigatório ausente.'];
				break;
			case '9':
				$return = ['type' => 'error','message' => 'complemento: tamanho de parâmetro inválido.'];
				break;
			case '10':
				$return = ['type' => 'error','message' => 'complemento: formato de parâmetro inválido.'];
				break;
			case '11':
				$return = ['type' => 'error','message' => 'departureTax: formato de parâmetro inválido.'];
				break;
			case '12':
				$return = ['type' => 'error','message' => 'documentNumber: tamanho de parâmetro inválido.'];
				break;
			case '13':
				$return = ['type' => 'error','message' => 'documentNumber: formato de parâmetro inválido.'];
				break;
			case '14':
				$return = ['type' => 'error','message' => 'documentNumber: parâmetro obrigatório ausente.'];
				break;
			case '15':
				$return = ['type' => 'error','message' => 'securityCode: tamanho de parâmetro inválido.'];
				break;
			case '16':
				$return = ['type' => 'error','message' => 'securityCode: formato de parâmetro inválido.'];
				break;
			case '17':
				$return = ['type' => 'error','message' => 'distributorAffiliation: tamanho de parâmetro inválido.'];
				break;
			case '18':
				$return = ['type' => 'error','message' => 'distributorAffiliation: formato de parâmetro inválido.'];
				break;
			case '19':
				$return = ['type' => 'error','message' => 'xid: tamanho de parâmetro inválido.'];
				break;
			case '20':
				$return = ['type' => 'error','message' => 'eci: formato de parâmetro inválido.'];
				break;
			case '21':
				$return = ['type' => 'error','message' => 'xid: Parâmetro necessário para cartão Visa está ausente.'];
				break;
			case '22':
				$return = ['type' => 'error','message' => 'street: o parâmetro obrigatório está ausente.'];
				break;
			case '23':
				$return = ['type' => 'error','message' => 'street: formato de parâmetro inválido.'];
				break;
			case '24':
				$return = ['type' => 'error','message' => 'Associação: Tamanho de parâmetro inválido'];
				break;
			case '25':
				$return = ['type' => 'error','message' => 'Associação: Formato de parâmetro inválido.'];
				break;
			case '26':
				$return = ['type' => 'error','message' => 'Associação: parâmetro obrigatório ausente.'];
				break;
			case '27':
				$return = ['type' => 'error','message' => 'Parâmetro cavv ou eci faltando.'];
				break;
			case '28':
				$return = ['type' => 'error','message' => 'code: tamanho do parâmetro inválido.'];
				break;
			case '29':
				$return = ['type' => 'error','message' => 'código: formato de parâmetro inválido.'];
				break;
			case '30':
				$return = ['type' => 'error','message' => 'code: o parâmetro obrigatório está ausente.'];
				break;
			case '31':
				$return = ['type' => 'error','message' => 'softdescriptor: tamanho de parâmetro inválido.'];
				break;
			case '32':
				$return = ['type' => 'error','message' => 'softdescriptor: formato de parâmetro inválido.'];
				break;
			case '33':
				$return = ['type' => 'error','message' => 'expirationMonth: formato de parâmetro inválido.'];
				break;
			case '34':
				$return = ['type' => 'error','message' => 'Código: formato de parâmetro inválido.'];
				break;
			case '35':
				$return = ['type' => 'error','message' => 'expirationMonth: missing required parameter.'];
				break;
			case '36':
				$return = ['type' => 'error','message' => 'cardNumber: tamanho de parâmetro inválido.'];
				break;
			case '37':
				$return = ['type' => 'error','message' => 'cardNumber: formato de parâmetro inválido.'];
				break;
			case '38':
				$return = ['type' => 'error','message' => 'cardNumber: parâmetro obrigatório ausente.'];
				break;
			case '39':
				$return = ['type' => 'error','message' => 'reference: Tamanho do parâmetro inválido.'];
				break;
			case '40':
				$return = ['type' => 'error','message' => 'reference: formato de parâmetro inválido.'];
				break;
			case '41':
				$return = ['type' => 'error','message' => 'reference: parâmetro necessário ausente.'];
				break;
			case '42':
				$return = ['type' => 'error','message' => 'reference: o número do pedido já existe.'];
				break;
			case '43':
				$return = ['type' => 'error','message' => 'number: tamanho do parâmetro inválido.'];
				break;
			case '44':
				$return = ['type' => 'error','message' => 'number: formato de parâmetro inválido.'];
				break;
			case '45':
				$return = ['type' => 'error','message' => 'number: parâmetro obrigatório ausente.'];
				break;
			case '46':
				$return = ['type' => 'error','message' => 'parcelas: não corresponde a transação de autorização.'];
				break;
			case '47':
				$return = ['type' => 'error','message' => 'origem: formato de parâmetro inválido.'];
				break;	
			case '49':
				$return = ['type' => 'error','message' => 'O valor da transação excede o autorizado.'];
				break;
			case '50':
				$return = ['type' => 'error','message' => 'parcelas: formato de parâmetro inválido.'];
				break;
			case '51':
				$return = ['type' => 'error','message' => 'Produto ou serviço desativado para este comerciante.'];
				break;
			case '53':
				$return = ['type' => 'error','message' => 'Transação não permitida para o remetente.'];
				break;
			case '54':
				$return = ['type' => 'error','message' => 'parcelas: Parâmetro não permitido para esta transação.'];
				break;
			case '55':
				$return = ['type' => 'error','message' => 'cardHolderName: tamanho de parâmetro inválido.'];
				break;
			case '56':
				$return = ['type' => 'error','message' => 'Erro nos dados reportados. Tente novamente.'];
				break;
			case '57':
				$return = ['type' => 'error','message' => 'Associação: Comerciante Inválido.'];
				break;
			case '58':
				$return = ['type' => 'error','message' => 'Não autorizado. Por favor, entre em contato com o emissor.'];
				break;
			case '59':
				$return = ['type' => 'error','message' => 'cardHolderName: formato de parâmetro inválido.'];
				break;
			case '60':
				$return = ['type' => 'error','message' => 'street: tamanho do parâmetro inválido.'];
				break;
			case '61':
				$return = ['type' => 'error','message' => 'subscription: Formato de parâmetro inválido.'];
				break;
			case '63':
				$return = ['type' => 'error','message' => 'softdescriptor: não habilitado para este comerciante.'];
				break;
			case '64':
				$return = ['type' => 'error','message' => 'Transação não processada. Tente novamente.'];
				break;
			case '65':
				$return = ['type' => 'error','message' => 'Token: token inválido.'];
				break;
			case '66':
				$return = ['type' => 'error','message' => 'departureTax: tamanho de parâmetro inválido.'];
				break;
			case '67':
				$return = ['type' => 'error','message' => 'departureTax: formato de parâmetro inválido.'];
				break;
			case '68':
				$return = ['type' => 'error','message' => 'departureTax: parâmetro obrigatório ausente.'];
				break;
			case '69':
				$return = ['type' => 'error','message' => 'Transação não permitida para este produto ou serviço.'];
				break;
			case '70':
				$return = ['type' => 'error','message' => 'Quantidade: tamanho do parâmetro inválido.'];
				break;
			case '71':
				$return = ['type' => 'error','message' => 'Quantidade: formato de parâmetro inválido.'];
				break;
			case '72':
				$return = ['type' => 'error','message' => 'Entre em contato com o remetente.'];
				break;
			case '73':
				$return = ['type' => 'error','message' => 'Quantidade: parâmetro obrigatório ausente.'];
				break;
			case '74':
				$return = ['type' => 'error','message' => 'Falha de comunicação. Tente novamente.'];
				break;
			case '75':
				$return = ['type' => 'error','message' => 'departureTax: O parâmetro não deve ser enviado para este tipo de transação.'];
				break;		
			case '76':
				$return = ['type' => 'error','message' => 'tipo: formato de parâmetro inválido.'];
				break;
			case '78':
				$return = ['type' => 'error','message' => 'Transação não existe.'];
				break;
			case '79':
				$return = ['type' => 'error','message' => 'Cartão expirado. A transação não pode ser reenviada. Por favor, entre em contato com o emissor.'];
				break;
			case '80':
				$return = ['type' => 'error','message' => 'Não autorizado. Por favor, entre em contato com o emissor. (Fundos insuficientes).'];
				break;
			case '82':
				$return = ['type' => 'error','message' => 'Transação não autorizada para cartão de débito.'];
				break;
			case '83':
				$return = ['type' => 'error','message' => 'Não autorizado. Por favor, entre em contato com o emissor.'];
				break;
			case '84':
				$return = ['type' => 'error','message' => 'Não autorizado. A transação não pode ser reenviada. Por favor, entre em contato com o emissor.'];
				break;
			case '85':
				$return = ['type' => 'error','message' => 'Combo: tamanho de parâmetro inválido.'];
				break;
			case '86':
				$return = ['type' => 'error','message' => 'cartão expirado.'];
				break;
			case '88':
				$return = ['type' => 'error','message' => 'Trader não aprovado. Ajuste seu site e entre em contato com a Rede para refazer as transações.'];
				break;
			case '89':
				$return = ['type' => 'error','message' => 'Token: token inválido.'];
				break;
			case '97':
				$return = ['type' => 'error','message' => 'tid: tamanho do parâmetro inválido.'];
				break;
			case '98':
				$return = ['type' => 'error','message' => 'tid: formato de parâmetro inválido.'];
				break;
			case '150':
				$return = ['type' => 'error','message' => 'Tempo Limite. Tente novamente.'];
				break;
			case '151':
				$return = ['type' => 'error','message' => 'parcelas: maior que o permitido.'];
				break;
			case '153':
				$return = ['type' => 'error','message' => 'documentNumber: número inválido.'];
				break;
			case '154':
				$return = ['type' => 'error','message' => 'incorporado: formato de parâmetro inválido.'];
				break;
			case '155':
				$return = ['type' => 'error','message' => 'eci: falta do parâmetro obrigatório.'];
				break;
			case '156':
				$return = ['type' => 'error','message' => 'eci: tamanho de parâmetro inválido.'];
				break;
			case '157':
				$return = ['type' => 'error','message' => 'cavv: o parâmetro requerido está faltando.'];
				break;
			case '158':
				$return = ['type' => 'error','message' => 'capture: Tipo não permitido para esta transação.'];
				break;
			case '159':
				$return = ['type' => 'error','message' => 'userAgent: tamanho de parâmetro inválido.'];
				break;
			case '160':
				$return = ['type' => 'error','message' => 'URLs: obrigatório parâmetro ausente (tipo).'];
				break;
			case '161':
				$return = ['type' => 'error','message' => 'URLs: formato de parâmetro inválido.'];
				break;
			case '167':
				$return = ['type' => 'error','message' => 'pedido inválido JSON.'];
				break;
			case '169':
				$return = ['type' => 'error','message' => 'Tipo de Conteúdo Inválido.'];
				break;
			case '171':
				$return = ['type' => 'error','message' => 'Operação não permitida para esta transação.'];
				break;
			case '173':
				$return = ['type' => 'error','message' => 'Autorização expirada.'];
				break;
			case '176':
				$return = ['type' => 'error','message' => 'URLs: obrigatório parâmetro ausente (url).'];
				break;
			default:
				$return = ['type' => 'success','message' => 'Success'];
				break;
		}
		return $return;
	}

	public function returnEmissor($code) {
		$return = [];

		switch($code) {
			case '00':
				$return = ['type' => 'success','message' => 'Success.'];
				break;
			case '101':
				$return = ['type' => 'error','message' => 'Não autorizado. Problemas no cartão, entre em contato com o emissor.'];
				break;
			case '102':
				$return = ['type' => 'error','message' => 'Não autorizado. Verifique a situação da loja com o emissor.'];
				break;
			case '103':
				$return = ['type' => 'error','message' => 'Não autorizado. Por favor, tente novamente.'];
				break;
			case '104':
				$return = ['type' => 'error','message' => 'Não autorizado. Por favor, tente novamente.'];
				break;
			case '105':
				$return = ['type' => 'error','message' => 'Não autorizado. Cartão inválido e/ou com saldo insuficiente. Favor utilizar outro cartão de crédito.'];
				break;
			case '106':
				$return = ['type' => 'error','message' => 'Erro no processamento do emissor. Por favor, tente novamente.'];
				break;
			case '107':
				$return = ['type' => 'error','message' => 'Não autorizado. Por favor, tente novamente.'];
				break;
			case '108':
				$return = ['type' => 'error','message' => 'Não autorizado. Valor não permitido para este tipo de cartão.'];
				break;
			case '109':
				$return = ['type' => 'error','message' => 'Não autorizado. Cartão inexistente.'];
				break;
			case '110':
				$return = ['type' => 'error','message' => 'Não autorizado. Tipo de transação não permitido para este cartão.'];
				break;
			case '111':
				$return = ['type' => 'error','message' => 'Não autorizado. Fundos insuficientes.'];
				break;
			case '112':
				$return = ['type' => 'error','message' => 'Não autorizado. A data de expiração expirou.'];
				break;
			case '113':
				$return = ['type' => 'error','message' => 'Não autorizado. Identificou risco moderado pelo emissor.'];
				break;
			case '114':
				$return = ['type' => 'error','message' => 'Não autorizado. O cartão não pertence à rede de pagamento.'];
				break;
			case '115':
				$return = ['type' => 'error','message' => 'Não autorizado. Excedeu o limite de transações permitidas no período.'];
				break;
			case '116':
				$return = ['type' => 'error','message' => 'Não autorizado. Por favor, entre em contato com o emissor do cartão.'];
				break;
			case '117':
				$return = ['type' => 'error','message' => 'Transação não encontrada.'];
				break;
			case '118':
				$return = ['type' => 'error','message' => 'Não autorizado. Cartão bloqueado.'];
				break;
			case '119':
				$return = ['type' => 'error','message' => 'Não autorizado. Código de segurança inválido.'];
				break;
			case '121':
				$return = ['type' => 'error','message' => 'Erro no processamento. Por favor, tente novamente.'];
				break;
			case '122':
				$return = ['type' => 'error','message' => 'Transação enviada anteriormente.'];
				break;
			case '123':
				$return = ['type' => 'error','message' => 'Não autorizado. O portador solicitou o fim das recorrências no emissor.'];
				break;
			case '124':
				$return = ['type' => 'error','message' => 'Não autorizado. Entre em contato com a Rede'];
				break;
			case '170':
				$return = ['type' => 'error','message' => 'Transação zero dólar não permitida para este cartão.'];
				break;
			case '174':
				$return = ['type' => 'success','message' => 'Sucesso de transação em zero dólar.'];
				break;
			case '175':
				$return = ['type' => 'error','message' => 'Negociação de zero dólar negada.'];
				break;
		}
		return $return;
	}

}
