<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import WarningButton from '@/Components/WarningButton.vue';
import DarkButton from '@/Components/DarkButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputGroup from '@/Components/InputGroup.vue';
import SelectInput from '@/Components/SelectInput.vue'
import {ref, watch, useAttrs } from 'vue'
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    salas: {type: Object},
    guests: {type: Object},
    user: {type: Object}
})
const form = useForm({
    name: '',
    code_sala: '',
    country_id: '',
    guest_selected: [],
})
const salas = ref(props.salas); // Inicializarla con el valor de la prop
const guest_selected = ref([]);
const v = ref({id: '1', name: 'prueba ',  country: 'pais prueba', book: []})
const items_guests = ref([]);
const selectedItems = ref([]);

const showModalView = ref(false);
const showModalForm = ref(false);
const showModalDelete = ref(false);
const showModalJoinForm = ref(false);
const tittle = ref('');
const operation = ref(1);
const msj = ref('');
const classMsj = ref('hidden');


const openModalView = (sala)=>{
    // showModalView.value = true;
    console.log('openModalView ==> ', sala, sala.code_sala);
    Inertia.visit(route('diagram_class.index'), {
    data: {
        // Aquí pones los datos que deseas enviar
        code_sala: sala.code_sala,
        // Agrega más datos según sea necesario
        },
    });
    // Enviar la solicitud POST solo con el payload
    // axios.get(route('diagram_class.get_room'))
    //     .then(response => {
    //         // ok('Acabas de unirte a una sala con éxito!!!');
    //         // get_salas();
    //     })
    //     .catch(errors => {
    //         console.error(errors);
    //     });
}
const openModalDelete = (sala)=>{
    showModalDelete.value = true;
    v.value = sala;
}
const openModalDeleteGuest = (guest_select)=>{
    guest_selected.value = guest_selected.value.filter(guest => guest.id !== guest_select.id);
}
const openModalForm = (option, sala)=>{
    showModalForm.value = true;
    operation.value = option;
    if(option === 1 ){
        tittle.value = 'Crear Sala';
    }else{
        console.log('al editar sala ', option, sala);
        tittle.value = 'Editar Sala';
        form.name = sala.name;
        form.code_sala = sala.code_sala;
        form.id = sala.id;
        guest_selected.value = sala.guests;
    }
}
const openModalJoinForm = () => {
    showModalJoinForm.value = true;
    tittle.value = 'Unirse a Sala';
}
const closeModalView = ()=>{
    showModalView.value = false;
}
const closeModal = ()=>{
    showModalView.value = false;
    showModalForm.value = false;
    showModalDelete.value = false;
    showModalJoinForm.value = false;
    form.reset();
    guest_selected.value= [];
}
const save = async ()=>{
    if(operation.value == 1){
        console.log('save', 1);
        form.guest_selected = guest_selected.value;
        try {
            await form.post(route('salas.store'));
            ok('Sala creada')
            get_salas();
            // console.log('good ens sala store');
        } catch (error) {
            console.log('Error:', error);
        }
        // form.post(route('salas.store',{
        //     onSuccess: () => {
        //         console.log('good');
        //     },
        //     onError: (errors) => {
        //         console.log('Errors:', errors); // Para ver si hay errores en la respuesta
        //     }
        // }))
    }else{
        form.put(route('salas.update',form.id),{
            onSuccess: () =>{ok('Sala actualizada')}
        });
    }
}
const joinRoom = () => {
    // Crear el objeto que deseas enviar
    const payload = {
        code_sala: form.code_sala, // Añade el dato adicional
    };

    // Enviar la solicitud POST solo con el payload
    axios.post(route('sala.join_room'), payload)
        .then(response => {
            ok('Acabas de unirte a una sala con éxito!!!');
            get_salas();
        })
        .catch(errors => {
            console.error(errors);
        });
};
const get_salas = () => {
    // Enviar la solicitud POST solo con el payload
    axios.post(route('sala.get_room'))
        .then(response => {
            ok('Acabas de unirte a una sala con éxito!!!');
            salas.value = response.data;
        })
        .catch(errors => {
            console.error(errors);
        });
};
const ok = (m)=>{
    console.log('ingresa a ok ');
    closeModal();

    form.reset();
    msj.value = m;
    classMsj.value = 'block';
    setTimeout(() => {
        classMsj.value = 'hidden';
    }, 8000);
}
const updateSelectedOptions=()=>{
    console.log(' ingresa a updateSelectedOptions');
}

const deleteAuthor = () =>{
  form.delete(route('salas.destroy',v.value.id),{
    onSuccess: () => {
        ok('Sala Eliminada !!!');
        get_salas();
    }
  })
}
// Definir el watch para guest_id
const watchGuestId = watch(
  () => form.guest_id, // Acceder a la propiedad reactiva con una función
  (newVal, oldVal) => {
    console.log('El ID del invitado ha cambiado:', newVal, ' antiguo valor => ', oldVal);
    // Aquí puedes realizar las acciones que desees cuando cambie el ID del invitado
    if (newVal) {
        // props.guests.forEach(guest => {
        // console.log('Guest:', guest);  // Asegúrate de que accedes correctamente a cada invitado
        // console.log('ID del invitado:', guest.id);  // Accede al ID del invitado
        // });
        // console.log(' invitadossssssssssss ', props.guests);
        const foundGuest = props.guests.find(guest => guest.id == newVal);
        if (foundGuest) {
            // console.log('Objeto encontrado:', foundGuest, foundGuest.name);  // El invitado con el id buscado
            const existeGuestSelected = guest_selected.value.find(guest => guest.id == newVal);
            if(!existeGuestSelected){
                guest_selected.value.push(foundGuest);
            }
        }
        form.guest_id = '';
    }
  }
);
</script>

<template>
	<Head title="Salas" />

	<AuthenticatedLayout>
		<template #header>
			Salas
            <darkButton @click="openModalForm(1)" v-if="user.type_user == 1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </darkButton>
            <darkButton @click="openModalJoinForm()" v-if="user.type_user == 2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
                </svg>
            </darkButton>
		</template>

		<div class="w-full overflow-hidden rounded-lg border shadow-md">

			<div class="inline-flex overflow-hidden mb-4 w-full bg-white rounded-lg shadow-md" :class="classMsj">
				<div class="flex justify-center items-center w-12 bg-green-500">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
				</div>

				<div class="px-4 py-2 -mx-3">
					<div class="mx-3">
						<span class="font-semibold text-green-500">success</span>
						<p class="text-sm text-gray-600">{{ msj }}</p>
					</div>
				</div>
			</div>
			<div class="w-full overflow-x-auto bg-white">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Codigo</th>
                            <th class="px-4 py-3">Anfitrion</th>
                            <th class="px-4 py-3">Fecha Creación</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y">
                        <tr v-for="sala, index in salas" :key="sala.id" class="text-gray-700">
                            <td class="px-4 py-3 text-sm">
                                {{index + 1}}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{sala.name}}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{sala.code_sala}}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{sala.host_name}}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{sala.created_at}}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <secondaryButton @click="openModalView(sala)" :isButtonDisabled="sala.enable !== 1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                                    </svg>
                                </secondaryButton>
                                <warningButton @click="openModalForm(2, sala)"  v-if="user.type_user == 1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </warningButton>
                                <DarkButton @click="openModalDelete(sala)"  v-if="user.type_user == 1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6" v-if="sala.enable==1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6" v-if="sala.enable==2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                </DarkButton>
                            </td>
                            <!-- <td class="px-4 py-3 text-sm">
                                <warningButton>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </warningButton>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <DarkButton>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </DarkButton>
                            </td> -->
                        </tr>
                    </tbody>
                </table>
            </div>
		</div>
        <Modal :show="showModalView" @close="closeModal">
            <div class="p-6">
                <p>Author: <span class="text-lg font-medium text-gray-900">{{ v.name }}</span></p>
                <p>Country: <span class="text-lg font-medium text-gray-900">{{ v.country }}</span></p>
                Books:
                <ol>
                    <li class="text-lg font-medium text-gray-900" v-for="b,i in v.books" :key="b.id">
                    {{ (i+1)+') '+b.title }}
                    </li>
                </ol>
            </div>
            <div class="m-6 flex justify-end">
                <secondaryButton @click="closeModal">Cancelar</secondaryButton>
            </div>
        </Modal>

        <Modal :show="showModalForm" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">{{ tittle }}</h2>
                <div class="mt-6 mb-6 space-y-6 max-w-xl">
                    <InputGroup :text="'Nombre'" :required="'required'" v-model="form.name" :type="'text'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </InputGroup>
                    <InputError class="mt-1" :message="form.errors.name"></InputError>

                    <InputGroup :text="'Código de Sala'" :required="'required'" v-model="form.code_sala" :type="'text'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002h.375a1.125 1.125 0 0 1 0 2.25H2.99" />
                        </svg>
                    </InputGroup>
                    <InputError class="mt-1" :message="form.errors.code_sala"></InputError>
                    <SelectInput :text="'seleccione invitado'" :required="'required'" v-model="form.guest_id" :options="guests" v-if="operation == 1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                        </svg>
                    </SelectInput>

                    <div class="w-full overflow-hidden rounded-lg border shadow-md">
                        <div class="w-full overflow-x-auto bg-white">
                            <table class="w-full whitespace-no-wrap">
                                <thead>
                                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b">
                                        <th class="px-4 py-3">#</th>
                                        <th class="px-4 py-3">Nombre invitado</th>
                                        <th class="px-4 py-3">correo</th>
                                        <th class="px-4 py-3" v-if="operation==1">opciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y">
                                    <tr v-for="guest, index in guest_selected" :key="guest.id" class="text-gray-700">
                                        <td class="px-4 py-3 text-sm">
                                            {{index + 1}}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            {{guest.name}}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            {{guest.email}}
                                        </td>
                                        <td class="px-4 py-3 text-sm" v-if="operation==1">
                                            <DarkButton @click="openModalDeleteGuest(guest)">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </DarkButton>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <PrimaryButton @click="save">Save</PrimaryButton>
                </div>
            </div>
            <div class="m-6 flex justify-end">
                <secondaryButton @click="closeModal">Cancelar</secondaryButton>
            </div>
        </Modal>
        <Modal :show="showModalJoinForm" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">{{ tittle }}</h2>
                <div class="mt-6 mb-6 space-y-6 max-w-xl">

                    <InputGroup :text="'Ingrese Código de Sala'" :required="'required'" v-model="form.code_sala" :type="'text'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002h.375a1.125 1.125 0 0 1 0 2.25H2.99" />
                        </svg>
                    </InputGroup>
                    <InputError class="mt-1" :message="form.errors.code_sala"></InputError>

                    <PrimaryButton @click="joinRoom">Save</PrimaryButton>
                </div>
            </div>
            <div class="m-6 flex justify-end">
                <secondaryButton @click="closeModal">Cancelar</secondaryButton>
            </div>
        </Modal>
        <Modal :show="showModalDelete" @close="closeModal">
            <div class="p-6">
                <p class="text-2xl text-gray-500" v-if="v.enable==1">
                Estas Seguro de Inactivar a
                <span class="text-2xl font-medium text-gray-900">{{ v.name }}</span> ? </p>
                <p class="text-2xl text-gray-500" v-else>
                Estas Seguro de Activar a
                <span class="text-2xl font-medium text-gray-900">{{ v.name }}</span> ? </p>
                <PrimaryButton @click="deleteAuthor">{{v.enable==1? 'Si, Eliminar' : 'Si, Activar'}}</PrimaryButton>
            </div>
            <div class="m-6 flex justify-end">
                <secondaryButton @click="closeModal">Cancelar</secondaryButton>
            </div>
        </Modal>
	</AuthenticatedLayout>
</template>
