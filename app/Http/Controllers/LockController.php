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
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }
        $connection = $request->input("connection");
        $year = $request->input("year");
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $locks = DB::select(
                "SELECT `id`, `seq`, `sy_id`, `is_blocked`, `is_lock_classbased` FROM  lock_sequence WHERE sy_id = $sy_id"
            );
            if (count($locks) > 0) {
                return response()->json($locks, 200);
            } else {
                return response()->json([], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving locks: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function saveOrUpdateLocks(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'data' => 'required|json',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . $th->getMessage(),
            ], 422);
        }

        $connection = $request->input("connection");
        $data = $request->input("data");
        $data_size = $request->input("data_size");
        $year = $request->input("year");

        $lockList = json_decode($data, true);
        config(["database.default" => $connection]);
        $sy_id = MyHelper::getSchoolYearID($year);

        $errMsg = "";
        $allAffected = 1;
        foreach ($lockList as $lockData) {
            $is_blocked = $lockData["is_blocked"];
            $seq = $lockData["seq"];

            try {
                $lockRef = LockSequence::where('sy_id', $sy_id)
                    ->where('seq', $seq)
                    ->first();
                if (is_null($lockRef)) {
                    //Let's create a new lock;
                    $newLock = new LockSequence();
                    $newLock->sy_id = $sy_id;
                    $newLock->seq = $seq;
                    $newLock->is_blocked = $is_blocked;
                    $newLock->save();
                } else {
                    //Update the existing lock
                    $lockRef->is_blocked = $is_blocked;
                    $lockRef->update();
                }
            } catch (Exception $ex) {
                $errMsg = $ex->getMessage();
                $allAffected = 0;
            }
        }
        //echo "$allAffected"; //1--> All Locks successfully modified; 0--> Failed to save at least one
        if ($allAffected == 1) {
            return response()->json([
                'status' => true,
                'message' => 'Locks saved successfully.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to lock at least a sequence: ' . $errMsg,
            ], 500);
        }
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
