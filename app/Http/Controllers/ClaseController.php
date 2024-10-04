<?php

namespace App\Http\Controllers;

use App\Events\DiagramComunication;
use App\Models\Attribute;
use App\Models\Clase;
use App\Models\ClassRelationship;
use App\Models\Methods;
use App\Models\TypeRelationship;
use Illuminate\Http\Request;

class ClaseController extends Controller
{
    public function create_clase_socket($request, $user_auth){
        $user_auth->message = $request;
        // dd($request['msj'], $user_auth);

        $clase = Clase::create([
            'name'=> $request['msj']['text1'],
            'color'=> $request['msj']['color'],
            'code_sala'=> $user_auth->code_sala_activate,
            'id_key_go_js'=> $request['msj']['key'],
            'coordenadaX'=> '',
            'coordenadaY'=> '',
        ]);
        $attribute = Attribute::create([
            'name'=> $request['msj']['text2'],
            'clase_id'=> $clase->id,
        ]);
        $methods = Methods::create([
            'name'=> $request['msj']['text3'],
            'clase_id'=> $clase->id,
        ]);
        
        broadcast(new DiagramComunication($user_auth));
    }
    public function move_node($request, $user_auth){
        // dd($request, $user_auth);
        $data_node = $request['msj'];
        $clase = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data_node['nodeKey'])->first();
        $clase->coordenadaX =  $data_node['coordenadaX'];
        $clase->coordenadaY =  $data_node['coordenadaY'];
        $clase->save();
        // dd($clase);
        $user_auth->message = $request;
        broadcast(new DiagramComunication($user_auth));
    }
    public function format_class_init($code_sala){
        
        $class = Clase::where('code_sala', $code_sala)->with('attributes', 'methods')->get();
        $array_nodo = [];
        foreach ($class as $key => $clase) {
            $attributes = '';
            foreach ($clase->attributes as $attribute) {
                $attributes = $attributes . $attribute->name . "\n";
            }
            $methods = '';
            foreach ($clase->methods as $method) {
                $methods = $methods . $method->name . "\n";
            }
            $nodo = [
                'key' => $clase->id_key_go_js,
                'loc' => $clase->coordenadaX. ', '. $clase->coordenadaY,
                'color' => $clase->color,
                'text1' => $clase->name,
                'text2' => $attributes,
                'text3' => $methods,
            ];
            $array_nodo[] = $nodo; // Agrega el nodo al array
        }
        return $array_nodo;

    } 
    public function format_relacions_init($code_sala){
        
        $ClassRelationships = ClassRelationship::where('code_sala', $code_sala)->where('tabla_intermedia_identificador', null)->get();
        $array_links = [];
        foreach ($ClassRelationships as $key => $ClassRelationship) {
            // dd($ClassRelationship->clase_init->id_key_go_js);
            $fromText = $ClassRelationship->clase_init->id_key_go_js;
            $toText = $ClassRelationship->clase_finish->id_key_go_js;
            $link = [
                'fromText' => $fromText,
                'toText' => $toText,
                'valueFrom' => $ClassRelationship->quantity_init,
                'valueTo' => $ClassRelationship->quantity_finish,
                'type_relation' => $ClassRelationship->type_relationship_id,
            ];
            $array_links[] = $link; // Agrega el nodo al array
        }
        return $array_links;
    } 
    public function format_relacions_intermedia_init($code_sala){
        
        $ClassRelationships = ClassRelationship::where('code_sala', $code_sala)->whereNotNull('tabla_intermedia_identificador')->get();
        $ClassRelationships = $ClassRelationships->groupBy('tabla_intermedia_identificador');
        $array_links = [];
        foreach ($ClassRelationships as $key => $ClassRelationship) {
            

            $link = [
                'fromA' => $ClassRelationship[0]->clase_init->id_key_go_js,
                'fromB' => $ClassRelationship[1]->clase_init->id_key_go_js,
                'to' => $ClassRelationship[0]->clase_finish->id_key_go_js,
            ];
            $array_links[] = $link; // Agrega el nodo al array
        }
        return $array_links;
    } 
    public function get_id_key(){
        $ultimoRegistro = Clase::latest('id')->first();
        if($ultimoRegistro){
            return $ultimoRegistro->id +1;
        }else{
            return 1;
        }
    }
    public function edit_class($request, $user_auth){
        $data = $request['msj'];
        $class = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['key'])->first();
        switch ($data['type']) {
            case 'text1': // nombre de clases
                $class->name = $data['data_text'];
                $class->save();
                break;
            case 'text2': // nombre de atributos
                $class->attributes->each(function($attribute) {
                    $attribute->delete(); // Elimina cada atributo individualmente
                });

                $attributes = explode("\n", $data['data_text']);
                foreach ($attributes as $attribute) {
                    // Limpiamos espacios en blanco o saltos de línea innecesarios
                    $line = trim($attribute);
                    if (!empty($line)) {
                        $attribute = Attribute::create([
                            'name'=> $line,
                            'clase_id'=> $class->id,
                        ]);
                    }
                }
                # code...
                break;
            case 'text3': // nombre de metodos
                $class->methods->each(function($method) {
                    $method->delete(); // Elimina cada atributo individualmente
                });

                $methods = explode("\n", $data['data_text']);
                foreach ($methods as $method) {
                    // Limpiamos espacios en blanco o saltos de línea innecesarios
                    $line = trim($method);
                    if (!empty($line)) {
                        $method = Methods::create([
                            'name'=> $line,
                            'clase_id'=> $class->id,
                        ]);
                    }
                }
                break;
            
            default:
                # code...
                break;
        }
        $user_auth->message = $request;
        broadcast(new DiagramComunication($user_auth));
    }
    public function create_link($request, $user_auth){
        
        $data = $request['msj'];
        $classFrom = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['fromText'])->first();
        $classTo = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['toText'])->first();
        // dd($user_auth->code_sala_activate);
        $classRelationShip = ClassRelationship::create([
            'clase_init_id'=> $classFrom->id,
            'clase_finish_id'=> $classTo->id,
            'type_relationship_id'=> TypeRelationship::getTypeRelationship(TypeRelationship::$ASOCIATION)->id,
            'quantity_init'=> $data['valueFrom'],
            'quantity_finish'=> $data['valueTo'],
            'code_sala'=> $user_auth->code_sala_activate,
        ]);       
        // dd($classFrom, $classTo);
        $user_auth->message = $request;
        broadcast(new DiagramComunication($user_auth));
    }
    public function modified_leter_link($request, $user_auth){
        
        $data = $request['msj'];
        $classFrom = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['fromText'])->first();
        $classTo = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['toText'])->first();
        $classRelationShip = ClassRelationship::where('clase_init_id', $classFrom->id)->where('clase_finish_id', $classTo->id)->first();
        if($data['type']== 'fromText'){ // nodo inicial es el que se debe modificar
            $classRelationShip->quantity_init = $data['valueText'];
            $classRelationShip->save();
        }else{ // el otro nodo es el que se debe modificar
            $classRelationShip->quantity_finish = $data['valueText'];
            $classRelationShip->save();
        }
        $user_auth->message = $request;
        broadcast(new DiagramComunication($user_auth));
    }
    public function delete_link($request, $user_auth){
        
        $data = $request['msj'];
        $classFrom = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['from'])->first();
        $classTo = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['to'])->first();
        if($classFrom && $classTo){
            $classRelationShip = ClassRelationship::where('clase_init_id', $classFrom->id)->where('clase_finish_id', $classTo->id)->first();
            if($classRelationShip){
                $classRelationShip->delete(); // Elimina la relación de clase
                // dd($request, $classRelationShip);
            }
        }
        
        
        $user_auth->message = $request;
        broadcast(new DiagramComunication($user_auth));
    }
    public function delete_class($request, $user_auth){
        
        $data = $request['msj'];
        $classFrom = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['key'])->with('attributes', 'methods')->first();
        foreach ($classFrom->attributes as $key => $attribute) {
            $attribute->delete();
        }
        foreach ($classFrom->methods as $key => $method) {
            $method->delete();
        }
        $classFrom->delete();
        $user_auth->message = $request;
        broadcast(new DiagramComunication($user_auth));
    }
    public function draw_relations($request, $user_auth){
        
        $data = $request['msj'];
        $classFrom = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['from'])->first();
        $classTo = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['to'])->first();
        $classRelationShip = ClassRelationship::where('clase_init_id', $classFrom->id)->where('clase_finish_id', $classTo->id)->first();
        switch ($request['type']) {
            case 'dependencia':
                $typeRelationship = TypeRelationship::where('name', TypeRelationship::$DEPENDENCIA)->first();
                $classRelationShip->type_relationship_id = $typeRelationship->id; 
                $classRelationShip->save(); 
                break;
            case 'composicion':
                $typeRelationship = TypeRelationship::where('name', TypeRelationship::$COMPOSITION)->first();
                $classRelationShip->type_relationship_id = $typeRelationship->id;  
                $classRelationShip->save(); 
                break;
            case 'agregacion':
                $typeRelationship = TypeRelationship::where('name', TypeRelationship::$AGREGATION)->first();
                $classRelationShip->type_relationship_id = $typeRelationship->id;  
                $classRelationShip->save(); 
                break;
            case 'asociacion':
                $typeRelationship = TypeRelationship::where('name', TypeRelationship::$ASOCIATION)->first();
                $classRelationShip->type_relationship_id = $typeRelationship->id;  
                $classRelationShip->save(); 
                break;
            case 'generalizacion':
                $typeRelationship = TypeRelationship::where('name', TypeRelationship::$GENERALIZATION)->first();
                $classRelationShip->type_relationship_id = $typeRelationship->id;  
                $classRelationShip->save(); 
                break;
            
            default:
                # code...
                break;
        }
        $user_auth->message = $request;
        broadcast(new DiagramComunication($user_auth));
    }
    public function create_clase_intermedia($request, $user_auth){
        $data = $request['msj'];

        $classFromA = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['fromA'])->first();
        $classFromB = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['fromB'])->first();
        $classTo = Clase::where('code_sala', $user_auth->code_sala_activate)->where('id_key_go_js', $data['to'])->first();
        $classRelationShip = ClassRelationship::whereNotNull('tabla_intermedia_identificador')
                                                    ->max('tabla_intermedia_identificador');
        $classRelationShipId = $classRelationShip? (int) $classRelationShip + 1 : 1;
        
        $classRelationShipA = ClassRelationship::create([
            'clase_init_id'=> $classFromA->id,
            'clase_finish_id'=> $classTo->id,
            'type_relationship_id'=> TypeRelationship::getTypeRelationship(TypeRelationship::$ASOCIATION)->id,
            'quantity_init'=> '*',
            'quantity_finish'=> '',
            'code_sala'=> $user_auth->code_sala_activate,
            'tabla_intermedia_identificador'=> $classRelationShipId,
        ]);    
        $classRelationShipB = ClassRelationship::create([
            'clase_init_id'=> $classFromB->id,
            'clase_finish_id'=> $classTo->id,
            'type_relationship_id'=> TypeRelationship::getTypeRelationship(TypeRelationship::$ASOCIATION)->id,
            'quantity_init'=> '*',
            'quantity_finish'=> '',
            'code_sala'=> $user_auth->code_sala_activate,
            'tabla_intermedia_identificador'=> $classRelationShipId,
        ]);          
        $user_auth->message = $request;
        broadcast(new DiagramComunication($user_auth));
    }
    public function simulation_click($request, $user_auth){
        $user_auth->message = $request;
        broadcast(new DiagramComunication($user_auth));

    }
    public function simulation_desclick($request, $user_auth){
        $user_auth->message = $request;
        broadcast(new DiagramComunication($user_auth));

    }
}
