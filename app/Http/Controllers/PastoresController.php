<?php

namespace App\Http\Controllers;

use App\Models\BaseModel;
use App\Models\PaisesModel;
use App\Models\PastoresModel;
use App\Models\PrincipalModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PastoresController extends Controller
{
    //
    private $base_model;
    private $pastores_model;
    private $paises_model;
    private $principal_model;

    public function __construct() {
        parent:: __construct();
        $this->pastores_model = new PastoresModel();
        $this->base_model = new BaseModel();
        $this->paises_model = new PaisesModel();
        $this->principal_model = new PrincipalModel();
    }

    public function index() {
        $view = "pastores.index";
        $data["title"] = traducir("traductor.titulo_pastores");
        $data["subtitle"] = "";
        $data["tabla"] = $this->pastores_model->tabla()->HTML();

        $botones = array();
        $botones[0] = '<button disabled="disabled" tecla_rapida="F1" style="margin-right: 5px;" class="btn btn-default btn-sm" id="nuevo-pastor"><img style="width: 19px; height: 20px;" src="'.URL::asset('images/iconos/agregar-archivo.png').'"><br>'.traducir("traductor.nuevo").' [F1]</button>';
        $botones[1] = '<button disabled="disabled" tecla_rapida="F2" style="margin-right: 5px;" class="btn btn-default btn-sm" id="modificar-pastor"><img style="width: 19px; height: 20px;" src="'.URL::asset('images/iconos/editar-documento.png').'"><br>'.traducir("traductor.modificar").' [F2]</button>';
        $botones[2] = '<button disabled="disabled" tecla_rapida="F7" style="margin-right: 5px;" class="btn btn-default btn-sm" id="eliminar-pastor"><img style="width: 19px; height: 20px;" src="'.URL::asset('images/iconos/delete.png').'"><br>'.traducir("traductor.eliminar").' [F7]</button>';
        $data["botones"] = $botones;
        $data["scripts"] = $this->cargar_js(["pastores.js"]);
        return parent::init($view, $data);



    }

    public function buscar_datos() {
        $json_data = $this->pastores_model->tabla()->obtenerDatos();
        echo json_encode($json_data);
    }


    public function guardar_pastores(Request $request) {

        $_POST = $this->toUpper($_POST);
        if(!isset($_POST["vigente"])) {
            $_POST["vigente"] = "0";
        }
        if ($request->input("idotrospastores") == '') {
            $result = $this->base_model->insertar($this->preparar_datos("iglesias.otrospastores", $_POST));
        }else{
            $result = $this->base_model->modificar($this->preparar_datos("iglesias.otrospastores", $_POST));
        }

        echo json_encode($result);
    }

    public function eliminar_pastores() {


        try {
            $sql_bautizo = "SELECT * FROM iglesias.miembro WHERE tabla_encargado_bautizo='iglesias.otrospastores' AND encargado_bautizo=".$_REQUEST["id"];
            $bautizo = DB::select($sql_bautizo);

            if(count($bautizo) > 0) {
                throw new Exception(traducir("traductor.eliminar_pastor_bautizo"));
            }

            $sql_historial = "SELECT * FROM iglesias.historial_altasybajas WHERE tabla='iglesias.otrospastores' AND responsable=".$_REQUEST["id"];
            $historial = DB::select($sql_historial);

            if(count($historial) > 0) {
                throw new Exception(traducir("traductor.eliminar_pastor_altasybajas"));
            }

            $result = $this->base_model->eliminar(["iglesias.otrospastores","idotrospastores"]);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(array("status" => "ee", "msg" => $e->getMessage()));
        }
    }


    public function get_pastores(Request $request) {

        $sql = "SELECT * FROM iglesias.otrospastores WHERE idotrospastores=".$request->input("id");
        $one = DB::select($sql);
        echo json_encode($one);
    }

    public function obtener_cargos() {

		$result = $this->pastores_model->obtener_cargos();
        echo json_encode($result);
    }

    public function select_init() {
        $data["idpais"] = $this->paises_model->obtener_todos_paises();
        $data["idtipodoc"] = $this->principal_model->obtener_tipos_documento();
        $data["idcargo"] = $this->pastores_model->obtener_cargos();

        echo json_encode($data);

    }







}
