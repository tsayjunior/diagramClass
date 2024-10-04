<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{id}.{code_sala}', function ($user, $id, $code_sala) {
    $user_data = User::find($user->id);

    // Agregar logs para verificar los valores
    // \Log::info('Comparando usuario con ID:', ['user_data_id' => $user_data->id]);
    // \Log::info('Comparando con ID del canal:', ['id' => $id]);
    // \Log::info('Comparando código de sala:', ['user_code_sala' => $user_data->code_sala_activate, 'code_sala' => $code_sala]);
    // \Log::info('Comparando sala:', ['user' => $user_data]);

    return (int) $user_data->id === (int) $id && $user_data->code_sala_activate == $code_sala ;
});

Broadcast::channel('diagramComunication.{code_sala}', function ($user, $code_sala) {
    \Log::info(' inicio de comparacion ');

    $user_data = User::find($user->id);

    // Agregar logs para verificar los valores
    \Log::info('Comparando código de sala:', ['user_code_sala' => $user_data->code_sala_activate, 'code_sala' => $code_sala]);
    \Log::info('Comparando sala:', ['user' => $user_data]);

    return $user_data->code_sala_activate == $code_sala ;
});
// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });
