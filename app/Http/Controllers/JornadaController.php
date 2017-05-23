<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modulos\Escuela\Promotor;
use App\Modulos\Escuela\Jornada;
use App\Modulos\Parque\Parque;
use Idrd\Usuarios\Repo\Documento;
use Idrd\Usuarios\Repo\Pais;
use Idrd\Usuarios\Repo\Etnia;
use Idrd\Parques\Repo\Localidad;
use App\Http\Requests;
use App\Http\Requests\GuardarJornada;

class JornadaController extends Controller
{
	private $promotor;

    public function __construct()
	{
		if (isset($_SESSION['Usuario']))
			$this->usuario = $_SESSION['Usuario'];

		$this->promotor = Promotor::with('persona')
									->where('Id_Persona', $this->usuario[0])
									->first();
	}

    public function index(Request $request)
    {
    	$request->flash();

		if ($request->isMethod('get'))
		{
			$qb = null;
			$elementos = $qb;
		} else {
			$qb = Jornada::with('parque')
							->where('Id_Promotor', $this->promotor['Id_Promotor']);

			$qb = $this->aplicarFiltro($qb, $request);

			$elementos = $qb->whereNull('deleted_at')
							->orderBy('Id_Jornada', 'DESC')
							->get();
		}

		$lista = [
	        'elementos' => $elementos,
			'parques' => $this->parquesHabilitados(),
	        'status' => session('status')
		];

		$datos = [
			'titulo' => 'Jornadas promotor',
			'seccion' => 'Jornadas promotor',
			'lista'	=> view('idrd.jornadas.lista', $lista)
		];

		return view('list', $datos);
    }

    public function formulario(Request $request, $id_jornada = 0)
    {
    	$jornada = null;

    	if ($id_jornada)
    		$jornada = Jornada::with('parque')->find($id_jornada);

    	$formulario = [
			'jornada' => $jornada,
			'promotor' => $this->promotor,
			'parques' => $this->parquesHabilitados(),
			'documentos' => Documento::all(),
	        'paises' => Pais::all(),
	        'etnias' => Etnia::all(),
	        'localidades' => Localidad::all(),
	        'status' => session('status')
		];

		$datos = [
			'titulo' => 'Crear ó editar jornadas',
			'seccion' => 'Promotores',
			'formulario' => view('idrd.jornadas.formulario', $formulario)
		];

		return view('form', $datos);
    }

    public function procesar(GuardarJornada $request)
    {

    }

    private function aplicarFiltro($qb, $request)
	{
		if($request->input('parque') && $request->input('parque') != 'Todos')
		{
			$qb->where('Id_Parque', $request->input('parque'));
		}

		if($request->input('desde'))
		{
			$qb->where('Fecha', '>=', $request->input('desde'));
		}

		if($request->input('hasta'))
		{
			$qb->where('Fecha', '<=', $request->input('hasta'));
		}

		return $qb;
	}

	private function parquesHabilitados() {
		return Parque::whereIn('Id', [8585, 9478, 9989, 9936, 15565, 10721])->get();
	}
}
