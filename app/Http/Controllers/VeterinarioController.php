<?php namespace ClinicaVeterinaria\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Request;
use ClinicaVeterinaria\Veterinario;
use ClinicaVeterinaria\Especialidade;
use ClinicaVeterinaria\Login;
class VeterinarioController extends Controller {
	public function lista(){
		$veterinarioObj = new Veterinario();
		$veterinarios = $veterinarioObj->lista();
		$dados = array("veterinarios" => $veterinarios);
		return view("veterinario/listagem")->with($dados);
	}
	public function consulta($veterinario_id){
		$especialidadeObj = new Especialidade();
		$veterinarioObj = new Veterinario();
		$especialidades = $especialidadeObj->lista();
		$veterinario = $veterinarioObj->consulta($veterinario_id);
		$veterinario[0]->dataAdmissao = convertDateToBrazilian($veterinario[0]->dataAdmissao);
		$dados = array("veterinario" => $veterinario[0],"especialidades" => $especialidades);
		return view("veterinario/formulario")->with($dados);
	}
	/*retorna o formulário para adicionar ou atualizar um veterinário*/
	public function formulario(){
		$especialidadeObj = new Especialidade();
		$especialidades = $especialidadeObj->lista();
		$dados = array("especialidades" => $especialidades);
		return view("veterinario/formulario")->with($dados);
	}
	public function adiciona(){
		$parametros = Request::all();
		/*insere um veterinário no banco de dados*/
		$parametros['cpf'] = documentToDataBase($parametros['cpf']);
		$parametros['crmv'] = documentToDataBase($parametros['crmv']);
		$parametros['telefone'] = phoneToDataBase($parametros['telefone']);
		$parametros['celular'] = phoneToDataBase($parametros['celular']);
		$parametros['dataAdmissao'] = convertDateToAmerican($parametros['dataAdmissao']);
		$veterinarioObj = new Veterinario($parametros);
		$veterinarioObj->save();
		/*insere um login para veterinário no banco de dados*/
		$parametros_login = Request::only("login","senha");
		$parametros_login["perfil_id"] = $veterinarioObj->id;
		$parametros_login['senha'] = MD5($parametros_login['senha']);
		$parametros_login['cargo'] = 'VET';
		Login::create($parametros_login);
		return redirect("/veterinario")->withInput(Request::only("nome"));
	}
	public function exclui(){
		/*excluir veterinário e login*/
		$veterinario_id = Request::input("veterinario_id");
		$login = Request::input("login");
		$veterinarioObj = new Veterinario();		
		$veterinarioObj->deleta($veterinario_id);
		return redirect()->action("VeterinarioController@lista")->withInput(Request::only("veterinario_id"));
	}
	public function atualiza(){
		$veterinario_id = Request::input("veterinario_id");
		$parametros = Request::except("_token","veterinario_id");
		$parametros['cpf'] = documentToDataBase($parametros['cpf']);
		$parametros['crmv'] = documentToDataBase($parametros['crmv']);
		$parametros['telefone'] = phoneToDataBase($parametros['telefone']);
		$parametros['celular'] = phoneToDataBase($parametros['celular']);
		$parametros['dataAdmissao'] = convertDateToAmerican($parametros['dataAdmissao']);
		$veterinarioObj = new Veterinario();
		$veterinarioObj->atualiza($veterinario_id,$parametros);
		return redirect()->action("VeterinarioController@lista");
	}
}