<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Invite;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AppController extends Controller
{
    /**
     * Creation d'un nouveau evenement(mariage)
     * @param Request $request
     * @author Gaston delimond
     * @DateTime 24/01/2024 23:50'
     * @return JsonResponse
    */
    public function createEvent(Request $request):JsonResponse
    {
        try {
            $datas = $request->validate([
                'event_nom'=>'required|string',
                'event_couple_nom'=>'required|string',
                'event_date'=>'required|date',
            ]);
            $datas['event_code'] = $this->buildRandomCode(6);
            $event = Evenement::create($datas);
            if(isset($event)){
                return response()->json([
                    "status"=>"success",
                    "event"=>$event
                ]);
            }
            else{
                return response()->json(['errors' => "Echec de création de l'Evenement !" ]);
            }
        }
        catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors ]);
        }
        catch (\ErrorException $e){
            return response()->json(['errors' => $e->getMessage() ]);
        }
    }




    /**
     * Creation d'une table
     * @param Request $request
     * @author Gaston delimond
     * @DateTime 25/01/2024 00:03'
     * @return JsonResponse
     */
    public function createTable(Request $request):JsonResponse
    {
        try {
            $datas = $request->validate([
                'table_libelle'=>'required|string',
                'table_nbre_chaise'=>'required|int',
                'event_id'=>'required|int|exists:evenements,id',
            ]);

            $lastTable = Table::create($datas);
            if(isset($lastTable)){
                return response()->json([
                    "status"=>"success",
                    "table"=>$lastTable
                ]);
            }
            else{
                return response()->json(['errors' => "Echec de la création de la table" ]);
            }
        }
        catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors ]);
        }
        catch (\ErrorException $e){
            return response()->json(['errors' => $e->getMessage() ]);
        }
    }




    /**
     * Creation d'un invité
     * @param Request $request
     * @author Gaston delimond
     * @DateTime 25/01/2024 00:03'
     * @return JsonResponse
     */
    public function createInvite(Request $request):JsonResponse
    {
        try {
            $datas = $request->validate([
                'invite_nom'=> 'required|string',
                'invite_type'=>'required|string',
                'table_id'=>'required|int|exists:tables,id',
                'event_id'=>'required|int|exists:evenements,id',
            ]);

            $table = Table::findOrFail((int)$datas['table_id']);
            $count = $this->countInvites((int)$datas['table_id']);
            $countTable = $table->table_nbre_chaise - $count;
            if($countTable <= 0 ){
                return response()->json(['errors' => "La table sélectionnée est déjà pleine !" ]);
            }
            $lastInvite = Invite::create($datas);
            $inviteJson = $lastInvite->toJson();
            $qrCode = QrCode::size(200)->generate($inviteJson);
            $lastInvite->invite_qrcode = $qrCode;
            $lastInvite->save();
            if(isset($lastInvite)){
                return response()->json([
                    "status"=>"success",
                    "invite"=>$lastInvite
                ]);
            }
            else{
                return response()->json(['errors' => "Echec de la création de l'invite" ]);
            }
        }
        catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors ]);
        }
        catch (\ErrorException $e){
            return response()->json(['errors' => $e->getMessage() ]);
        }
    }



    /**
     * Compte les invites pour correspondre au nombre des places à une table
     * @author Gaston delimond
     * @DateTime 25/01/2024 00:28'
     * @param int $tableId
     * @return integer $count
    */
    private function countInvites(int $tableId): int
    {
        return Invite::with('table')
            ->where('table_id', $tableId)
            ->count();
    }


    /**
     * generate a alpha numeric code
     * @param int|null $length
     * @author Gaston Delimond
     * @DateTime 25/01/2024 00:02
     * @return string
     */
    private function buildRandomCode(int $length=null) :string{
        $lettreAleatoire = chr(rand(65, 90));
        $chiffresAleatoires = str_pad(rand(0, 9999), $length ?? 5, '0', STR_PAD_LEFT);
        return $lettreAleatoire . $chiffresAleatoires;
    }
}
