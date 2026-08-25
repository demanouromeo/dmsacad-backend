<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\TeacherTimetableMail;

class TimetableController extends Controller
{
    private function validationError(\Throwable $th)
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed: ' . $th->getMessage(),
        ], 422);
    }

    //--------------------------------------------------------------------------------------------
    // TT CONFIG (start time / break durations / period duration) - one row per school, find-or-create
    // by sy_id, same pattern as ClassifiedparamController::saveClassifiedParamOfYear.
    //--------------------------------------------------------------------------------------------

    public function getTtConfig(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $config = DB::select(
                "SELECT tt_config_id, start_time, duration_break1, duration_break2, period_duration,
                        number_of_period_before_break1_start, number_of_period_before_break2_start, sy_id
                 FROM tt_config WHERE sy_id = $sy_id"
            );
            return response()->json($config, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    public function saveTtConfig(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'start_time' => 'required|string|regex:/^\d{2}h\d{2}$/',
                'duration_break1' => 'required|integer|min:0',
                'duration_break2' => 'required|integer|min:0',
                'period_duration' => 'required|integer|min:30|max:60',
                'number_of_period_before_break1_start' => 'required|integer|min:1',
                'number_of_period_before_break2_start' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $existing = DB::select("SELECT tt_config_id FROM tt_config WHERE sy_id = $sy_id");
            $startTime = $request->input('start_time');
            $break1 = $request->input('duration_break1');
            $break2 = $request->input('duration_break2');
            $periodDuration = $request->input('period_duration');
            $beforeBreak1 = $request->input('number_of_period_before_break1_start');
            $beforeBreak2 = $request->input('number_of_period_before_break2_start');

            if (count($existing) > 0) {
                DB::update(
                    "UPDATE tt_config SET start_time = ?, duration_break1 = ?, duration_break2 = ?,
                        period_duration = ?, number_of_period_before_break1_start = ?,
                        number_of_period_before_break2_start = ? WHERE sy_id = ?",
                    [$startTime, $break1, $break2, $periodDuration, $beforeBreak1, $beforeBreak2, $sy_id]
                );
            } else {
                DB::insert(
                    "INSERT INTO tt_config (start_time, duration_break1, duration_break2, period_duration,
                        number_of_period_before_break1_start, number_of_period_before_break2_start, sy_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$startTime, $break1, $break2, $periodDuration, $beforeBreak1, $beforeBreak2, $sy_id]
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Time table configuration saved successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save time table configuration: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // JOURS (school days). NOTE: `label` and `num` each carry a genuine single-column UNIQUE index
    // (confirmed via SHOW CREATE TABLE - not a composite with sy_id), so in practice this table can
    // only ever hold one coherent set of days school-wide, not one set per school year despite the
    // sy_id column/FK. Treated here as the school's single day configuration (sy_id just records which
    // year last touched it) - upsert by `num`, never a plain insert-per-year.
    //--------------------------------------------------------------------------------------------

    public function getJours(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        config(["database.default" => $connection]);
        try {
            $jours = DB::select("SELECT jour_id, label, num, number_of_periods, sy_id FROM jours ORDER BY num ASC");
            return response()->json($jours, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    public function saveJour(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'label' => 'required|string|max:20',
                'num' => 'required|integer|min:1|max:7',
                'number_of_periods' => 'required|integer|min:1|max:12',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $label = $request->input('label');
            $num = $request->input('num');
            $numberOfPeriods = $request->input('number_of_periods');

            $existing = DB::select("SELECT jour_id FROM jours WHERE num = ?", [$num]);
            if (count($existing) > 0) {
                DB::update(
                    "UPDATE jours SET label = ?, number_of_periods = ?, sy_id = ? WHERE num = ?",
                    [$label, $numberOfPeriods, $sy_id, $num]
                );
            } else {
                DB::insert(
                    "INSERT INTO jours (label, num, number_of_periods, sy_id) VALUES (?, ?, ?, ?)",
                    [$label, $num, $numberOfPeriods, $sy_id]
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Day saved successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save day: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteJour(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'jour_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $jour_id = $request->input('jour_id');
        config(["database.default" => $connection]);
        try {
            DB::delete("DELETE FROM classe_period WHERE jour_id = ?", [$jour_id]);
            DB::delete("DELETE FROM jours WHERE jour_id = ?", [$jour_id]);
            return response()->json([
                'status' => true,
                'message' => 'Day deleted successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete day: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // TEACHER WEEKLY LOAD (staff_year.max_periods_per_week)
    //--------------------------------------------------------------------------------------------

    public function getStaffMaxPeriods(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $rows = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname,
                        COALESCE(staff_year.max_periods_per_week, 18) AS max_periods_per_week
                 FROM staff
                 LEFT JOIN staff_year ON staff_year.staff_id = staff.staff_id AND staff_year.sy_id = $sy_id
                 ORDER BY staff.name ASC, staff.surname ASC"
            );
            return response()->json($rows, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    public function updateStaffMaxPeriods(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'staff_id' => 'required|integer|min:1',
                'max_periods_per_week' => 'required|integer|min:1|max:60',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $staff_id = $request->input('staff_id');
            $maxPeriods = $request->input('max_periods_per_week');

            $existing = DB::select(
                "SELECT staff_year_id FROM staff_year WHERE staff_id = ? AND sy_id = ?",
                [$staff_id, $sy_id]
            );
            if (count($existing) > 0) {
                DB::update(
                    "UPDATE staff_year SET max_periods_per_week = ? WHERE staff_id = ? AND sy_id = ?",
                    [$maxPeriods, $staff_id, $sy_id]
                );
            } else {
                DB::insert(
                    "INSERT INTO staff_year (staff_id, sy_id, max_periods_per_week) VALUES (?, ?, ?)",
                    [$staff_id, $sy_id, $maxPeriods]
                );
            }
            return response()->json([
                'status' => true,
                'message' => 'Teacher weekly load saved successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save teacher weekly load: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // SUBJECTS OF A CLASSE - timetable settings (weight / numnber_of_period_per_week / commoncourse)
    // plus the assigned teacher, joined in so this same endpoint also feeds the grid cell editor's
    // subject/teacher dropdowns.
    //--------------------------------------------------------------------------------------------

    public function getClasseSubjectSettings(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        $classe_id = $request->input('classe_id');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $rows = DB::select(
                "SELECT subject_classe.subject_classe_id, subject_classe.subject_id, subject.subject_title,
                        subject_classe.weight, subject_classe.numnber_of_period_per_week, subject_classe.commoncourse,
                        teacher.staff_id AS staff_id, teacher.name AS staff_name, teacher.surname AS staff_surname
                 FROM subject_classe
                 JOIN subject ON subject.subject_id = subject_classe.subject_id
                 LEFT JOIN (
                     SELECT subject_classe_id, MIN(staff_id) AS staff_id
                     FROM subject_classe_staff
                     GROUP BY subject_classe_id
                 ) assigned ON assigned.subject_classe_id = subject_classe.subject_classe_id
                 LEFT JOIN staff teacher ON teacher.staff_id = assigned.staff_id
                 WHERE subject_classe.sy_id = $sy_id AND subject_classe.classe_id = $classe_id
                 ORDER BY subject.subject_title ASC"
            );
            return response()->json($rows, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    public function updateClasseSubjectSetting(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'subject_classe_id' => 'required|integer|min:1',
                'weight' => 'required|integer|min:1|max:5',
                'numnber_of_period_per_week' => 'required|integer|min:0|max:20',
                'commoncourse' => 'required|integer|min:0|max:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        config(["database.default" => $connection]);
        try {
            DB::update(
                "UPDATE subject_classe SET weight = ?, numnber_of_period_per_week = ?, commoncourse = ?
                 WHERE subject_classe_id = ?",
                [
                    $request->input('weight'),
                    $request->input('numnber_of_period_per_week'),
                    $request->input('commoncourse'),
                    $request->input('subject_classe_id'),
                ]
            );
            return response()->json([
                'status' => true,
                'message' => 'Subject time table setting saved successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save subject time table setting: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // GENERATED GRID (read one classe) + MANUAL CELL EDIT
    //--------------------------------------------------------------------------------------------

    public function getClasseCells(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        $classe_id = $request->input('classe_id');
        config(["database.default" => $connection]);
        try {
            $sy_id = MyHelper::getSchoolYearID($year);
            $rows = DB::select(
                "SELECT classe_period.jour_id, classe_period.period_number, classe_period.subject_id,
                        subject.subject_title, classe_period.staff_id, staff.name AS staff_name, staff.surname AS staff_surname
                 FROM classe_period
                 JOIN subject ON subject.subject_id = classe_period.subject_id
                 LEFT JOIN staff ON staff.staff_id = classe_period.staff_id
                 WHERE classe_period.sy_id = $sy_id AND classe_period.classe_id = $classe_id"
            );
            return response()->json($rows, 200);
        } catch (Exception $e) {
            return response()->json([], 500);
        }
    }

    public function updateCell(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
                'classe_id' => 'required|integer|min:1',
                'jour_id' => 'required|integer|min:1',
                'period_number' => 'required|integer|min:1',
                'subject_id' => 'nullable|integer|min:1',
                'staff_id' => 'nullable|integer|min:1',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        $classe_id = $request->input('classe_id');
        $jour_id = $request->input('jour_id');
        $period_number = $request->input('period_number');
        $subject_id = $request->input('subject_id');
        $staff_id = $request->input('staff_id');
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            if (is_null($subject_id)) {
                DB::delete(
                    "DELETE FROM classe_period WHERE sy_id = ? AND classe_id = ? AND jour_id = ? AND period_number = ?",
                    [$sy_id, $classe_id, $jour_id, $period_number]
                );
                return response()->json(['status' => true, 'message' => 'Period cleared successfully.'], 200);
            }

            $assignment = DB::select(
                "SELECT subject_classe_id FROM subject_classe WHERE sy_id = ? AND classe_id = ? AND subject_id = ?",
                [$sy_id, $classe_id, $subject_id]
            );
            if (count($assignment) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'That subject is not assigned to this class for this year.',
                ], 422);
            }

            if (!is_null($staff_id)) {
                $conflict = DB::select(
                    "SELECT classe.classe_name, subject.subject_title
                     FROM classe_period
                     JOIN classe ON classe.classe_id = classe_period.classe_id
                     JOIN subject ON subject.subject_id = classe_period.subject_id
                     WHERE classe_period.sy_id = ? AND classe_period.jour_id = ? AND classe_period.period_number = ?
                       AND classe_period.staff_id = ? AND classe_period.subject_id <> ?
                       AND NOT (classe_period.classe_id = ? AND classe_period.jour_id = ? AND classe_period.period_number = ?)",
                    [$sy_id, $jour_id, $period_number, $staff_id, $subject_id, $classe_id, $jour_id, $period_number]
                );
                if (count($conflict) > 0) {
                    $other = $conflict[0];
                    return response()->json([
                        'status' => false,
                        'message' => 'This teacher is already teaching "' . $other->subject_title . '" in ' . $other->classe_name . ' at this day/period.',
                    ], 409);
                }
            }

            $existing = DB::select(
                "SELECT classe_period_id FROM classe_period WHERE sy_id = ? AND classe_id = ? AND jour_id = ? AND period_number = ?",
                [$sy_id, $classe_id, $jour_id, $period_number]
            );
            if (count($existing) > 0) {
                DB::update(
                    "UPDATE classe_period SET subject_id = ?, staff_id = ? WHERE classe_period_id = ?",
                    [$subject_id, $staff_id, $existing[0]->classe_period_id]
                );
            } else {
                DB::insert(
                    "INSERT INTO classe_period (jour_id, period_number, sy_id, subject_id, staff_id, classe_id)
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [$jour_id, $period_number, $sy_id, $subject_id, $staff_id, $classe_id]
                );
            }
            return response()->json(['status' => true, 'message' => 'Period saved successfully.'], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save period: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // GENERATE - builds the whole school's timetable for one year from scratch.
    //--------------------------------------------------------------------------------------------

    public function generate(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $jours = DB::select("SELECT jour_id, num, number_of_periods FROM jours ORDER BY num ASC");
            if (count($jours) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No school days are configured yet. Configure them in Time table settings first.',
                ], 422);
            }

            $allSlots = [];
            foreach ($jours as $jour) {
                for ($p = 1; $p <= (int)$jour->number_of_periods; $p++) {
                    $allSlots[] = ['jour_id' => (int)$jour->jour_id, 'period_number' => $p];
                }
            }

            $scRows = DB::select(
                "SELECT sc.subject_classe_id, sc.classe_id, sc.subject_id, sc.weight,
                        sc.numnber_of_period_per_week AS periods_per_week, sc.commoncourse, sc.section_id,
                        c.level, c.classe_name, s.subject_title
                 FROM subject_classe sc
                 JOIN classe c ON c.classe_id = sc.classe_id
                 JOIN subject s ON s.subject_id = sc.subject_id
                 WHERE sc.sy_id = $sy_id"
            );

            $teacherRows = DB::select(
                "SELECT scs.subject_classe_id, MIN(scs.staff_id) AS staff_id
                 FROM subject_classe_staff scs
                 JOIN subject_classe sc2 ON sc2.subject_classe_id = scs.subject_classe_id
                 WHERE sc2.sy_id = $sy_id
                 GROUP BY scs.subject_classe_id"
            );
            $teacherOf = [];
            foreach ($teacherRows as $t) {
                $teacherOf[$t->subject_classe_id] = (int)$t->staff_id;
            }

            $maxPeriodsRows = DB::select("SELECT staff_id, max_periods_per_week FROM staff_year WHERE sy_id = $sy_id");
            $maxPeriodsOf = [];
            foreach ($maxPeriodsRows as $m) {
                $maxPeriodsOf[(int)$m->staff_id] = (int)$m->max_periods_per_week;
            }

            $usedSlot = [];       // [classe_id][ "jour-period" ] = true
            $teacherBusy = [];    // [staff_id][ "jour-period" ] = subject_id
            $teacherLoad = [];    // [staff_id] = count
            $subjectDayCount = []; // [classe_id][subject_id][jour_id] = count

            $slotKey = function ($jour_id, $period_number) {
                return $jour_id . '-' . $period_number;
            };

            $maxOf = function ($staff_id) use ($maxPeriodsOf) {
                return $maxPeriodsOf[$staff_id] ?? 18;
            };

            $unassignedWarnings = []; // key = classe_id-subject_id
            $noCapacityWarnings = []; // key = classe_id-subject_id

            $recordUnassigned = function ($row) use (&$unassignedWarnings) {
                $key = $row->classe_id . '-' . $row->subject_id;
                if (!isset($unassignedWarnings[$key])) {
                    $unassignedWarnings[$key] = [
                        'classe_name' => $row->classe_name,
                        'subject_title' => $row->subject_title,
                        'count' => 0,
                    ];
                }
                $unassignedWarnings[$key]['count']++;
            };

            $recordNoCapacity = function ($row) use (&$noCapacityWarnings) {
                $key = $row->classe_id . '-' . $row->subject_id;
                if (!isset($noCapacityWarnings[$key])) {
                    $noCapacityWarnings[$key] = [
                        'classe_name' => $row->classe_name,
                        'subject_title' => $row->subject_title,
                        'count' => 0,
                    ];
                }
                $noCapacityWarnings[$key]['count']++;
            };

            $insertRows = []; // rows to bulk insert at the end: [jour_id, period_number, sy_id, subject_id, staff_id, classe_id]

            $place = function ($row, $jour_id, $period_number, $staffId) use (&$insertRows, &$usedSlot, &$subjectDayCount, $sy_id, $slotKey) {
                $insertRows[] = [$jour_id, $period_number, $sy_id, $row->subject_id, $staffId, $row->classe_id];
                $usedSlot[$row->classe_id][$slotKey($jour_id, $period_number)] = true;
                $subjectDayCount[$row->classe_id][$row->subject_id][$jour_id] =
                    ($subjectDayCount[$row->classe_id][$row->subject_id][$jour_id] ?? 0) + 1;
            };

            // ---------- PHASE A: combined ("commoncourse") groups ----------
            $groups = [];
            foreach ($scRows as $row) {
                if ((int)$row->commoncourse === 1) {
                    $groupKey = $row->level . '_' . $row->subject_id . '_' . $row->section_id;
                    $groups[$groupKey][] = $row;
                }
            }

            $leftover = []; // subject_classe_id => remaining periods_per_week after phase A
            foreach ($scRows as $row) {
                $leftover[$row->subject_classe_id] = (int)$row->periods_per_week;
            }

            foreach ($groups as $groupRows) {
                $sessionCount = min(array_map(fn($r) => (int)$r->periods_per_week, $groupRows));
                if ($sessionCount <= 0) {
                    continue;
                }
                $memberClasseIds = array_unique(array_map(fn($r) => $r->classe_id, $groupRows));
                $distinctTeacherIds = array_values(array_unique(array_filter(
                    array_map(fn($r) => $teacherOf[$r->subject_classe_id] ?? null, $groupRows)
                )));

                $placedSessions = 0;
                foreach ($allSlots as $slot) {
                    if ($placedSessions >= $sessionCount) {
                        break;
                    }
                    $key = $slotKey($slot['jour_id'], $slot['period_number']);

                    $freeForAll = true;
                    foreach ($memberClasseIds as $cid) {
                        if (isset($usedSlot[$cid][$key])) {
                            $freeForAll = false;
                            break;
                        }
                    }
                    if (!$freeForAll) {
                        continue;
                    }

                    $teachersFree = true;
                    foreach ($distinctTeacherIds as $tid) {
                        if (isset($teacherBusy[$tid][$key])) {
                            $teachersFree = false;
                            break;
                        }
                    }

                    $useTeachers = $teachersFree;
                    if ($useTeachers) {
                        foreach ($distinctTeacherIds as $tid) {
                            if (($teacherLoad[$tid] ?? 0) >= $maxOf($tid)) {
                                $useTeachers = false;
                                break;
                            }
                        }
                    }

                    foreach ($groupRows as $r) {
                        $staffForRow = null;
                        if ($useTeachers && isset($teacherOf[$r->subject_classe_id])) {
                            $staffForRow = $teacherOf[$r->subject_classe_id];
                        }
                        if (is_null($staffForRow)) {
                            $recordUnassigned($r);
                        }
                        $place($r, $slot['jour_id'], $slot['period_number'], $staffForRow);
                        $leftover[$r->subject_classe_id]--;
                    }
                    if ($useTeachers) {
                        foreach ($distinctTeacherIds as $tid) {
                            $teacherBusy[$tid][$key] = $groupRows[0]->subject_id;
                            $teacherLoad[$tid] = ($teacherLoad[$tid] ?? 0) + 1;
                        }
                    }
                    $placedSessions++;
                }
            }

            // ---------- PHASE B: independent placement, weight DESC ----------
            usort($scRows, fn($a, $b) => $b->weight <=> $a->weight);

            foreach ($scRows as $row) {
                $remaining = $leftover[$row->subject_classe_id] ?? 0;
                $teacherId = $teacherOf[$row->subject_classe_id] ?? null;

                for ($i = 0; $i < $remaining; $i++) {
                    $free = [];
                    foreach ($allSlots as $slot) {
                        $key = $slotKey($slot['jour_id'], $slot['period_number']);
                        if (!isset($usedSlot[$row->classe_id][$key])) {
                            $free[] = $slot;
                        }
                    }
                    if (count($free) === 0) {
                        $recordNoCapacity($row);
                        continue;
                    }

                    usort($free, function ($a, $b) use ($row, $subjectDayCount) {
                        $aRepeat = $subjectDayCount[$row->classe_id][$row->subject_id][$a['jour_id']] ?? 0;
                        $bRepeat = $subjectDayCount[$row->classe_id][$row->subject_id][$b['jour_id']] ?? 0;
                        if ($aRepeat !== $bRepeat) {
                            return $aRepeat <=> $bRepeat;
                        }
                        return $a['period_number'] <=> $b['period_number'];
                    });

                    $chosen = null;
                    $chosenStaff = null;
                    if (is_null($teacherId)) {
                        $chosen = $free[0];
                    } else {
                        foreach ($free as $slot) {
                            $key = $slotKey($slot['jour_id'], $slot['period_number']);
                            $busy = isset($teacherBusy[$teacherId][$key]) && $teacherBusy[$teacherId][$key] !== $row->subject_id;
                            $overCap = ($teacherLoad[$teacherId] ?? 0) >= $maxOf($teacherId);
                            if (!$busy && !$overCap) {
                                $chosen = $slot;
                                $chosenStaff = $teacherId;
                                break;
                            }
                        }
                        if (is_null($chosen)) {
                            $chosen = $free[0];
                            $recordUnassigned($row);
                        }
                    }

                    $place($row, $chosen['jour_id'], $chosen['period_number'], $chosenStaff);
                    if (!is_null($chosenStaff)) {
                        $key = $slotKey($chosen['jour_id'], $chosen['period_number']);
                        $teacherBusy[$chosenStaff][$key] = $row->subject_id;
                        $teacherLoad[$chosenStaff] = ($teacherLoad[$chosenStaff] ?? 0) + 1;
                    }
                }
            }

            DB::beginTransaction();
            DB::delete("DELETE FROM classe_period WHERE sy_id = ?", [$sy_id]);
            foreach ($insertRows as $r) {
                DB::insert(
                    "INSERT INTO classe_period (jour_id, period_number, sy_id, subject_id, staff_id, classe_id)
                     VALUES (?, ?, ?, ?, ?, ?)",
                    $r
                );
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Time table generated successfully for every class.',
                'warnings' => [
                    'unassignedTeacher' => array_values($unassignedWarnings),
                    'noCapacity' => array_values($noCapacityWarnings),
                ],
            ], 200);
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate time table: ' . $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------------------------------------------------
    // SEND EACH TEACHER THEIR INDIVIDUAL TIME TABLE BY EMAIL - reads the already-generated
    // classe_period rows (never generates anything itself), groups them per teacher, and emails
    // each one their own weekly grid. A teacher with no email on their account, or whose send fails
    // (bad SMTP config, invalid address, ...), is skipped and reported back rather than aborting the
    // whole batch - one bad address must not block everyone else's email.
    //--------------------------------------------------------------------------------------------

    // Mirrors src/utils/timetableTime.ts's computeDayTimeline() (period start/end only, breaks just
    // shift the running clock) so the emailed grid's times match what the admin sees on screen.
    private function computePeriodTimes($ttConfig, int $maxPeriods): array
    {
        $times = [];
        if (is_null($ttConfig) || $maxPeriods <= 0) {
            return $times;
        }
        if (!preg_match('/^(\d{2})h(\d{2})$/', $ttConfig->start_time, $m)) {
            return $times;
        }
        $t = ((int)$m[1]) * 60 + (int)$m[2];
        $format = function (int $minutes) {
            $h = intdiv($minutes, 60) % 24;
            $mi = $minutes % 60;
            return sprintf('%02dH%02d', $h, $mi);
        };
        for ($p = 1; $p <= $maxPeriods; $p++) {
            $start = $t;
            $t += (int)$ttConfig->period_duration;
            $times[$p] = ['start' => $format($start), 'end' => $format($t)];
            if ($p === (int)$ttConfig->number_of_period_before_break1_start) {
                $t += (int)$ttConfig->duration_break1;
            } elseif ($p - (int)$ttConfig->number_of_period_before_break1_start === (int)$ttConfig->number_of_period_before_break2_start) {
                $t += (int)$ttConfig->duration_break2;
            }
        }
        return $times;
    }

    public function sendTeacherEmails(Request $request)
    {
        try {
            $request->validate([
                'connection' => 'required|string',
                'year' => 'required|string',
            ]);
        } catch (\Throwable $th) {
            return $this->validationError($th);
        }
        $connection = $request->input('connection');
        $year = $request->input('year');
        config(["database.default" => $connection]);

        try {
            $sy_id = MyHelper::getSchoolYearID($year);

            $cellRows = DB::select(
                "SELECT classe_period.jour_id, classe_period.period_number, classe_period.staff_id,
                        subject.subject_title, classe.classe_name
                 FROM classe_period
                 JOIN subject ON subject.subject_id = classe_period.subject_id
                 JOIN classe ON classe.classe_id = classe_period.classe_id
                 WHERE classe_period.sy_id = ? AND classe_period.staff_id IS NOT NULL",
                [$sy_id]
            );

            if (count($cellRows) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No time table has been generated yet for this year.',
                ], 422);
            }

            $jours = DB::select("SELECT jour_id, label, num, number_of_periods FROM jours ORDER BY num ASC");
            $maxPeriods = 0;
            foreach ($jours as $j) {
                $maxPeriods = max($maxPeriods, (int)$j->number_of_periods);
            }

            $configRows = DB::select(
                "SELECT start_time, duration_break1, duration_break2, period_duration,
                        number_of_period_before_break1_start, number_of_period_before_break2_start
                 FROM tt_config WHERE sy_id = ?",
                [$sy_id]
            );
            $timeOf = $this->computePeriodTimes(count($configRows) > 0 ? $configRows[0] : null, $maxPeriods);

            $byStaff = []; // [staff_id][ "jour_id-period_number" ] = ['subject_title'=>, 'classe_name'=>]
            foreach ($cellRows as $row) {
                $byStaff[$row->staff_id][$row->jour_id . '-' . $row->period_number] = [
                    'subject_title' => $row->subject_title,
                    'classe_name' => $row->classe_name,
                ];
            }

            $staffIds = array_keys($byStaff);
            $placeholders = implode(',', array_fill(0, count($staffIds), '?'));
            $teachers = DB::select(
                "SELECT staff.staff_id, staff.name, staff.surname, account.email
                 FROM staff
                 JOIN account ON account.acc_id = staff.acc_id
                 WHERE staff.staff_id IN ($placeholders)",
                $staffIds
            );

            $days = [];
            foreach ($jours as $j) {
                $days[] = [
                    'jour_id' => (int)$j->jour_id,
                    'label' => $j->label,
                    'number_of_periods' => (int)$j->number_of_periods,
                ];
            }
            $periods = [];
            for ($p = 1; $p <= $maxPeriods; $p++) {
                $periods[] = [
                    'number' => $p,
                    'start' => $timeOf[$p]['start'] ?? null,
                    'end' => $timeOf[$p]['end'] ?? null,
                ];
            }

            $sentCount = 0;
            $noEmail = [];
            $sendFailed = [];

            foreach ($teachers as $teacher) {
                $teacherName = trim($teacher->name . ' ' . $teacher->surname);
                if (empty($teacher->email)) {
                    $noEmail[] = ['staff_name' => $teacherName];
                    continue;
                }

                $grid = [];
                foreach ($periods as $period) {
                    foreach ($days as $day) {
                        $key = $day['jour_id'] . '-' . $period['number'];
                        if (isset($byStaff[$teacher->staff_id][$key])) {
                            $grid[$period['number']][$day['jour_id']] = $byStaff[$teacher->staff_id][$key];
                        }
                    }
                }

                try {
                    Mail::to($teacher->email)->send(
                        new TeacherTimetableMail($teacherName, $year, $days, $periods, $grid)
                    );
                    $sentCount++;
                } catch (\Throwable $e) {
                    $sendFailed[] = ['staff_name' => $teacherName];
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Time table emails sent.',
                'sentCount' => $sentCount,
                'warnings' => [
                    'noEmail' => $noEmail,
                    'sendFailed' => $sendFailed,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send time table emails: ' . $e->getMessage(),
            ], 500);
        }
    }
}
