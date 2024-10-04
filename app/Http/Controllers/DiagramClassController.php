<?php

namespace App\Http\Controllers;

use App\Events\DiagramComunication;
use App\Events\MessageSent;
use App\Models\Clase;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DiagramClassController extends Controller
{
    public function getRoom(Request $request)
    {
        // Aquí puedes procesar cualquier lógica necesaria

        // return Inertia::location(route('diagram_class.index'));
        // Redirige a la vista de DiagramClass/Index
        // return redirect()->route('diagram_class.index');
        return Inertia::render('DiagramClass/IndexDiagram');
    }

    public function index(Request $request)
    {
        // return Inertia::location(route('diagram_class.index'));
        // dd($request->code_sala);
        
        $claseController = new ClaseController();
        $array_node = $claseController->format_class_init($request->code_sala);
        $array_links = $claseController->format_relacions_init($request->code_sala);
        $array_links_intermedia = $claseController->format_relacions_intermedia_init($request->code_sala);
        $sala = Sala::where('code_sala', $request->code_sala)->first();
        $user_auth = Auth::user();
        $user = User::find($user_auth->id);
        $user->code_sala_activate = $sala->code_sala;
        $user->save();
        // dd($user);
        return Inertia::render('DiagramClass/IndexDiagram',
            [
                'sala' => $sala,
                'user' => $user,
                'array_node' => $array_node,
                'array_links' => $array_links,
                'array_links_intermedia' => $array_links_intermedia,
            ]
        );
        // return Inertia::render('Users/Index', [
        //     'users' => User::paginate()
        // ]);
    }
    
    public function index_get(Request $request)
    {
        // return Inertia::location(route('diagram_class.index'));
        // dd($request->code_sala);
        
        $claseController = new ClaseController();
        $array_node = $claseController->format_class_init($request->code_sala);
        $array_links = $claseController->format_relacions_init($request->code_sala);
        $array_links_intermedia = $claseController->format_relacions_intermedia_init($request->code_sala);
        $sala = Sala::where('code_sala', $request->code_sala)->first();
        $user_auth = Auth::user();
        $user = User::find($user_auth->id);
        $user->code_sala_activate = $sala->code_sala;
        $user->save();
        // dd($user);
        return 
            [
                'sala' => $sala,
                'user' => $user,
                'array_node' => $array_node,
                'array_links' => $array_links,
                'array_links_intermedia' => $array_links_intermedia,
            ];
    }
    public function sent_data(){
        // dd('ingresa');
        $message = Sala::all()->first();
        // $message = 'holas';
        // dd($message);
        broadcast(new MessageSent($message));
    }
    
    public function peticion_socket(Request $request ){
        $user_auth = Auth::user(); // Por ejemplo, devuelve el nombre del usuario
        switch ($request->type) {
            case 'create_clase':
                $claseController = new ClaseController();
                return $claseController->create_clase_socket($request->all(), $user_auth);
                break;
            case 'move_node':
                $claseController = new ClaseController();
                return $claseController->move_node($request->all(), $user_auth);
                break;
            case 'edit_class':
                $claseController = new ClaseController();
                return $claseController->edit_class($request->all(), $user_auth);
                break;
            case 'create_link':
                $claseController = new ClaseController();
                return $claseController->create_link($request->all(), $user_auth);
                break;
            case 'modified_leter_link':
                $claseController = new ClaseController();
                return $claseController->modified_leter_link($request->all(), $user_auth);
                break;
            case 'delete_link':
                $claseController = new ClaseController();
                return $claseController->delete_link($request->all(), $user_auth);
                break;
            case 'delete_class':
                $claseController = new ClaseController();
                return $claseController->delete_class($request->all(), $user_auth);
                break;
            case 'simulation_click':
                $claseController = new ClaseController();
                return $claseController->simulation_click($request->all(), $user_auth);
                break;
            case 'simulation_desclick':
                $claseController = new ClaseController();
                return $claseController->simulation_desclick($request->all(), $user_auth);
                break;
            case 'create_clase_intermedia':
                $claseController = new ClaseController();
                return $claseController->create_clase_intermedia($request->all(), $user_auth);
                break;
            case 'dependencia' || 'composicion' || 'agregacion' || 'asociacion' || 'generalizacion' :
                $claseController = new ClaseController();
                return $claseController->draw_relations($request->all(), $user_auth);
                break;
            
            
            default:
                # code...
                break;
        }
        dd('no deberia ingresar');
        $user_auth->message = $request->mesage; // Por ejemplo, devuelve el nombre del usuario
        // dd($user_auth);
        // $message = 'holas';
        // dd($message);
        broadcast(new DiagramComunication($user_auth));
    }
    public function create_class(Request $request){

    }
    public function download_diagram(Request $request){
         // Define la estructura del archivo XMI
         $attributeController = new AttributeController();
         $xmiContent = $attributeController->create_xmi($request->code_sala);
         // Crea el archivo .xmi en el storage (en la carpeta local "app")
         $fileName = 'diagram.xmi';
         $filePath = 'files/' . $fileName;
 
         // Guarda el contenido en el archivo
         Storage::put($filePath, $xmiContent);
 
         // Verifica si el archivo se creó correctamente
         if (!Storage::exists($filePath)) {
             abort(404, 'No se pudo generar el archivo XMI.');
         }
 
         // Descarga el archivo .xmi generado
         return Storage::download($filePath, $fileName, [
             'Content-Type' => 'application/xml'
         ]);
    }
}
