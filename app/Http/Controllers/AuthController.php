<?php

/**
 * @OA\Info(
 *    title="APIs For Thrift Store",
 *    version="1.0.0",
 * ),
 *   @OA\SecurityScheme(
 *       securityScheme="bearerAuth",
 *       in="header",
 *       name="bearerAuth",
 *       type="http",
 *       scheme="bearer",
 *       bearerFormat="JWT",
 *    ),
 */
namespace App\Http\Controllers;

use App\Models\RecintoElectoral;
use App\Models\User;
use App\Models\Persona;
use Canton;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make($request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Existen campos vacios',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function listPCPR(){

     $provincias=DB::table('provincias')
    ->join('cantones', 'provincias.id', '=', 'cantones.provincia_id')
    ->join('parroquias', 'cantones.id', '=', 'parroquias.canton_id')
    ->join('recintoselectorales', 'parroquias.id', '=', 'recintoselectorales.parroquia_id')
    ->select('provincias.provincia', 'cantones.canton', 'parroquias.parroquia', 'recintoselectorales.recinto')
    ->get();
    return response()->json([
        "Listado"=> $provincias,
]);
    }
    public function listaCP(){

        $provincias=DB::table('provincias')
       ->join('cantones', 'provincias.id', '=', 'cantones.provincia_id')
       ->select('cantones.canton','provincias.provincia')
       ->get();
       return response()->json([
           "Listado"=> $provincias,
   ]);
       }
       public function listaPC(){

        $provincias=DB::table('provincias')
        ->join('cantones', 'provincias.id', '=', 'cantones.provincia_id')
        ->select('provincias.provincia','cantones.canton')
        ->get();
        return response()->json([
            "Listado"=> $provincias,
    ]);
       }
       public function UpdateRE(Request $request, $id){

        $request->validate([
           'recinto' => 'required|string',
           'parroquia_id' => 'required',
       ]);


       $recinto = RecintoElectoral::find($id);

       if ($recinto->estado==false) {
           return response()->json(['message' => 'Registro electoral no encontrado'], 404);
       }

       $recinto->update([
           'recinto' => $request->input('recinto'),
           'parroquia_id' => $request->input('parroquia_id'),

       ]);
       return response()->json(['message' => 'Registro electoral actualizado correctamente']);


        }
        public function Deletec(Request $request, $cantonId)
        {
            // Buscar el cantón por su ID
            $canton = Canton::find($cantonId);

            // Verificar si el cantón existe
            if ($canton->estado==false) {
                return response()->json(['message' => 'Cantón no encontrado'], 404);
            }

            // Obtener las parroquias asociadas al cantón
            $parroquias = $canton->parroquias;

            // Eliminar cada parroquia asociada al cantón
            foreach ($parroquias as $parroquia) {
                $parroquias->estado=false;
                $parroquias->save();

            }

            // Devolver una respuesta de éxito
            return response()->json(['message' => 'Parroquias eliminadas correctamente'],200);
        }


}
