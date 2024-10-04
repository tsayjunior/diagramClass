<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import go from 'gojs'; // Asegúrate de haber instalado gojs
import DarkButton from '@/Components/DarkButton.vue';
// import Echo from 'laravel-echo';

let myDiagram = ref({});

let widthOfTheRectangle = 100;
let heightOfTheRectangle = 60;
var linkDataArray = ref([
  { to: "Beta", from: "Alpha", color: "blue" },
  // { to: "Zeta", from: "Alpha", color: "blue" }
]);
let ref_arrowShape = ref(null);
let lineShape = ref(null);
let ref_link = ref(null);
let show_relations = ref(false);
let id_key = ref(1); // identificador
let process_create_table_intermedia = ref(0); // identificador
let nodeDataArray = ref([
  { key: id_key, color: "black", loc: "0 0" },
]);
let dataClickeado = ref([]);
let node_padre = ref({});
let node_other_father = ref({});
let node_intermedia = ref({});
let is_move = ref(false);// si se esta moviendo algun nodo se pone true
let node_delete = ref(0);// si se esta moviendo algun nodo se pone true

const props = defineProps({
  user: { type: Object },
  sala: { type: Object },
  array_node: { type: Object },
  array_links: { type: Object },
  array_links_intermedia: { type: Object },
  positions: { type: Object },
})
// Inicializar el diagrama cuando el componente se monte
onMounted(() => {
  init(); // Llama a la función init() cuando el componente se monte
  listen_sockets();
  if (Array.isArray(props.array_node) && props.array_node.length === 0) {
    console.log('El array está vacío');
  } else {
    console.log('El array no está vacío');
    props.array_node.forEach((node, index) => {
      console.log(`Elemento ${index}:`, node['key']);

      const [x, y] = node['loc'].split(',').map(coord => coord.trim()); // trim() para eliminar espacios innecesarios

      console.log(x);  // '52.800018310547'
      console.log(y);  // '93.599945068359'
      console.log('lo que tiene el nod => ', node['loc']);
      addPassSocket(node);
      moveNodo(parseFloat(x), parseFloat(y), node['key']);
    });
  }
  if (Array.isArray(props.array_links) && props.array_links.length === 0) {
    console.log('El array está vacío');
  } else {
    console.log('El array no está vacío');
    props.array_links.forEach((link, index) => {
      console.log(`Elemento ${index}:`, link);
      createLink(link['fromText'], link['toText'], link['valueFrom'], link['valueTo'], link['type_relation']);
    });
  }
  if (Array.isArray(props.array_links_intermedia) && props.array_links_intermedia.length === 0) {
    console.log('El array está vacío');
  } else {
    props.array_links_intermedia.forEach((link, index) => {
      create_relation_intermedia(link.fromA, link.fromB, link.to);
      console.log(' =============================> ', link);
    });
  }
  //   Echo.private(`chat.${props.currentUser.id}`)
  // 	.listen(
  // 		"MessageSent",
  // 		(response) => {
  // 			// messages.value.push(response.message);
  // 			console.log(reponse);
  // 		}
  // 	)
});
function listen_sockets() {
  let value = null;
  console.log(' listen_sockets =>> ', props.user.id, props.user.code_sala_activate);
  Echo.private(`diagramComunication.${props.user.code_sala_activate}`)
    .listen(
      "DiagramComunication",
      (response) => {
        console.log('-------------- al escuchar sockets -------------------------', props.user.id, response.message.id);
        if (props.user.id !== response.message.id) {
          console.log(response.message);
          switch (response.message.message?.type) {
            case 'create_clase':
              addPassSocket(response.message.message?.msj)
              break;
            case 'move_node':
              let data = response.message.message?.msj;
              console.log(' mover nodo = > ', data);
              moveNodo(data.coordenadaX, data.coordenadaY, data.nodeKey,)
              break;
            case 'edit_class':
              value = response.message.message?.msj;
              console.log(' edit_class = > ', value);
              setEditLeterNode(value.key, value.type, value.data_text);
              // moveNodo(data.coordenadaX, data.coordenadaY, data.nodeKey,)
              break;
            case 'create_link':
              value = response.message.message?.msj;
              console.log(' create_link = > ', value);
              createLink(value.fromText, value.toText, value.valueFrom, value.valueTo);
              // moveNodo(data.coordenadaX, data.coordenadaY, data.nodeKey,)
              break;
            case 'modified_leter_link':
              value = response.message.message?.msj;
              console.log(' modified_leter_link = > ', value);
              updateLinkText(value.fromText, value.toText, value.type, value.valueText)
              break;
            case 'delete_link':
              value = response.message.message?.msj;
              console.log(' delete_link = > ', value);
              removeLinkByNodes(value.from, value.to);
              // updateLinkText(value.fromText, value.toText, value.type, value.valueText)
              break;
            case 'delete_class':
              value = response.message.message?.msj;
              console.log(' delete_class = > ', value);
              removeNodeByKey(value.key);
              // updateLinkText(value.fromText, value.toText, value.type, value.valueText)
              break;
            case 'generalizacion':
              value = response.message.message?.msj;
              modifyLinkAttributes(value.from, value.to, response.message.message.type);
              break;
            case 'asociacion':
              value = response.message.message?.msj;
              modifyLinkAttributes(value.from, value.to, response.message.message.type);
              break;
            case 'agregacion':
              value = response.message.message?.msj;
              modifyLinkAttributes(value.from, value.to, response.message.message.type);
              break;
            case 'composicion':
              value = response.message.message?.msj;
              modifyLinkAttributes(value.from, value.to, response.message.message.type);
              break;
            case 'dependencia':
              value = response.message.message?.msj;
              modifyLinkAttributes(value.from, value.to, response.message.message.type);
              break;
            case 'create_clase_intermedia':
              value = response.message.message?.msj;
              create_relation_intermedia(value.fromA, value.fromB, value.to);
              // modifyLinkAttributes(value.from, value.to, response.message.message.type);
              break;
            case 'simulation_click':
              value = response.message.message?.msj;
              selectNodeByKey(value.key);
              // modifyLinkAttributes(value.from, value.to, response.message.message.type);
              break;
            case 'simulation_desclick':
              value = response.message.message?.msj;
              deselectNodeByKey(value.key);
              // modifyLinkAttributes(value.from, value.to, response.message.message.type);
              break;

            default:
              break;
          }
        }
      }
    )

  // funcion para editar las letras de un nodo
  function setEditLeterNode(key, type, date_new) {
    // Obtén el nodo mediante su key
    var node = myDiagram.findNodeForKey(key);

    if (node !== null) {
      // Modifica el valor de los campos "id", "methods" y "encabezado" en el objeto data del nodo
      if (type == 'text1') {
        // console.log('***********************************************************', key, date_new, node.data);
        myDiagram.model.setDataProperty(node.data, "text1", date_new);
      } else if (type == 'text2') {
        myDiagram.model.setDataProperty(node.data, "text2", date_new);
      } else {
        myDiagram.model.setDataProperty(node.data, "text3", date_new);
      }
    }
  }
  // Echo.join(`diagramComunication.${props.user.code_sala_activate}`)
  //   .here((users) => {
  //       console.log('Usuarios conectados:', users);
  //   })
  //   .joining((user) => {
  //       console.log(`${user.name} se ha unido.`);
  //   })
  //   .leaving((user) => {
  //       console.log(`${user.name} ha salido.`);
  //   })
  //   .error((error) => {
  //       console.error('Error en el canal:', error);
  //   });
}
function selectNodeByKey(key) {
  const node = myDiagram.findNodeForKey(key);
  
  if (node) {
    // Cambia la selección del nodo
    myDiagram.select(node); // Selecciona el nodo visualmente

    // Opcional: Si deseas asegurarte de que el nodo sea visible (por ejemplo, desplazarte a él)
    myDiagram.scrollToRect(node.actualBounds);
  }
}
function deselectNodeByKey(key) {
  // Buscar el nodo por su clave (key)
  var node = myDiagram.findNodeForKey(key);
  
  // Verificar si el nodo existe y si está seleccionado
  if (node !== null && node.isSelected) {
    // Quitar el nodo de la selección
    node.isSelected = false;
  }
}
const create_relation_intermedia = (fromA, fromB, to) => {
  console.log(' ingresa a crear relacion intermedia -------------------');
  const newLinkDataA = {
    from: fromA,
    to: to,
    fromLabel: '*',
    toLabel: '',
    isOrthogonal: true,  // Esta relación será ortogonal
  };
  const newLinkDataB = {
    from: fromB,
    to: to,
    fromLabel: '*',
    toLabel: '',
    isOrthogonal: true,  // Esta relación será ortogonal
  };
  console.log(newLinkDataA, newLinkDataB);
  myDiagram.model.addLinkData(newLinkDataA);
  myDiagram.model.addLinkData(newLinkDataB);
}
const clean_data_for_create_intermedia = () => {
  node_padre.value = {};
  node_other_father.value = {};
  node_intermedia.value = {};
}
const process_create_relation_intermedia = (value) => {
  process_create_table_intermedia.value = value;
  if(value== 4){
    create_relation_intermedia(node_padre.value.key, node_other_father.value.key, node_intermedia.value.key);

    let node_data = { fromA: node_padre.value.key, fromB: node_other_father.value.key, to: node_intermedia.value.key }
    send_sockets(formatSendSocket('create_clase_intermedia', node_data));
    clean_data_for_create_intermedia();
    setTimeout(() => {
      process_create_table_intermedia.value = 0;
    }, 3000);
  }

}
const formatSendSocket = (type, value) => {
  let format = {
    'type': type,
    'msj': value,
  };
  return format;
}
// Definir el método para agregar un rectángulo
// const add = async () => {
// 	// id_key.value = +id_key.value + 1;  // Incrementar el identificador de clave
//   let key = await get_id_key();
// 	let node_data = { key: id_key.value, color: "black", loc: "200 200" };

// 	send_sockets(formatSendSocket('create_clase', node_data));
// 	settAddNew(node_data);
// };
// funcion para editar las letras de un nodo
function createLink(from, to, valueFrom, valueTo, type_relation = null) {
  const newLinkData = {
    from: from,
    to: to,
    fromLabel: valueFrom,
    toLabel: valueTo
  };

  myDiagram.model.addLinkData(newLinkData);
  console.log('//////////////////////////////////////////////////////////////////////');
  console.log(type_relation);
  console.log('//////////////////////////////////////////////////////////////////////');
  if(type_relation!= null){
    switch (type_relation) {
      case 1:
        modifyLinkAttributes(from, to, 'asociacion');
        break;
      case 2:
        modifyLinkAttributes(from, to, 'generalizacion');
        break;
      case 3:
        modifyLinkAttributes(from, to, 'agregacion');
        break;
      case 4:
        modifyLinkAttributes(from, to, 'dependencia');
        break;
      case 5:
        modifyLinkAttributes(from, to, 'composicion');
        break;
    
      default:
        break;
    }
  }
}
const add = async () => {
  // Esperar la respuesta de get_id_key
  try {
    let key_id = await get_id_key(); // Asignar el valor retornado directamente a id_key.value

    // Después de obtener el id_key, puedes usarlo
    // let node_data = { key: key_id, color: "black", loc: "200 200" };
    let node_data = { key: key_id, text1: "nombre tabla", text2: "+id : int", text3: "+methods()", color: "black", loc: "100 100", }
    send_sockets(formatSendSocket('create_clase', node_data));
    settAddNew(node_data);
  } catch (error) {
    console.error('Error al agregar el nodo:', error);
  }
};
// añadir cuand me pasa del socket
const addPassSocket = (node_data) => {
  id_key.value = node_data.key;  // 
  settAddNew(node_data);
};
const settAddNew = (node_data) => {
  console.log('Agregar rectángulo', node_data);
  myDiagram.startTransaction("addNode");  // Iniciar una transacción llamada "addNode"
  // id_key.value = +id_key.value + 1;  // Incrementar el identificador de clave
  myDiagram.model.addNodeData(node_data);  // Agregar el nuevo nodo
  myDiagram.commitTransaction("addNode");  // Finalizar la transacción

}
// Función para cambiar la forma de la flecha
function toggleArrow(link) {
  myDiagram.startTransaction("toggleArrow");  // Iniciar transacción
  ref_arrowShape = link.findObject("TOARROW"); // Buscar el Shape de la flecha
  ref_link = link; // guardo la referencia
  lineShape = link.findObject("LinkShape"); // Encuentra la línea
  console.log('ingresa a toggleArrow ', ref_arrowShape, link, ' - ', ref_arrowShape.toArrow);


  show_relations.value = true;
  //  const shape = link.findObject("TOARROW"); // Encuentra la flecha en el enlace

  // shape.toArrow = "Open";  // Flecha abierta
  // lineShape.strokeDashArray = [6, 3];  // Línea punteada
  // shape.fill = "transparent"; // Sin relleno

  myDiagram.commitTransaction("toggleArrow");  // Terminar transacción
}
const sendDataNodoClickeado = (type) =>{
  
  send_sockets(formatSendSocket(type, dataClickeado.value));
  console.log(dataClickeado.value);
} 
// Definir el método para agregar un rectángulo con flecha
// relacion generalizacion
const withArrow = () => {
  // Lógica para agregar un rectángulo con flecha
  console.log('Agregar rectángulo con flecha');
  myDiagram.startTransaction("toggleArrow");  // Iniciar transacción
  ref_arrowShape.toArrow = "Triangle";
  lineShape.strokeDashArray = [];  // Línea punteada
  ref_arrowShape.fill = "#DAE4E4";
  ref_link.findObject("fromText").text = '';
  ref_link.findObject("toText").text = '';

  myDiagram.commitTransaction("toggleArrow");  // Terminar transacción
  show_relations.value = false;
  sendDataNodoClickeado('generalizacion');
};
// Definir el método para agregar un rectángulo sin flecha
// relacion asociacion
const noArrow = () => {
  // Lógica para agregar un rectángulo sin flecha
  console.log('Agregar rectángulo sin flecha');
  myDiagram.startTransaction("toggleArrow");  // Iniciar transacción
  ref_arrowShape.toArrow = "";
  ref_arrowShape.fill = "#DAE4E4";
  lineShape.strokeDashArray = [];  // Línea punteada
  ref_link.findObject("fromText").text = '1';
  ref_link.findObject("toText").text = '*';
  myDiagram.commitTransaction("toggleArrow");  // Terminar transacción
  show_relations.value = false;
  sendDataNodoClickeado('asociacion');
};

const agregation = () => {
  // Lógica para agregar un rectángulo con flecha
  console.log('Agregar rectángulo con flecha');
  myDiagram.startTransaction("toggleArrow");  // Iniciar transacción
  ref_arrowShape.toArrow = "Diamond";
  ref_arrowShape.fill = "#DAE4E4";
  lineShape.strokeDashArray = [];  // Línea punteada
  ref_link.findObject("fromText").text = '';
  ref_link.findObject("toText").text = '';

  myDiagram.commitTransaction("toggleArrow");  // Terminar transacción
  show_relations.value = false;
  sendDataNodoClickeado('agregacion');
};
const composition = () => {
  // Lógica para agregar un rectángulo con flecha
  console.log('Agregar rectángulo con flecha');
  myDiagram.startTransaction("toggleArrow");  // Iniciar transacción
  ref_arrowShape.toArrow = "Diamond";
  ref_arrowShape.fill = "black";
  lineShape.strokeDashArray = [];  // Línea punteada
  ref_link.findObject("fromText").text = '';
  ref_link.findObject("toText").text = '';

  myDiagram.commitTransaction("toggleArrow");  // Terminar transacción
  show_relations.value = false;
  sendDataNodoClickeado('composicion');
};
// Relacion dependencia
const Dependency = () => {
  // Lógica para agregar un rectángulo sin flecha
  console.log('Agregar rectángulo sin flecha');
  myDiagram.startTransaction("toggleArrow");  // Iniciar transacción
  ref_arrowShape.toArrow = "OpenTriangle";
  lineShape.strokeDashArray = [6, 3];  // Línea punteada
  ref_link.findObject("fromText").text = '';
  ref_arrowShape.fill = "#DAE4E4";
  ref_link.findObject("toText").text = '';
  myDiagram.commitTransaction("toggleArrow");  // Terminar transacción
  show_relations.value = false;
  sendDataNodoClickeado('dependencia');
};
const peticion_sockets = (mesage) => {
  // Crear el objeto que deseas enviar
  const payload = {
    mesage: mesage, // Añade el dato adicional
  };
  send_sockets(payload);
};

const send_sockets = (payload) => {
  // Enviar la solicitud POST solo con el payload
  axios.post(route('diagram_class.peticion_socket'), payload)
    .then(response => {
      // ok('Acabas de unirte a una sala con éxito!!!');
    })
    .catch(errors => {
      console.error(errors);
    });
};

const get_id_key = async () => {
  try {
    // Enviar la solicitud GET y esperar la respuesta
    const response = await axios.get(route('clase.get_id_key'));

    // Retornar el dato que necesitas
    return response.data;
  } catch (error) {
    console.error('Error al obtener el ID:', error);
    throw error; // Lanza el error para que pueda ser manejado en la función que llama a get_id_key
  }
};
// Función para inicializar el diagrama
function init() {
  //   const $ = go.GraphObject.make; // Abreviación para facilitar la creación de objetos GoJS

  //   const diagram = $(go.Diagram, 'myDiagramDiv', {
  //     // Configuración del diagrama
  //   });
  load_my_diagram_div();
  // Aquí puedes agregar más lógica para inicializar el diagrama
}
// Función para manejar la edición del texto en el enlace
function validateText(textBlock, labelType) {
  // Obtener el enlace (relación) en el que se ha editado el texto
  const link = textBlock.part;
  console.log(' validate_text =>', textBlock, labelType);
  // Obtener los datos del enlace (nodos desde y hasta)
  const fromNodeKey = link.data.from;
  const toNodeKey = link.data.to;

  // Obtener el nuevo texto que fue editado
  const newText = textBlock.text;

  // Mostrar información en consola
  console.log(`Texto en ${labelType} editado. Nuevo valor: ${newText}`);
  console.log(`El enlace es desde el nodo: ${fromNodeKey} hacia el nodo: ${toNodeKey}`);
}
function load_my_diagram_div_old() {
  const $ = go.GraphObject.make;  // Inicializa la función de fábrica

  myDiagram = $(go.Diagram, "myDiagramDiv", {
    "undoManager.isEnabled": true,  // Habilitar Ctrl-Z para deshacer y Ctrl-Y para rehacer
  });

  myDiagram.nodeTemplate = $(go.Node, "Auto",
    {
      locationSpot: go.Spot.TopLeft,
      // mouseOver: (e, obj) => pos(obj.part.location),
      // click: (e, obj) => console.log('hace click'),
      mouseEnter: (e, obj) => {
        is_move = true;
        pos(obj.part.data.key);
        // El cursor del mouse ha entrado en el área del nodo
        console.log("Mouse entra en el nodo:", obj.part.data.key, is_move, obj);
      },
      mouseLeave: (e, obj) => {
        is_move = false;
        // El cursor del mouse ha salido del área del nodo
        console.log("Mouse sale del nodo:", obj.part.data.key, is_move, obj);
      },
    },
    // Establece la ubicación en la esquina superior izquierda del nodo
    $(go.Shape, "Rectangle",
      {
        fill: "lightblue", portId: "", cursor: "pointer",
        fromLinkable: true, toLinkable: true,
        fromLinkableDuplicates: true, toLinkableDuplicates: true,
        fromLinkableSelfNode: true, toLinkableSelfNode: true
      }
    ),
    $(go.Panel, "Vertical",
      $(go.TextBlock, {
        text: "Encabezado",
        margin: 5,
        font: "bold 14px sans-serif",
        isMultiline: false,
        editable: true, // Permite la edición del texto
        textEdited: (e, textBlock) => handleTextEdited(e, textBlock)
      }, new go.Binding("text", "text1")),
      $(go.TextBlock, {
        text: "+id : int",
        margin: 5,
        isMultiline: true,
        editable: true, // Permite la edición del texto
        textEdited: (e, textBlock) => handleTextEdited(e, textBlock)  // Permite la edición del texto
      },
        new go.Binding("text", "text2")),
      $(go.TextBlock, {
        text: "+methods()",
        margin: 5,
        isMultiline: true,
        editable: true, // Permite la edición del texto
        textEdited: (e, textBlock) => handleTextEdited(e, textBlock)  // Permite la edición del texto
      },
        new go.Binding("text", "methods"))
    )
  );


  myDiagram.linkTemplate =
    $(go.Link,
      {
        routing: go.Link.Orthogonal,
        relinkableFrom: true,
        relinkableTo: true,
        corner: 5,
        selectable: true, // Permite seleccionar el enlace
        selectionAdorned: true, // Añade un borde alrededor del enlace cuando está seleccionado
        // click: (e, link) => toggleArrow(link) // Cambia la forma de la flecha al hacer clic
      },
      {
        click: (e, obj) => {
          // Se ha hecho clic en el enlace
          console.log("Clic en el enlace:", obj.part.data);
          toggleArrow(obj);

          const data = obj.part.data;
          console.log("Enlace clickeado:");
          // console.log("Desde:", data.from);
          // console.log("Hasta:", data.to);
          // Obtener el texto de los extremos del enlace
          const fromText = obj.findObject("fromText").text;
          const toText = obj.findObject("toText").text;

          // console.log("Texto desde:", fromText);
          // console.log("Texto hasta:", toText);
          
          let valueNodo = { from: data.from, to: data.to, fromText: fromText, toText: toText };
          dataClickeado.value = valueNodo; 
          console.log(' dato clieckeado => ', valueNodo, dataClickeado.value );
        },
        doubleClick: (e, obj) => {
          // Se ha hecho doble clic en el enlace
          console.log("Doble clic en el enlace:", obj.part.data);
        }
      },
      $(go.Shape,
        {
          strokeWidth: 2,
          // strokeDashArray: [4, 2], // Puntos y guiones en la línea
          stroke: "black" // Color de la línea
        },
        new go.Binding("stroke", "fromNode", n => (n && n.data.color) || "black").ofObject()),
      $(go.Shape,
        {
          name: "TOARROW", // Nombre para encontrarlo más tarde
          toArrow: "",
          stroke: "black",
          fill: "#DAE4E4", // Sin relleno
          scale: 3 // Tamaño de la flecha (ajústalo según necesites)
        },
        new go.Binding("stroke", "fromNode", n => (n && n.data.color) || "black").ofObject()
      ),// Etiqueta en el extremo "desde" (from)
      $(go.TextBlock, "1",  // Texto por defecto "1"
        {
          name: "fromText",  // Nombre para encontrarlo después
          segmentIndex: 0, segmentFraction: 0.2, alignmentFocus: go.Spot.TopLeft,
          margin: new go.Margin(0, 20),
          editable: true,  // Permitir la edición del texto
          isMultiline: false,  // Desactivar el modo multilinea
          textEdited: (e, textBlock) => validateText(textBlock)  // Agrega la función de validación aquí
        },  // Ajustes de posición
        new go.Binding("text", "fromLabel")),  // Permite enlazar la propiedad "fromLabel" del modelo

      // Etiqueta en el extremo "hasta" (to)
      $(go.TextBlock, "*",  // Texto por defecto "*"
        {
          name: "toText",  // Nombre para encontrarlo después
          segmentIndex: -1, segmentFraction: 0.8, alignmentFocus: go.Spot.BottomRight,
          margin: new go.Margin(0, 20),  // Aumentar el margen para evitar que el texto sea tapado
          editable: true,  // Permitir la edición del texto
          isMultiline: false,  // Desactivar el modo multilinea
          textEdited: (e, textBlock) => validateText(textBlock)  // Agrega la función de validación aquí
        },  // Ajustes de posición
        new go.Binding("text", "toLabel"))  // Permite enlazar la propiedad "toLabel" del modelo
    );
  // Configura la herramienta de edición de texto
  myDiagram.toolManager.textEditingTool.starting = go.TextEditingTool.SingleClick;
  // Añadir el listener para el texto editado
  // myDiagram.addDiagramListener("TextEditingTool.textEdited", (e) => {
  //   const textBlock = e.subject;
  //   if (textBlock instanceof go.TextBlock) {
  //     validateText(textBlock);
  //   }
  // });
  myDiagram.addDiagramListener("SelectionMoved", function (e) {
    e.subject.each(function (part) {
      if (part instanceof go.Node) {
        const nodeName = part.data.name || "Sin nombre";  // Obtiene el nombre del nodo (suponiendo que el nombre está en la propiedad `name`)
        const nodeKey = part.data.key;  // Obtiene el identificador único del nodo
        const newLocation = part.location;  // Obtiene la nueva posición del nodo

        console.log("Nodo movido:", nodeName);
        console.log("Identificador del nodo (key):", nodeKey);
        console.log("Nueva posición del nodo:", newLocation.toString());
        console.log("Nueva posición del nodo:", newLocation.x, newLocation.y);

        let forMove = { coordenadaX: newLocation.x, coordenadaY: newLocation.y, nodeKey: nodeKey };
        send_sockets(formatSendSocket('move_node', forMove));
        // moveNodo(newLocation.x,newLocation.y, nodeKey);
        // Aquí puedes realizar cualquier acción adicional con la información obtenida
      }
    });
  });
  // myDiagram.model.nodeDataArray = [
  // 	nodeDataArray
  // ];
  // myDiagram.model.linkDataArray = [
  // 	linkDataArray
  // ]
  function pos(key) {

    // while(is_move){
    if (is_move.value) {
      // pos(pos, key)
      let pos_act = getPosition(key);
      setTimeout(() => {
        // console.log("Han pasado 3 segundos", is_move, pos_act, key);
        pos(key);
      }, 30);
    }
    // console.log('ingresa a pos', pos);
    // }
  }
  //obtiene la posicion del nodo
  function getPosition(key) {
    var node = myDiagram.findNodeForKey(key);
    if (node !== null) {
      var part = myDiagram.findPartForData(node.data);
      if (part !== null) {
        return part.position;
      }
    }
  }
  const handleTextEdited = (e, textBlock) => {
    const node = textBlock.part; // Obtén el nodo que contiene el TextBlock
    if (node) {
      const nodeData = node.data; // Obtén los datos del nodo
      const editedText = textBlock.text; // Texto editado
      const key = nodeData.key; // Clave del nodo

      // Aquí puedes implementar tu lógica para actualizar los datos del nodo
      console.log(`Editando nodo: ${key}`);
      console.log(`Texto editado: ${editedText}`);

      // Puedes agregar lógica aquí para modificar el estilo de texto
      // Por ejemplo, si deseas cambiar el color o tamaño de la fuente:
      // textBlock.font = "italic 16px sans-serif"; // Cambiar el estilo de la fuente
    } else {
      console.warn("El nodo es indefinido. Asegúrate de que el TextBlock esté asociado a un nodo.");
    }
  };
  // function moveNodo(posX, posY, nodeKey){
  //   const node = myDiagram.findNodeForKey(nodeKey);  // Busca el nodo por su key
  //   if (node) {
  //     // node.location = new go.Point(posX, posY);  // Cambia la posición del nodo mediante código
  //     node.location = new go.Point(69, -85);  // Cambia la posición del nodo mediante código
  //     console.log(`Nodo movido a la nueva posición (${posX}, ${posY}).`);
  //   }
  // }
  // peticiones axios

}
function load_my_diagram_div() {
  const $ = go.GraphObject.make;  // Inicializa la función de fábrica

  myDiagram = $(go.Diagram, "myDiagramDiv", {
    "undoManager.isEnabled": true,  // Habilitar Ctrl-Z para deshacer y Ctrl-Y para rehacer
  });
  // Define el template de la figura
  myDiagram.nodeTemplate =
    $(go.Node, "Auto",
      {
        locationSpot: go.Spot.TopLeft,
        // mouseOver: (e, obj) => pos(obj.part.location),
        // click: (e, obj) => console.log('hace click'),
        mouseEnter: (e, obj) => {
          is_move = true;
          pos(obj.part.data.key);
          // El cursor del mouse ha entrado en el área del nodo
          console.log("Mouse entra en el nodo:", obj.part.data.key, is_move, obj);
          let forMove = { is_clickeado: true, key: obj.part.data.key };
          send_sockets(formatSendSocket('simulation_click', forMove));
        },
        mouseLeave: (e, obj) => {
          is_move = false;
          // El cursor del mouse ha salido del área del nodo
          console.log("Mouse sale del nodo:", obj.part.data.key, is_move, obj);
          let forMove = { is_clickeado: false, key: obj.part.data.key };
          send_sockets(formatSendSocket('simulation_desclick', forMove));
        },
        click: (e, node) => {
          // Obtener los datos del nodo seleccionado
          const data = node.data;
          
          // Acceder a la clave (key) y a otras propiedades del nodo
          const nodeKey = data.key; // Cambia "key" si tu clave tiene otro nombre
          const otherInfo = data.otherProperty; // Ejemplo de otra propiedad
          if(process_create_table_intermedia.value > 0){
            if(process_create_table_intermedia.value==1){
              node_padre.value = data; 
              process_create_relation_intermedia(2);
              console.log(' informacion de nodo padre', node_padre.value);
            }else if(process_create_table_intermedia.value==2){
              node_other_father.value = data; 
              process_create_relation_intermedia(3);
              console.log(' informacion del otro nodo padre', node_other_father.value);
            }else if(process_create_table_intermedia.value==3){
              node_intermedia.value = data; 
              process_create_relation_intermedia(4);
              console.log(' informacion del nodo intermedio', node_intermedia.value);

            }
            // console.log("Información del nodo:", {
            //   key: nodeKey,
            //   ...data // Muestra todas las propiedades del nodo
            // });
          }
        }
      },
      // Establece la ubicación en la esquina superior izquierda del nodo
      $(go.Shape, "Rectangle",
        {
          fill: "lightblue", portId: "", cursor: "pointer",
          fromLinkable: true, toLinkable: true,
          fromLinkableDuplicates: true,
          fromLinkableSelfNode: true, toLinkableSelfNode: true
        },
        // new go.Binding("fill", "color")
      ),
      $(go.Panel, "Vertical", { margin: 5, },
        $(go.TextBlock, {
          // text: "Encabezado",
          margin: 5,
          font: "bold 14px sans-serif",
          isMultiline: false,
          editable: true, // Permite la edición del texto
          // Evento de edición del encabezado
          textEdited: function (e, tb, oldText) {
            console.log(' nombre clases => ', tb, e.part.data.key, oldText);
            var node = e.part;
            var key = node.data.key;

            let forMove = { key: key, type: 'text1', data_text: oldText };
            send_sockets(formatSendSocket('edit_class', forMove));
            // modifiedDataNode(key, "text1", oldText)
            // console.log("Modificado el encabezado del nodo", key, "a", oldText);
          }
        },
          new go.Binding("text", "text1")
        ),
        $(go.TextBlock, {
          // text: "id",
          margin: 5,
          isMultiline: true,
          editable: true, // Permite la edición del texto
          // Evento de edición del encabezado
          textEdited: function (e, tb, oldText) {
            console.log('*atributos => ', tb, e.part.data.key, oldText);
            var node = e.part;
            var key = node.data.key;
            let forMove = { key: key, type: 'text2', data_text: oldText };
            send_sockets(formatSendSocket('edit_class', forMove));
            // modifiedDataNode(key, "text2", oldText)
            // console.log("Modificado el encabezado del nodo", key, "a", oldText);
          } // Permite la edición del texto
        },
          new go.Binding("text", "text2")),
        $(go.TextBlock, {
          // text: "methods()",
          margin: 5,
          isMultiline: true,
          editable: true, // Permite la edición del texto
          // Evento de edición del encabezado
          textEdited: function (e, tb, oldText) {
            console.log('*Metodos => **', tb, e.part.data.key, oldText);
            var node = e.part;
            var key = node.data.key;
            let forMove = { key: key, type: 'text3', data_text: oldText };
            send_sockets(formatSendSocket('edit_class', forMove));
            // modifiedDataNode(key, "text3", oldText)
            // console.log("Modificado el encabezado del nodo", key, "a", oldText);
          }
        },
          new go.Binding("text", "text3"))
      )
    );
  myDiagram.linkTemplate =
    $(go.Link,
      {
        routing: go.Link.Normal,
        relinkableFrom: true,
        relinkableTo: true,
        corner: 5,
        selectable: true, // Permite seleccionar el enlace
        selectionAdorned: true, // Añade un borde alrededor del enlace cuando está seleccionado
        adjusting: go.Link.Stretch // Asegura que el enlace se estire si es necesario
        // click: (e, link) => toggleArrow(link) // Cambia la forma de la flecha al hacer clic

      },
      {
        click: (e, obj) => {
          // Se ha hecho clic en el enlace
          console.log("Clic en el enlace:", obj.part.data);
          toggleArrow(obj);

          const data = obj.part.data;
          console.log("Enlace clickeado:");
          // console.log("Desde:", data.from);
          // console.log("Hasta:", data.to);
          // Obtener el texto de los extremos del enlace
          const fromText = obj.findObject("fromText").text;
          const toText = obj.findObject("toText").text;

          // console.log("Texto desde:", fromText);
          // console.log("Texto hasta:", toText);
          let valueNodo = { from: data.from, to: data.to, fromText: fromText, toText: toText };
          dataClickeado.value = valueNodo; 
          console.log(' dato clieckeado => ', valueNodo, dataClickeado.value );
        },
        doubleClick: (e, obj) => {
          // Se ha hecho doble clic en el enlace
          console.log("Doble clic en el enlace:", obj.part.data);
        }
      },
      // Enlace dinámico de datos para definir el routing
      new go.Binding("routing", "", function(data) {
        return data.isOrthogonal ? go.Link.Orthogonal : go.Link.Normal;
      }),
      $(go.Shape,
        {
          name: "LinkShape",
          strokeWidth: 2,
          // strokeDashArray: [4, 2], // Puntos y guiones en la línea
          stroke: "black" // Color de la línea
        },
        new go.Binding("stroke", "fromNode", n => (n && n.data.color) || "black").ofObject()),
      $(go.Shape,
        {
          name: "TOARROW", // Nombre para encontrarlo más tarde
          toArrow: "",
          stroke: "black",
          fill: "#DAE4E4", // Sin relleno
          scale: 3 // Tamaño de la flecha (ajústalo según necesites)
        },
        new go.Binding("stroke", "fromNode", n => (n && n.data.color) || "black").ofObject()
      ),// Etiqueta en el extremo "desde" (from)
      $(go.TextBlock, "1",  // Texto por defecto "1"
        {
          name: "fromText",  // Nombre para encontrarlo después
          segmentIndex: 0, segmentFraction: 0.3, 
          alignmentFocus: go.Spot.TopLeft,
          margin: new go.Margin(0, 20),
          editable: true,  // Permitir la edición del texto
          isMultiline: false,  // Desactivar el modo multilinea
          // textEdited: (e, textBlock) => validateText(textBlock, "fromLabel")  // Agrega la función de validación aquí
        },  // Ajustes de posición
        new go.Binding("text", "fromLabel")),  // Permite enlazar la propiedad "fromLabel" del modelo

      // Etiqueta en el extremo "hasta" (to)
      $(go.TextBlock, "*",  // Texto por defecto "*"
        {
          name: "toText",  // Nombre para encontrarlo después
          segmentIndex: -1, segmentFraction: 0.3, 
          alignmentFocus: go.Spot.BottomRight,
          margin: new go.Margin(0, 20),  // Aumentar el margen para evitar que el texto sea tapado
          editable: true,  // Permitir la edición del texto
          isMultiline: false,  // Desactivar el modo multilinea
          // textEdited: (e, textBlock) => validateText(textBlock, "toLabel")  // Agrega la función de validación aquí
        },  // Ajustes de posición
        new go.Binding("text", "toLabel"))  // Permite enlazar la propiedad "toLabel" del modelo
    );
  // Configura la herramienta de edición de texto
  // myDiagram.toolManager.textEditingTool.starting = go.TextEditingTool.SingleClick;

  myDiagram.model = new go.GraphLinksModel(nodeDataArray, []);
  // myDiagram.model.nodeDataArray = [
  //     nodeDataArray
  // ];
  // myDiagram.model.linkDataArray = [
  //     linkDataArray
  // ]
  // myDiagram.model = new go.GraphLinksModel(nodeDataArray, []);
  // console.log('ingresa');

  myDiagram.addDiagramListener("SelectionMoved", function (e) {
    e.subject.each(function (part) {
      if (part instanceof go.Node) {
        const nodeName = part.data.name || "Sin nombre";  // Obtiene el nombre del nodo (suponiendo que el nombre está en la propiedad `name`)
        const nodeKey = part.data.key;  // Obtiene el identificador único del nodo
        const newLocation = part.location;  // Obtiene la nueva posición del nodo

        console.log("Nodo movido:", nodeName);
        console.log("Identificador del nodo (key):", nodeKey);
        console.log("Nueva posición del nodo:", newLocation.toString());
        console.log("Nueva posición del nodo:", newLocation.x, newLocation.y);

        let forMove = { coordenadaX: newLocation.x, coordenadaY: newLocation.y, nodeKey: nodeKey };
        send_sockets(formatSendSocket('move_node', forMove));
        // moveNodo(newLocation.x,newLocation.y, nodeKey);
        // Aquí puedes realizar cualquier acción adicional con la información obtenida
      }
    });
  });
  myDiagram.addDiagramListener("LinkDrawn", (e) => {
    const link = e.subject;
    const fromNode = link.fromNode; // Nodo de origen
    const toNode = link.toNode; // Nodo de destino

    // Obtener el texto de los extremos del enlace
    const fromText = link.data.fromLabel;
    const toText = link.data.toLabel;

    if (fromNode && toNode) {
      // console.log(`Se ha creado un enlace entre '${fromNode.data.key}' y '${toNode.data.key}'`);
      // console.log(`Texto desde: ${fromText}`);
      // console.log(`Texto hasta: ${toText}`);
      let link = { fromText: fromNode.data.key, toText: toNode.data.key, valueFrom: '1', valueTo: '*' };
      send_sockets(formatSendSocket('create_link', link));
    }
  });

  myDiagram.addDiagramListener("LinkRelinked", (e) => {
    const link = e.subject;
    const fromNode = link.fromNode; // Nodo de origen
    const toNode = link.toNode; // Nodo de destino

    // Obtener el texto de los extremos del enlace
    const fromText = link.data.fromLabel;
    const toText = link.data.toLabel;

    if (fromNode && toNode) {
      console.log(`Se ha modificado el enlace. Nuevo enlace entre '${fromNode.data.key}' y '${toNode.data.key}'`);
      console.log(`Texto desde: ${fromText}`);
      console.log(`Texto hasta: ${toText}`);
    }
  });
  myDiagram.addDiagramListener("SelectionDeleted", function (e) {
    // Obtener el objeto eliminado
    var deletedParts = e.subject; // Obtiene las partes eliminadas del diagrama
    deletedParts.each(function (part) {
      // Realiza acciones específicas para cada parte eliminada
      if (part instanceof go.Link) {
        console.log("Enlace eliminado:", part.data);
        send_sockets(formatSendSocket('delete_link', part.data));
        // Realiza acciones adicionales para enlaces eliminados
        // socket.emit('delete_link', part.data)
      } else if (part instanceof go.Node) {
        // console.log("Nodo eliminado:", part.data);
        // Realiza acciones adicionales para nodos eliminados
        // Buscar enlaces que apunten a este nodo eliminado
        var linksToDeletedNode = myDiagram.links.filter(function (link) {
          return link.to === part.data.key; // Verifica si el nodo eliminado es el destino
        });

        // Si hay enlaces que apuntan al nodo eliminado, imprímelos
        if (linksToDeletedNode.count > 0) {
          console.log("Enlaces que apuntan al nodo eliminado:");
          linksToDeletedNode.each(function (link) {
            console.log(link.data);
          });
        } else {
          console.log("No hay enlaces que apunten al nodo eliminado.");
        }
        send_sockets(formatSendSocket('delete_class', part.data));
      }
    });
  });
  // Configuración del diagrama
  // myDiagram.addDiagramListener("TextEditingStarted", (e) => {
  //   const tb = e.subject;  // El TextBlock que está siendo editado
  //   let oldText = tb.text;  // Guarda el texto antiguo antes de la edición
  //   console.log(`Texto antiguo: ${oldText}`);
  // });
  // Listener para después de la edición de texto
  myDiagram.addDiagramListener("TextEdited", (e) => {
    const tb = e.subject;  // El TextBlock que fue editado
    const newText = tb.text;  // El nuevo texto

    // Verificar si el TextBlock tiene un part (enlace)
    if (!tb.part) {
      console.error("No se pudo encontrar el part para este TextBlock.");
      return;
    }

    const link = tb.part;  // Obtener el enlace asociado al TextBlock

    // Verificar si los datos del enlace existen
    if (!link.data) {
      console.error("No se pudo acceder a los datos del enlace.");
      return;
    }

    // Identificar si el texto editado es "fromText" o "toText"
    let type_direction = '';
    if (tb.name === "fromText") {
      type_direction = "fromText";
      // console.log("Se editó el texto desde (fromText).");
    } else if (tb.name === "toText") {
      type_direction = "toText";
      // console.log("Se editó el texto hacia (toText).");
    }

    // Obtener los datos del enlace (nodos desde y hasta)
    const fromNodeKey = link.data.from;
    const toNodeKey = link.data.to;

    // Mostrar la información en la consola
    if (fromNodeKey != undefined && toNodeKey != undefined) {
      console.log('--------------------editar letra de relacion-----------------------------');
      console.log(`Texto nuevo: ${type_direction} => ${newText}`);
      // console.log(`Texto antiguo: ${oldText}`);
      console.log(`Relación desde el nodo: ${fromNodeKey} hacia el nodo: ${toNodeKey}`);
      let link_data = { type: type_direction, fromText: fromNodeKey, toText: toNodeKey, valueText: newText };
      send_sockets(formatSendSocket('modified_leter_link', link_data));
    }
  });

  function pos(key) {

    // while(is_move){
    if (is_move.value) {
      // pos(pos, key)
      let pos_act = getPosition(key);
      setTimeout(() => {
        // console.log("Han pasado 3 segundos", is_move, pos_act, key);
        pos(key);
      }, 30);
    }
    // console.log('ingresa a pos', pos);
    // }
  }

  // funcion para editar las letras de un nodo
  function setEditLeterNode(key, type, date_new) {
    // Obtén el nodo mediante su key
    var node = myDiagram.findNodeForKey(key);

    if (node !== null) {
      // Modifica el valor de los campos "id", "methods" y "encabezado" en el objeto data del nodo
      if (type == 'text1') {
        console.log('***********************************************************', key, date_new, node.data);
        myDiagram.model.setDataProperty(node.data, "text1", date_new);
      } else if (type == 'text2') {
        myDiagram.model.setDataProperty(node.data, "text2", date_new);
      } else {
        myDiagram.model.setDataProperty(node.data, "text3", date_new);
      }
    }
  }
}
// mueve el nodo, segun su identificador, y las posiciones que se le pasa
const moveNodo = (posX, posY, nodeKey) => {
  console.log(' move nodo aaaa', posX, posY, nodeKey);
  const node = myDiagram.findNodeForKey(nodeKey);  // Busca el nodo por su key
  console.log(node);
  if (node) {
    node.location = new go.Point(posX, posY);  // Cambia la posición del nodo mediante código
    // node.location = new go.Point(69, -85);  // Cambia la posición del nodo mediante código
    console.log(`Nodo movido a la nueva posición (${posX}, ${posY}).`);
  }
}
function updateLinkText(fromKey, toKey, type_text, newText) {
  // Comenzar una transacción
  myDiagram.model.startTransaction("updateLinkText");

  // Obtener el array de enlaces del modelo
  var links = myDiagram.model.linkDataArray;

  // Recorrer todos los enlaces
  for (var i = 0; i < links.length; i++) {
    var link = links[i];

    // Verificar si el enlace conecta los nodos correctos
    if (link.from === fromKey && link.to === toKey) {
      // Actualizar el texto según el tipo
      if (type_text === "toText") {
        myDiagram.model.setDataProperty(link, "toLabel", newText); // Cambia toLabel
      } else {
        myDiagram.model.setDataProperty(link, "fromLabel", newText); // Cambia fromLabel
      }

      // Opcional: Confirmar el cambio en la consola
      console.log(`Texto actualizado en el enlace desde ${fromKey} hacia ${toKey}: ${type_text} = "${newText}"`);
      break; // Salir del bucle después de encontrar y actualizar el enlace
    }
  }

  // Finalizar la transacción
  myDiagram.model.commitTransaction("updateLinkText");
}
function removeLinkByNodes(fromKey, toKey) {
  // Comenzar una transacción
  myDiagram.model.startTransaction("updateLinkText");
  console.log('**************** removeLinkByNodes *********************', fromKey, toKey);
  var fromNode = myDiagram.findNodeForKey(fromKey);
  var toNode = myDiagram.findNodeForKey(toKey);

  let fromNodeKey = fromNode?.data.key;
  let toNodeKey = toNode?.data.key;

  if (!fromNodeKey) {
    fromNodeKey = node_delete.value;
  }
  if (!toNodeKey) {
    toNodeKey = node_delete.value;
  }
  var links = myDiagram.model.linkDataArray;
  for (var i = 0; i < links.length; i++) {
    var link = links[i];
    console.log(link.from, link.to);
    if (link.from === fromNodeKey && link.to === toNodeKey) {
      myDiagram.model.removeLinkData(link);
      break;
    }
  }
  // }else{

  // }
  // Finalizar la transacción
  myDiagram.model.commitTransaction("updateLinkText");
}
// funcion para eliminar node
function removeNodeByKey(key) {
  // Comenzar una transacción
  myDiagram.model.startTransaction("updateLinkText");
  node_delete.value = key;
  const nodeToRemove = myDiagram.model.nodeDataArray.find(node => node.key === key);
  if (nodeToRemove) {
    myDiagram.model.removeNodeData(nodeToRemove);
  }
  // Finalizar la transacción
  myDiagram.model.commitTransaction("updateLinkText");
}
function modifyLinkAttributes(fromNodeId, toNodeId, type) {
  // Iniciar transacción
  myDiagram.startTransaction("modifyLinkAttributes");
  console.log('----------------- modificar las relaciones o links *********************************', fromNodeId, toNodeId, type);
  // Buscar el enlace correspondiente en el diagrama
  const link = myDiagram.findLinksByExample({ from: fromNodeId, to: toNodeId }).first();

  if (link) {
    const ref_arrowShape = link.findObject("TOARROW"); // Buscar el Shape de la flecha
    const lineShape = link.findObject("LinkShape"); // Encontrar la línea
    switch (type) {
      case 'generalizacion':
        // Modificar los atributos del enlace
        // myDiagram.model.setDataProperty(link.data, "isOrthogonal", true);
        ref_arrowShape.toArrow = "Triangle"; // Cambiar la forma de la flecha
        // lineShape.isOrthogonal = true; // Cambiar la forma de la flecha
        ref_arrowShape.fill = "#DAE4E4"; // Cambiar el color de la flecha
        lineShape.strokeDashArray = []; // Línea continua (sin punteado)
        // Limpiar textos
        link.findObject("fromText").text = '';
        link.findObject("toText").text = '';
        break;
      case 'asociacion':
        // Modificar los atributos del enlace
        ref_arrowShape.toArrow = ""; // Cambiar la forma de la flecha
        ref_arrowShape.fill = "#DAE4E4"; // Cambiar el color de la flecha
        lineShape.strokeDashArray = []; // Línea continua (sin punteado)
        // Limpiar textos
        link.findObject("fromText").text = '1';
        link.findObject("toText").text = '*';
        break;
      case 'agregacion':
        // Modificar los atributos del enlace
        ref_arrowShape.toArrow = "Diamond"; // Cambiar la forma de la flecha
        ref_arrowShape.fill = "#DAE4E4"; // Cambiar el color de la flecha
        lineShape.strokeDashArray = []; // Línea continua (sin punteado)
        // Limpiar textos
        link.findObject("fromText").text = '';
        link.findObject("toText").text = '';
        break;
      case 'composicion':
        // Modificar los atributos del enlace
        ref_arrowShape.toArrow = "Diamond"; // Cambiar la forma de la flecha
        ref_arrowShape.fill = "black"; // Cambiar el color de la flecha
        lineShape.strokeDashArray = []; // Línea continua (sin punteado)
        // Limpiar textos
        link.findObject("fromText").text = '';
        link.findObject("toText").text = '';
        break;
      case 'dependencia':
        // Modificar los atributos del enlace
        ref_arrowShape.toArrow = "OpenTriangle"; // Cambiar la forma de la flecha
        ref_arrowShape.fill = "#DAE4E4"; // Cambiar el color de la flecha
        lineShape.strokeDashArray = [6, 3];  // Línea punteada
        // Limpiar textos
        link.findObject("fromText").text = '';
        link.findObject("toText").text = '';
        break;
    
      default:
        break;
    }

    console.log("Atributos del enlace modificados:", link.data);
  } else {
    console.log("No se encontró el enlace entre:", fromNodeId, "y", toNodeId);
  }

  // Terminar transacción
  myDiagram.commitTransaction("modifyLinkAttributes");
}
const finished_create_intermedio = () => {
  clean_data_for_create_intermedia();
  process_create_table_intermedia.value = 0;
}

</script>

<style scoped>
/* Asegúrate de que el body tenga un margen de 0 para que el div ocupe toda la pantalla */
body,
html {
  margin: 0;
  padding: 0;
  height: 100%;
  width: 100%;
}

.container {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 0px;
}

#myButtonDiv,
#myDiagramDiv {
  border: 1px solid black;
  background-color: #DAE4E4;
}

#myButtonDiv {
  width: 100px;
  height: 100px;
}

#myDiagramDiv {
  flex: 1;
  height: 800px;
}

.animated-button {
  display: flex;
  align-items: center;
  background-color: #4CAF50;
  border: none;
  color: white;
  padding: 12px 20px;
  font-size: 16px;
  cursor: pointer;
  border-radius: 8px;
  transition: background-color 0.3s ease, transform 0.3s ease;
  gap: 10px;
  margin-bottom: 10px; /* Margen para separación */
}

.animated-button:hover {
  background-color: #45a049;
  transform: scale(1.05);
}

.size-6 {
  width: 24px;
  height: 24px;
}

.button-text {
  font-size: 18px;
}
</style>
<template>

  <Head title="diagrama de clases" />

  <AuthenticatedLayout :show_navigation="false" :show_back="false">
    <template #header>
      Diagramador
    </template>
    <!-- <template> -->
    <div class="container">
      <div id="myButtonDiv"
        style="height: 500px; width: 230px; border: 1px solid black; background-color: #DAE4E4; margin-bottom: 10px;">
        <div style="justify-content: center; align-items: center; margin: 15px; height: 100vh;">
          <span style="text-align: center; font-family: 'Arial', sans-serif; font-size: 20px; font-weight: 600; letter-spacing: 1px; color: #4a4a4a; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);"  v-if="!show_relations">
            Clases
          </span>
          <button @click="add" class="animated-button" v-if="!show_relations">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            <span class="button-text">Clase</span>
          </button>
          <span style="text-align: center; font-family: 'Arial', sans-serif; font-size: 20px; font-weight: 600; letter-spacing: 1px; color: #4a4a4a; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);"  v-if="!show_relations">
            tabla intermedia
          </span>
          <button @click="process_create_relation_intermedia(1)" class="animated-button" v-if="!show_relations && process_create_table_intermedia == 0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
            </svg>
            <span class="button-text">relacionar</span>
          </button>
          <button class="animated-button" v-if="!show_relations && process_create_table_intermedia == 1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
            </svg>
            <span class="button-text">Seleccione clase padre</span>
          </button>
          <button class="animated-button" v-if="!show_relations && process_create_table_intermedia == 2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
            </svg>
            <span class="button-text">seleccione otra clase padre</span>
          </button>
          <button class="animated-button" v-if="!show_relations  && process_create_table_intermedia == 3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
            </svg>
            <span class="button-text">seleccione clase intermedia</span>
          </button>
          <button class="animated-button" v-if="!show_relations  && process_create_table_intermedia == 4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            <span class="button-text">Realizado</span>
          </button>
          <button class="animated-button" @click="finished_create_intermedio" v-if="!show_relations  && process_create_table_intermedia > 0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            <span class="button-text">finalizar</span>
          </button>
          <!-- <div v-if="process_create_table_intermedia>0">
            <span style="text-align: center; font-family: 'Arial', sans-serif; font-size: 15px; font-weight: 600; letter-spacing: 1px; color: #4a4a4a; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);"  v-if="!show_relations">
              Relacion1: {{node_padre.text1}}<br>
            </span>
            <span style="text-align: center; font-family: 'Arial', sans-serif; font-size: 15px; font-weight: 600; letter-spacing: 1px; color: #4a4a4a; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);"  v-if="!show_relations">
              Relacion2: {{node_other_father.text1}}<br>
            </span>
            <span style="text-align: center; font-family: 'Arial', sans-serif; font-size: 15px; font-weight: 600; letter-spacing: 1px; color: #4a4a4a; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);"  v-if="!show_relations">
              Intermedia: {{node_intermedia.text1}}
            </span>
          </div> -->
          <span style="text-align: center; font-family: 'Arial', sans-serif; font-size: 20px; font-weight: 600; letter-spacing: 1px; color: #4a4a4a; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);"  v-if="show_relations">
            Relaciones
          </span>
          <button @click="withArrow" class="animated-button" v-if="show_relations"> 
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
              <path fill-rule="evenodd" d="M20.03 3.97a.75.75 0 0 1 0 1.06L6.31 18.75h9.44a.75.75 0 0 1 0 1.5H4.5a.75.75 0 0 1-.75-.75V8.25a.75.75 0 0 1 1.5 0v9.44L18.97 3.97a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
            <span class="button-text">generalizacion</span>
          </button>
        
          <button @click="agregation" class="animated-button"  v-if="show_relations">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
              <path fill-rule="evenodd" d="M20.03 3.97a.75.75 0 0 1 0 1.06L6.31 18.75h9.44a.75.75 0 0 1 0 1.5H4.5a.75.75 0 0 1-.75-.75V8.25a.75.75 0 0 1 1.5 0v9.44L18.97 3.97a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
            <span class="button-text">agregacion</span>
          </button>
          <button @click="composition" class="animated-button" v-if="show_relations">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
              <path fill-rule="evenodd" d="M20.03 3.97a.75.75 0 0 1 0 1.06L6.31 18.75h9.44a.75.75 0 0 1 0 1.5H4.5a.75.75 0 0 1-.75-.75V8.25a.75.75 0 0 1 1.5 0v9.44L18.97 3.97a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
            <span class="button-text">composicion</span>
          </button>

          <button @click="noArrow" class="animated-button" v-if="show_relations">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
              <path fill-rule="evenodd" d="M20.03 3.97a.75.75 0 0 1 0 1.06L6.31 18.75h9.44a.75.75 0 0 1 0 1.5H4.5a.75.75 0 0 1-.75-.75V8.25a.75.75 0 0 1 1.5 0v9.44L18.97 3.97a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
            <span class="button-text">Asociacion</span>
          </button>
          <button @click="Dependency" class="animated-button" v-if="show_relations">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
              <path fill-rule="evenodd" d="M20.03 3.97a.75.75 0 0 1 0 1.06L6.31 18.75h9.44a.75.75 0 0 1 0 1.5H4.5a.75.75 0 0 1-.75-.75V8.25a.75.75 0 0 1 1.5 0v9.44L18.97 3.97a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
            <span class="button-text">dependencia</span>
          </button>
          <button @click="show_relations = !show_relations" class="animated-button" v-if="show_relations">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
            </svg>
            <span class="button-text">Volver</span>
          </button>
        </div>
      </div>
      <div id="myDiagramDiv" style="height: 500px; width: 1600px; border: 1px solid black; background-color: #DAE4E4;">
      </div>
    </div>

    <!-- <DarkButton @click="peticion_sockets('holaaaaaaaa desde frontend')">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
        class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
      </svg>
    </DarkButton> -->
    <!-- </template> -->
  </AuthenticatedLayout>
</template>
