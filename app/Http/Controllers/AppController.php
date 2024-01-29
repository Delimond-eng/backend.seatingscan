<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Invite;
use App\Models\ScanGuest;
use App\Models\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use \Illuminate\Http\Response;

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
                'event_date_heure'=>'required|date_format:Y-m-d H:i',
            ]);
            $datas['event_code'] = $this->buildRandomCode(4);
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
                'table_id'=>'required|int|exists:tables,id'
            ]);
            //Affichage de données de la table sélectionnée !
            $table = Table::findOrFail((int)$datas['table_id']);
            $datas['event_id'] = $table->event_id;
            $countTable = $this->countTablePlace((int)$datas["table_id"]);
            if($countTable <= 0 ){
                return response()->json(['errors' => "La table sélectionnée est déjà pleine !" ]);
            }
            $lastInvite = Invite::create($datas);
            //$lastInvite->with('table');
            $inviteInfos = Invite::with('table')->find($lastInvite->id);
            $inviteJson = $inviteInfos->toJson();
            $qrCode = $this->generateQRCode($inviteJson);
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
     * Transferer un invité d'une table à un autre table
     * @param Request $request
     * @return JsonResponse
     * @author Gaston delimond
     * @DateTime 26/01/2024 12:26
     */
    public function transfertInvite(Request $request):JsonResponse
    {
        try {
            $datas = $request->validate([
                "invite_id"=>"required|int|exists:invites,id",
                "table_id"=>"required|int|exists:tables,id"
            ]);
            $invite = Invite::with('table')->find((int)$datas['invite_id']);
            $invite['table_id'] = $datas['table_id'];
            $countTable = $this->countTablePlace((int)$datas["table_id"]);
            if($countTable <= 0 ){
                return response()->json(['errors' => "La table sélectionnée est déjà pleine !" ]);
            }
            $done = $invite->save();

            if($done){
                $scanning =ScanGuest::create([
                    "event_id"=>$invite['event_id'],
                    "invite_id"=>$invite['id']
                ]);
                return response()->json([
                    "status"=>"success",
                    "invite"=>$invite,
                    "scan"=>$scanning
                ]);
            }
            else{
                return response()->json(['errors' => "Echec du transfert de l'invité !" ]);
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
     * Affichage de tous les evenements avec tables->invites pour chaque evenement
     * @author Gaston delimond
     * @param int|null $key
     * @return JsonResponse
    */
    public function viewAllEvents(int $key=null):JsonResponse
    {
        //Lorsque l'ID de l'evenement est passé à la requete, on affiche que l'evenement demandé dans la requete
        if(isset($key)){
            $events = Evenement::with('tables.invites')
                ->orderByDesc('id')
                ->where('id', $key)
                ->first();
            return response()->json([
                "status"=>"success",
                "events"=>$events
            ]);
        }
        // On affiche tous les evenements dans une liste
        else{
            $events = Evenement::with('tables.invites')
                ->orderByDesc('id')->get();
            return response()->json([
                "status"=>"success",
                "events"=>$events
            ]);
        }
    }


    /**
     * Se Connecter à un evenement
     * @author Gaston delimond
     * @param Request $request
     * @return JsonResponse
    */
    public function loggedToEvent(Request $request):JsonResponse
    {
        try {
            $data = $request->validate([
                "event_code"=>"required|string"
            ]);
            $result = Evenement::where('event_code', $data['event_code'])->first();
            if(isset($result)){
                return response()->json([
                    "status"=>"success",
                    "event"=>$result
                ]);
            }
            else{
                return response()->json(["errors"=>"Evenement non reconnu !"]);
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
     * Scanner un nouveau invité
     * @param Request $request*
     * @author Gaston delimond
     * @DateTime 27/01/2024 23:28
     * @return JsonResponse
    */
    public function validScanGuest(Request $request):JsonResponse
    {
        try {
            $data = $request->validate([
                "event_id"=>"required|int|exists:evenements,id",
                "invite_id"=>"required|int|unique:scan_guests,invite_id"
            ]);

            $scanning = ScanGuest::create($data);
            $invite = Invite::with('table')->find($scanning->invite_id);
            if (isset($scanning)){
                return response()->json([
                    "status"=>"success",
                    "invite"=>$invite
                ]);
            }
            else{
                return response()->json(["errors"=>"Echec de validation de l'invité"]);
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
     * Test generate PDF
     * @param int|null $eventID
     * @return Response
     */
    public function generatePdfWithQRCodes(?int $eventID ): Response
    {
        $qrcodes = Invite::all()->where("event_id", $eventID);
        $data = ['qrcodes' => $qrcodes];
        $pdf = PDF::loadView('pdf.qrcodes', $data)->setPaper('a4')->setOption('margin-top', 10);
        return $pdf->download('seating_scan_event_'.$eventID.'.pdf');
    }


    /**
     * Generate qrcode simple
     * @param $data
     * @return string
     * @author Gaston delimond
     */
    private function generateQRCode($data):string
    {
        $qrCode = QrCode::size(50)->generate($data);
        return 'data:image/png;base64,' . base64_encode($qrCode);
    }


    /**
     * Count table place
     * @author Gaston Delimond
     * @param int $tableID
     * @return int place
    */
    private function countTablePlace(int $tableID):int
    {
        $table = Table::findOrFail($tableID);
        $count = $this->countInvites($tableID);
        return $table->table_nbre_chaise - $count;
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
        $lettreAleatoire1 = chr(rand(65, 90));
        $lettreAleatoire2 = chr(rand(65, 90));
        $chiffresAleatoires = str_pad(rand(0, 9999), $length ?? 5, '0', STR_PAD_LEFT);
        return $lettreAleatoire1 . $lettreAleatoire2 . $chiffresAleatoires;
    }
}
