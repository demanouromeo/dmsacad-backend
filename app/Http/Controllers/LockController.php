<?php

namespace App\Http\Controllers;

use App\Models\LockSequence;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LockController extends Controller
{
    public function locksOfYear(Request $request)
    {
        $connection = $request->input("connection");
        $year = $request->input("year"); 
        config(["database.default" => $connection]); 
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $locks = DB::select(
                "SELECT * FROM  lock_sequence WHERE sy_id = $sy_id"
            );
            if (count($locks) > 0) {
                return response()->json($locks, 200);
            } else {
                return [];
            }
        } catch (Exception $e) {
            //echo '<br/>ERROR: ' . $e->getMessage();
            return response()->json([], 500); //ERROR OCCURS
        }
    }
    public function saveOrUpdateLocks(Request $request)
    {
        //echo "Starting...\n";
        $connection = $request->input("connection");
        $data = $request->input("data");
        //$data = $request->input("year");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $lockList = json_decode($data, true);
        //$n = count($fList);
        //echo "DATA Lenght = $n [size transmitted is $data_size]";
        $allAffected = 1; //interpreted as true. 0-->false 
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);
        foreach ($lockList as $lockData) {
            $is_blocked = $lockData["is_blocked"];
            $seq = $lockData["seq"];  
             
            try {                 
                $lockRef = LockSequence::where('sy_id', $sy_id)
                -> where('seq', $seq)
                ->first();
                 if(is_null($lockRef)){
                    //Let's create a new lock;
                    $newLock = new LockSequence();
                    $newLock->sy_id = $sy_id;
                    $newLock->seq = $seq;
                    $newLock->is_blocked = $is_blocked;
                    $newLock->save();
                 }else{
                    //Update the existing lock
                    $lockRef->is_blocked = $is_blocked;
                    $lockRef->update();
                 }
                
            } catch (Exception $ex) {
                echo $ex->getMessage();
                $allAffected = 0;
            } 
        }
        echo "$allAffected"; //1--> All Locks successfully modified; 0--> Failed to save at least one
    }

    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(LockSequence $lockSequence)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LockSequence $lockSequence)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LockSequence $lockSequence)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LockSequence $lockSequence)
    {
        //
    }
}
