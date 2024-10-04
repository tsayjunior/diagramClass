<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class SalaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_auth = Auth::user(); // Por ejemplo, devuelve el nombre del usuario
        $user_guests = User::where('type_user', User::$USER_GHEST)->get();
        if($user_auth->type_user == User::$USER_HOST){
            $salas = $user_auth->salas()->with('guests')->get();
            $salas = $salas->map(function ($sala) use ($user_auth) {
                $sala->host_name = $user_auth->name; // Agregas el atributo 'status' con el valor que quieras
                return $sala; // Devuelves el objeto sala con el nuevo atributo
            });
            // $salas = Sala::with('guests')->get();
        }else{
            $salas = $user_auth->salas_guest;
            $salas = $salas->map(function ($sala) {
                $user = User::find($sala->host_id);
                $sala->host_name = $user->name; // Agregas el atributo 'status' con el valor que quieras
                return $sala; // Devuelves el objeto sala con el nuevo atributo
            });
        }
        return Inertia::render('Sala/Index', [
            'salas' => $salas,
            'user' => $user_auth,
            'guests' => $user_guests,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $customMessages = [
            'name.required' => 'El nombre es requerido.',
            'code_sala.required' => 'El codigo de sala es requerido.',
        ];
    
        $validate = $request->validate([
            'name' => 'required|string',
            'code_sala' => 'required|string',
        ], $customMessages);
        
        try {
            DB::beginTransaction();
            $user_auth = Auth::user(); // Por ejemplo, devuelve el nombre del usuario
            $sala = Sala::create([
                'name' => $request['name'],
                'code_sala' => $request['code_sala'],
                'host_id' => $user_auth->id,
            ]);
            // dd('good');
            foreach ($request->guest_selected as $key => $guest) {
                // Supongamos que deseas asignar el usuario sque ha creado la sala
                // $sala->guests()->attach($guest['id']);
            }
            // dd($sala);
            DB::commit();
            return redirect('salas');
        } catch (\Throwable $e) {
            // dd($e);
            Log::error('Error al crear la sala: ' . $e->getMessage());
            DB::rollBack();
            return response()->json(["messagge" => $e->getMessage()], 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sala $sala)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sala $sala)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sala $sala)
    {
        $customMessages = [
            'name.required' => 'El nombre es requerido.',
            'code_sala.required' => 'El codigo de sala es requerido.',
        ];
    
        $validate = $request->validate([
            'name' => 'required|string',
            'code_sala' => 'required|string',
        ], $customMessages);
        $sala->update([
            'name' => $request['name'],
            'code_sala' => $request['code_sala'],
        ]);
        // foreach ($request->guest_selected as $key => $guest) {
        //     // Supongamos que deseas asignar el usuario sque ha creado la sala
        //     $sala->guests()->attach($guest['id']);
        // }
        // dd($request->all());
        return redirect('salas');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sala $sala)
    {
        $sala->enable = $sala->enable == 1? 2 : 1;
        $sala->save();
        // dd( $sala->enable == 1);
        // $sala->delete();
        // dd($sala);
        return redirect('salas');
    }
    public function join_room(Request $request){
        $user_auth = Auth::user(); // Por ejemplo, devuelve el nombre del usuario
        // dd('good');
        $sala = Sala::where('code_sala', $request->code_sala)->first();
        $sala->guests()->attach($user_auth['id']);
        return redirect('salas');
    }
    public function get_room(Request $request){
        $user_auth = Auth::user(); // Por ejemplo, devuelve el nombre del usuario
        $user_guests = User::where('type_user', User::$USER_GHEST)->get();
        if($user_auth->type_user == User::$USER_HOST){
            $salas = $user_auth->salas()->with('guests')->get();
            // $salas = Sala::with('guests')->get();
            $salas = $salas->map(function ($sala) use ($user_auth) {
                $sala->host_name = $user_auth->name; // Agregas el atributo 'status' con el valor que quieras
                return $sala; // Devuelves el objeto sala con el nuevo atributo
            });
        }else{
            $salas = $user_auth->salas_guest;
            $salas = $salas->map(function ($sala) {
                $user = User::find($sala->host_id);
                $sala->host_name = $user->name; // Agregas el atributo 'status' con el valor que quieras
                return $sala; // Devuelves el objeto sala con el nuevo atributo
            });
        }
        return $salas;
    }
}
