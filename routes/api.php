<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\FiliereController;
use App\Http\Controllers\GroupeController;
use App\Http\Controllers\LockController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Products2Controller;
use App\Http\Controllers\SchoolInfoController;
use App\Http\Controllers\SpecialityController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SectionYearController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ThParamController;
use App\Http\Controllers\ClassifiedparamController;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//--------------------- SCHOOL CONFIG
Route::get('/configs/allSchools', [SchoolInfoController::class, 'allSchools']); //THIS API doesn't need Authentication
Route::get('/configs/getSchoolYears', [SchoolInfoController::class, 'getSchoolYears']);

//--------------------- ACCOUNTS
Route::post('/accounts/connect', [AccountController::class, 'login']); //ok - NO NEED FOR AUTHENTICATION. ANYONE CAN LOGIN
Route::post('/accounts/refresh', [AccountController::class, 'refresh']); //ok - NO NEED FOR AUTHENTICATION. ANYONE CAN REFRESH TOKEN. MEANWHILE, THE REFRESH TOKEN IS CHECKED IN THE CONTROLLER. IF IT'S INVALID, IT WILL RETURN 401 UNAUTHORIZED. IF IT'S VALID, IT WILL RETURN A NEW ACCESS TOKEN.


//******* ADMIN ROUTES 
Route::middleware(['jwt.auth', 'role:ADMIN'])->group(function () {
    //==> ADMIN ON SCHOOL CONFIG
    Route::post('/configs/schoolConfigSorU', [SchoolInfoController::class, 'saveSchoolInfo']); //ok. WORKs. Tested on Postman as post request. With Body tab --> The from-data. Logo image uploaded fro
    Route::get('/configs/updateSchoolInfo', [SchoolInfoController::class, 'updateSchoolInfo']); //ok
    Route::post('/configs/upload', [SchoolInfoController::class, 'upload']); //ok. I tested on POstman, Uploaded logo. I choose Body tab then --> form-data then key= image, value=choose file, other keys: connection and year. setting value for each.  

    //==> ADMIN ON ACCOUNTS
    Route::get('/accounts/{connection}', [AccountController::class, 'allAccounts']); //ok

    //==> ADMIN ON FILIERE
    Route::post('/filieres/saveFiliere', [FiliereController::class, 'saveFiliere']); //ok
    Route::post('/filieres/updateFiliere', [FiliereController::class, 'updateFiliere']); //ok
    Route::post('/filieres/updateManyFiliere', [FiliereController::class, 'updateManyFiliere']); //ok
    Route::post('/filieres/deleteManyFiliere', [FiliereController::class, 'deleteManyFiliere']); //ok

    //==> ADMIN ON SPECIALITY
    Route::post('/specialities/saveSpeciality', [SpecialityController::class, 'saveSpeciality']); //ok
    Route::post('/specialities/updateManySpecialities', [SpecialityController::class, 'updateManySpecialities']); //OK
    Route::post('/specialities/deleteManySpecialities', [SpecialityController::class, 'deleteManySpecialities']); //OK



    //==> ADMIN ON CLASSES
    Route::post('/classes/updateClassSettings', [ClasseController::class, 'updateClassSettings']); //OK
    Route::post('/classes/assignVpAClass', [ClasseController::class, 'assignVpAClass']); //OK
    Route::post('/classes/assignClassesToAVp', [ClasseController::class, 'assignClassesToAVp']); //OK - SUPER USEFULL
    Route::delete('/classes/removeALLVpClasses', [ClasseController::class, 'removeALLVpClasses']); //OK
    Route::delete('/classes/removeAClassFromAVp', [ClasseController::class, 'removeAClassFromAVp']); //OK
    Route::get('/classes/allVpClasses', [ClasseController::class, 'allVpClasses']); //OK
    Route::post('/classes/saveClasse', [ClasseController::class, 'saveClasse']); //OK
    Route::post('/classes/updateManyClasses', [ClasseController::class, 'updateManyClasses']); //OK
    Route::post('/classes/saveManyClasses', [ClasseController::class, 'saveManyClasses']); //OK
    Route::post('/classes/deleteManyClasses', [ClasseController::class, 'deleteManyClasses']); //OK
    Route::delete('/classes/deleteClassesOfSectionAndYear', [ClasseController::class, 'deleteClassesOfSectionAndYear']); //OK    
    Route::post('/classes/applyBasculement', [ClasseController::class, 'applyBasculement']); //OK
    Route::delete('/classes/resetBasculement', [ClasseController::class, 'resetBasculement']); //OK | iT removes basculement for all students of a classe. It is different from removeBasculement which removes basculement for some students of a classe(It can also do for all if you specify all students).
    Route::post('/classes/removeBasculement', [ClasseController::class, 'removeBasculement']); //How does it differs from resetBasculement? They are similar but not the same.
    Route::post('/classes/saveChanges', [ClasseController::class, 'saveChanges']);
    Route::post('/classes/saveChangesWithPOST', [ClasseController::class, 'saveChangesWithPOST']);
    Route::get('/classes/basculerSpecial', [ClasseController::class, 'basculerSpecial']);
    Route::get('/classes/processRedoublants', [ClasseController::class, 'processRedoublants']);
    Route::post('/classes/processRedoublantsWithPOST', [ClasseController::class, 'processRedoublantsWithPOST']);
    Route::get('/classes/cancelAllBasculement', [ClasseController::class, 'cancelAllBasculement']);
    Route::get('/classes/clearExclus', [ClasseController::class, 'clearExclus']);
    Route::post('/classes/clearExclusWithPOST', [ClasseController::class, 'clearExclusWithPOST']);
    Route::post('/classes/updateApcLevel', [ClasseController::class, 'updateApcLevel']);
});






//******** ANY CONNECTED USER ROUTES
Route::middleware(['jwt.auth'])->group(function () {
    //==> ON SCHOOL CONFIG
    Route::get('/configs/allSchoolConfig', [SchoolInfoController::class, 'allSchoolConfig']); //ok
    Route::get('/configs/getSchoolYearID', [SchoolInfoController::class, 'getSchoolYearID']); //ok
    Route::get('/configs/getClassificationParam', [SchoolInfoController::class, 'getClassificationParam']); //ok

    //==> ANY CONNECTED USER ON ACCOUNTS
    Route::post('/accounts/updateAccountWithPOST', [AccountController::class, 'updateAccountWithPOST']); //ok. Any user can update its account

    //==> ANY CONNECTED USER ON FILIERE
    Route::get('/filieres/allFilieres', [FiliereController::class, 'allFilieres']); //ok

    //==> ANY CONNECTED USER ON SPECIALITY
    Route::get('/specialities/allSpecialitesOfSection', [SpecialityController::class, 'allSpecialitesOfSection']); //ok
    Route::get('/specialities/allSpecialitesOfYear', [SpecialityController::class, 'allSpecialitesOfYear']); //ok


    //==> ON CLASSES
    Route::get('/classes/allClasse1', [ClasseController::class, 'allClasse1']); //OK 
    Route::get('/classes/getForClasseSize', [ClasseController::class, 'getForClasseSize']); //ok
    Route::get('/classes/getAllClassesOfSection', [ClasseController::class, 'getAllClassesOfSection']); //OK
    Route::get('/classes/getClassesOfSameLevel', [ClasseController::class, 'getClassesOfSameLevel']); //OK
    Route::get('/classes/getClassesOfASuject', [ClasseController::class, 'getClassesOfASuject']); //OK - allClassesOfSubject is better
    Route::get('/classes/getAPCLevels', [ClasseController::class, 'getAPCLevels']); //OK
    Route::get('/classes/allClasse1OfCM', [ClasseController::class, 'allClasse1OfCM']); //OK
    Route::get('/classes/allClassesOfSubject', [ClasseController::class, 'allClassesOfSubject']); //OK - better than getClassesOfASuject
    Route::get('/classes/getAllClassesOfSubject', [ClasseController::class, 'getAllClassesOfSubject']); //OK
    Route::get('/classes/allClasseOfSection', [ClasseController::class, 'allClasseOfSection']); //OK
});



//--------------------- STAFF
Route::get('/modules/staff/arrangeSG', [StaffController::class, 'arrangeSG']);
Route::get('/modules/staff/allClassMastersOfYear', [StaffController::class, 'allClassMastersOfYear']);
Route::get('/modules/staff/allSgOfYear', [StaffController::class, 'allSgOfYear']);
Route::get('/modules/staff/saveStaff', [StaffController::class, 'saveStaff']);
Route::get('/modules/staff/saveManyStaffs', [StaffController::class, 'saveManyStaffs']);
Route::post('/modules/staff/saveManyStaffsWithPOST', [StaffController::class, 'saveManyStaffsWithPOST']);
Route::get('/modules/staff/allStaffs1', [StaffController::class, 'allStaffs1']);
Route::get('/modules/staff/allStaffs2', [StaffController::class, 'allStaffs2']);
Route::get('/modules/staff/updateManyStaffs', [StaffController::class, 'updateManyStaffs']);
Route::get('/modules/staff/deleteManyStaffs', [StaffController::class, 'deleteManyStaffs']);
Route::get('/modules/staff/allTeachingStaffOfYear', [StaffController::class, 'allTeachingStaffOfYear']);
Route::get('/modules/staff/allStaffsOfaSC', [StaffController::class, 'allStaffsOfaSC']);
Route::get('/modules/staff/subjectTaughtByaStaff', [StaffController::class, 'subjectTaughtByaStaff']);
Route::get('/modules/staff/assignACourse', [StaffController::class, 'assignACourse']);
Route::get('/modules/staff/removeACourse', [StaffController::class, 'removeACourse']);
Route::get('/modules/staff/removeALLCourses', [StaffController::class, 'removeALLCourses']);
Route::post('/modules/staff/updateManyStaffsPOST', [StaffController::class, 'updateManyStaffsPOST']);
Route::post('/modules/staff/deleteManyStaffsWithPOST', [StaffController::class, 'deleteManyStaffsWithPOST']);
Route::get('/modules/staff/teachFromAcc', [StaffController::class, 'teachFromAcc']);
Route::get('/modules/staff/AllAttributionsOfSection', [StaffController::class, 'AllAttributionsOfSection']);
Route::get('/modules/staff/subjectTaughtByaStaff2', [StaffController::class, 'subjectTaughtByaStaff2']);
Route::get('/modules/staff/modifyStaff', [StaffController::class, 'modifyStaff']);
Route::get('/modules/staff/batchAssignCourses', [StaffController::class, 'batchAssignCourses']);
Route::get('/modules/staff/batchRemoveCourses', [StaffController::class, 'batchRemoveCourses']);




//--------------------- GROUPE
Route::get('/modules/subjects/allGroupes', [GroupeController::class, 'allGroupes']);
Route::get('/modules/subjects/deleteManyGroupes', [GroupeController::class, 'deleteManyGroupes']);
Route::get('/modules/subjects/saveGroupe', [GroupeController::class, 'saveGroupe']);
Route::get('/modules/subjects/updateManyGroupes', [GroupeController::class, 'updateManyGroupes']);
Route::get('/modules/subjects/groupesOfYearAndSection', [GroupeController::class, 'groupesOfYearAndSection']);

//--------------------- SUBJECTS
Route::get('/modules/subjects/allSubjectOfSectionAndYear', [SubjectController::class, 'allSubjectOfSectionAndYear']);
Route::get('/modules/subjects/saveSubject', [SubjectController::class, 'saveSubject']);
Route::get('/modules/subjects/updateManySubjects', [SubjectController::class, 'updateManySubjects']);
Route::get('/modules/subjects/deleteManySubjects', [SubjectController::class, 'deleteManySubjects']);
Route::get('/modules/subjects/saveManySubjects', [SubjectController::class, 'saveManySubjects']);
Route::get('/modules/subjects/deleteAllSubjectsOfSectionAndYear', [SubjectController::class, 'deleteAllSubjectsOfSectionAndYear']);
Route::get('/modules/subjects/subjectsNotOfClasse', [SubjectController::class, 'subjectsNotOfClasse']);
Route::get('/modules/subjects/subjectOfClasse', [SubjectController::class, 'subjectOfClasse']);
Route::get('/modules/subjects/allSubjectOfClasse', [SubjectController::class, 'allSubjectOfClasse']);
Route::get('/modules/subjects/saveManySC', [SubjectController::class, 'saveManySC']);
Route::get('/modules/subjects/deleteASubjectOfAClasseYearAndSection', [SubjectController::class, 'deleteASubjectOfAClasseYearAndSection']);
Route::get('/modules/subjects/saveCompetence', [SubjectController::class, 'saveCompetence']);
Route::get('/modules/subjects/allCompetences', [SubjectController::class, 'allCompetences']);
Route::get('/modules/subjects/allCompetences1', [SubjectController::class, 'allCompetences1']);
Route::get('/modules/subjects/allCompetences2', [SubjectController::class, 'allCompetences2']);
Route::get('/modules/subjects/updateManyCompetences', [SubjectController::class, 'updateManyCompetences']);
Route::get('/modules/subjects/deleteManyCompetences', [SubjectController::class, 'deleteManyCompetences']);
Route::get('/modules/subjects/calquerCompetences', [SubjectController::class, 'calquerCompetences']);
Route::get('/modules/subjects/calquerCompetencesOfTerm', [SubjectController::class, 'calquerCompetencesOfTerm']);
Route::get('/modules/subjects/calquerSubjects', [SubjectController::class, 'calquerSubjects']);
Route::get('/modules/subjects/calquerSubjects', [SubjectController::class, 'calquerSubjects']);
Route::get('/modules/subjects/subjectOfSection', [SubjectController::class, 'subjectOfSection']);
Route::post('/modules/subjects/deleteManySubjectsWithPOST', [SubjectController::class, 'deleteManySubjectsWithPOST']);
Route::post('/modules/subjects/updateManySubjectsWithPOST', [SubjectController::class, 'updateManySubjectsWithPOST']);
Route::post('/modules/subjects/saveManySubjectsWithPOST', [SubjectController::class, 'saveManySubjectsWithPOST']);
Route::post('/modules/subjects/saveManySCWithPost', [SubjectController::class, 'saveManySCWithPost']);
Route::post('/modules/subjects/saveManyAttricutionsWithPost', [SubjectController::class, 'saveManyAttricutionsWithPost']);
Route::get('/modules/subjects/allCompetencesOfSection', [SubjectController::class, 'allCompetencesOfSection']);
Route::get('/modules/subjects/deleteCompetencesOfAClasse', [SubjectController::class, 'deleteCompetencesOfAClasse']);
Route::get('/modules/subjects/deleteCompetencesWithNoMarks', [SubjectController::class, 'deleteCompetencesWithNoMarks']);
Route::post('/modules/subjects/deleteCompetencesWithNoMarksPOST', [SubjectController::class, 'deleteCompetencesWithNoMarksPOST']);




//--------------------- STUDENT
Route::get('/modules/student/allStudents', [StudentController::class, 'allStudents']);
Route::get('/modules/student/allStudentsOfClasse', [StudentController::class, 'allStudentsOfClasse']);
Route::get('/modules/student/allStudentsOfClasse2', [StudentController::class, 'allStudentsOfClasse2']);
Route::get('/modules/student/updateStudentClasse2PromotionInfo', [StudentController::class, 'updateStudentClasse2PromotionInfo']);
Route::get('/modules/student/updateManyStudents', [StudentController::class, 'updateManyStudents']);
Route::get('/modules/student/deleteManyStudents', [StudentController::class, 'deleteManyStudents']);
Route::get('/modules/student/saveManyStudents', [StudentController::class, 'saveManyStudents']);
Route::get('/modules/student/saveAStudent', [StudentController::class, 'saveAStudent']);
Route::get('/modules/student/updateManyStudents', [StudentController::class, 'updateManyStudents']);
Route::get('/modules/student/allStudClassOfAClasse', [StudentController::class, 'allStudClassOfAClasse']);
Route::get('/modules/student/saveManySeqMarks2', [StudentController::class, 'saveManySeqMarks2']);
Route::get('/modules/student/getSeqMarks', [StudentController::class, 'getSeqMarks']);
Route::get('/modules/student/getSeqMarks2', [StudentController::class, 'getSeqMarks2']);
Route::get('/modules/student/getCompMarks', [StudentController::class, 'getCompMarks']);
Route::get('/modules/student/getCompMarks2', [StudentController::class, 'getCompMarks2']);
Route::get('/modules/student/saveCompSeqMarks', [StudentController::class, 'saveCompSeqMarks']);
Route::get('/modules/student/getCompMarks', [StudentController::class, 'getCompMarks']);
Route::get('/modules/student/copyCompMarks', [StudentController::class, 'copyCompMarks']);
Route::get('/modules/student/copySeqMarks', [StudentController::class, 'copySeqMarks']);
Route::get('/modules/student/copyCompMarks2', [StudentController::class, 'copyCompMarks2']);
Route::get('/modules/student/copySeqMarks2', [StudentController::class, 'copySeqMarks2']);
Route::get('/modules/student/getAllSeqMarksSimple', [StudentController::class, 'getAllSeqMarksSimple']);
Route::get('/modules/student/getAllCompMarksSimple', [StudentController::class, 'getAllCompMarksSimple']);
Route::get('/modules/student/allStudentsForMarks', [StudentController::class, 'allStudentsForMarks']);
Route::get('/modules/student/allStudentsOfClasse3', [StudentController::class, 'allStudentsOfClasse3']);
Route::get('/modules/student/allStudentsOfClasseOfSchool', [StudentController::class, 'allStudentsOfClasseOfSchool']);
Route::get('/modules/student/allStudentSubjectOfTerm', [StudentController::class, 'allStudentSubjectOfTerm']);
Route::get('/modules/student/allStudentCompMarkOfTerm', [StudentController::class, 'allStudentCompMarkOfTerm']);
Route::get('/modules/student/allStudentsOfClasseForAbs', [StudentController::class, 'allStudentsOfClasseForAbs']);
Route::get('/modules/student/saveOrUpdateABS', [StudentController::class, 'saveOrUpdateABS']);
Route::get('/modules/student/getDisciplineOfClasse', [StudentController::class, 'getDisciplineOfClasse']);
Route::get('/modules/student/allStudentSubjectOfTerm2', [StudentController::class, 'allStudentSubjectOfTerm2']);
Route::get('/modules/student/allStudentCompMarkOfTerm2', [StudentController::class, 'allStudentCompMarkOfTerm2']);
Route::get('/modules/student/allStudentSubject', [StudentController::class, 'allStudentSubject']);
Route::get('/modules/student/allStudentCompMark', [StudentController::class, 'allStudentCompMark']);
Route::get('/modules/student/getAllDisciplines', [StudentController::class, 'getAllDisciplines']);
Route::get('/modules/student/getAllDisciplines2', [StudentController::class, 'getAllDisciplines2']);
Route::get('/modules/student/resetPromotionInfo', [StudentController::class, 'resetPromotionInfo']);
Route::get('/modules/student/updatePromotionInfo', [StudentController::class, 'updatePromotionInfo']);

Route::post('/modules/student/saveOrUpdateABSWithPOST2', [StudentController::class, 'saveOrUpdateABSWithPOST2']);
Route::post('/modules/student/saveManySeqMarksWithPOST2', [StudentController::class, 'saveManySeqMarksWithPOST2']);
Route::post('/modules/student/saveCompSeqMarksWithPOST2', [StudentController::class, 'saveCompSeqMarksWithPOST2']);
Route::post('/modules/student/saveManyStudentsWithPOST2', [StudentController::class, 'saveManyStudentsWithPOST2']);
Route::post('/modules/student/deleteManyStudentsWithPOST', [StudentController::class, 'deleteManyStudentsWithPOST']);
Route::post('/modules/student/updatePromotionInfoWithPOST', [StudentController::class, 'updatePromotionInfoWithPOST']);
Route::get('/modules/student/updateDismiss', [StudentController::class, 'updateDismiss']);
Route::get('/modules/student/updateSolvable', [StudentController::class, 'updateSolvable']);
Route::post('/modules/student/updateSolvablePOST', [StudentController::class, 'updateSolvablePOST']);
Route::get('/modules/student/addStudentToRepeatList', [StudentController::class, 'addStudentToRepeatList']);
Route::get('/modules/student/removeStudentFromClass', [StudentController::class, 'removeStudentFromClass']);
Route::get('/modules/student/allStudentsOfClasseOfSection', [StudentController::class, 'allStudentsOfClasseOfSection']);
Route::get('/modules/student/setFatherMother', [StudentController::class, 'setFatherMother']);
Route::get('/modules/student/addStudentToClass', [StudentController::class, 'addStudentToClass']);
Route::get('/modules/student/allStudClassOfYear', [StudentController::class, 'allStudClassOfYear']);
Route::get('/modules/student/uploadSeqMarks', [StudentController::class, 'uploadSeqMarks']);
Route::post('/modules/student/uploadSeqMarksWithPOST', [StudentController::class, 'uploadSeqMarksWithPOST']);
Route::get('/modules/student/uploadCompMarks', [StudentController::class, 'uploadCompMarks']);
Route::post('/modules/student/uploadCompMarksWithPOST', [StudentController::class, 'uploadCompMarksWithPOST']);



//--------------------- SECTION
Route::get('/modules/section/getSections', [SectionYearController::class, 'getSections']);






//--------------------- THPARAM
Route::get('/modules/th/thParamOfYear', [ThParamController::class, 'thParamOfYear']);
Route::get('/modules/th/saveThParam', [ThParamController::class, 'saveThParam']);

//--------------------- CLASSIFIED PARAM
Route::get('/modules/settings/classifiedParamOfYear', [ClassifiedparamController::class, 'classifiedParamOfYear']);
Route::get('/modules/settings/saveClassifiedParamOfYear', [ClassifiedparamController::class, 'saveClassifiedParamOfYear']);


//--------------------- LOCK
Route::get('/modules/lock/locksOfYear', [LockController::class, 'locksOfYear']);
Route::get('/modules/lock/saveOrUpdateLocks', [LockController::class, 'saveOrUpdateLocks']);


//--------------------- Patient
Route::get('/modules/patient/allPatients', [TestController::class, 'allPatients']);
Route::get('/modules/patient/savePatient', [TestController::class, 'savePatient']);




//test API
Route::get('/test', [TestController::class, 'test']);
Route::post('/test/getData', [TestController::class, 'getData']);
Route::get('/test/lockTerms', [TestController::class, 'lockTerms']);
Route::get('/test/addCenseurToClasses', [TestController::class, 'addCenseurToClasses']);
Route::get('/test/updateStudentClasseStructure', [TestController::class, 'updateStudentClasseStructure']);
Route::get('/test/updateClasseYearStructure', [TestController::class, 'updateClasseYearStructure']);
Route::get('/test/add2627', [TestController::class, 'add2627']);
Route::get('/test/prepareNewYear', [TestController::class, 'prepareNewYear']);
Route::get('/test/deleteStudClasse', [TestController::class, 'deleteStudClasse']);
Route::get('/test/deleteManyStudClasse', [TestController::class, 'deleteManyStudClasse']);
Route::post('/test/deleteManyStudClassePOST', [TestController::class, 'deleteManyStudClassePOST']);
Route::get('/test/alterStaff', [TestController::class, 'alterStaff']);
Route::get('/backup/backupDB', [BackupController::class, 'backupDB']);


//--------------------- SCHOOL CONFIG [DONE]
//Route::get('/modules/schoolConfig/allSchools', [SchoolInfoController::class, 'allSchools']); //THIS API doesn't need Authentication
//Route::get('/modules/allSchoolConfig', [SchoolInfoController::class, 'allSchoolConfig']); ALREADY SECURED & TESTES
//Route::post('/schoolConfigSorU', [SchoolInfoController::class, 'saveSchoolInfo']); SECURED NOT TESTED YET 
//Route::get('/modules/schoolConfig/updateSchoolInfo', [SchoolInfoController::class, 'updateSchoolInfo']); //SECURED - tested and working
//Route::get('/modules/schoolConfig/getClassificationParam', [SchoolInfoController::class, 'getClassificationParam']);//SECURED - tested and working
//Route::get('/modules/schoolConfig/getSchoolYearID', [SchoolInfoController::class, 'getSchoolYearID']);//SECURED - tested and working
//Route::post('/configs/upload', [SchoolInfoController::class, 'upload']);//SECURED 



//--------------------- ACCOUNTS [DONE]
// Route::get('/accounts/{connection}', [AccountController::class, 'allAccounts']);
// Route::post('/accounts/connect', [AccountController::class, 'login']);
// Route::post('/accounts/refresh', [AccountController::class, 'refresh']);
// Route::get('/modules/account/updateAccount', [AccountController::class, 'updateAccount']);