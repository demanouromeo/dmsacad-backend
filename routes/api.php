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
Route::post('/accounts/connect', [AccountController::class, 'connect']); //ok - NO NEED FOR AUTHENTICATION. ANYONE CAN LOGIN
Route::post('/accounts/refresh', [AccountController::class, 'refresh']); //ok - NO NEED FOR AUTHENTICATION. ANYONE CAN REFRESH TOKEN. MEANWHILE, THE REFRESH TOKEN IS CHECKED IN THE CONTROLLER. IF IT'S INVALID, IT WILL RETURN 401 UNAUTHORIZED. IF IT'S VALID, IT WILL RETURN A NEW ACCESS TOKEN.
Route::post('/accounts/logout', [AccountController::class, 'logout']);

//******* ADMIN ROUTES 
Route::middleware(['jwt.auth', 'role:ADMIN'])->group(function () {
    //==> ADMIN ON SCHOOL CONFIG
    Route::post('/configs/schoolConfigSorU', [SchoolInfoController::class, 'saveSchoolInfo']); //ok. WORKs. Tested on Postman as post request. With Body tab --> The from-data. Logo image uploaded fro
    Route::get('/configs/updateSchoolInfo', [SchoolInfoController::class, 'updateSchoolInfo']); //ok
    Route::post('/configs/upload', [SchoolInfoController::class, 'upload']); //ok. I tested on POstman, Uploaded logo. I choose Body tab then --> form-data then key= image, value=choose file, other keys: connection and year. setting value for each.  

    //==> ADMIN ON ACCOUNTS
    // NOTE: these two literal-path routes must stay registered before the /accounts/{connection}
    // wildcard below, or Laravel's registration-order route matching would swallow them into
    // allAccounts($connection = "allAdministrateurAccounts") - same gotcha as the old GET
    // /accounts/connect route documented on the frontend's connect() call site.
    Route::get('/accounts/allAdministrateurAccounts', [AccountController::class, 'allAdministrateurAccounts']);
    Route::post('/accounts/adminUpdateAccount', [AccountController::class, 'adminUpdateAccount']);
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
    Route::get('/classes/basculerSpecial', [ClasseController::class, 'basculerSpecial']); //OK
    Route::post('/classes/processRedoublants', [ClasseController::class, 'processRedoublants']); //OK
    Route::post('/classes/saveChanges', [ClasseController::class, 'saveChanges']); //OK !=applyBasculement utilisé pour enregistrer les modifications: lors du basculement
    Route::delete('/classes/cancelAllBasculement', [ClasseController::class, 'cancelAllBasculement']); //OK
    Route::post('/classes/clearExclus', [ClasseController::class, 'clearExclus']); //OK // Take 'data' from request body and 'next_year' then delete all student_classe of next_year that contain students in 'data'
    Route::post('/classes/updateApcLevel', [ClasseController::class, 'updateApcLevel']); //OK. here activated in request body can be 0(true) or 1(false).


    //==> ADMIN ON STAFF
    Route::post('/staffs/saveStaff', [StaffController::class, 'saveStaff']); //OK
    Route::post('/staffs/saveManyStaffs', [StaffController::class, 'saveManyStaffs']); //OK
    Route::post('/staffs/modifyStaff', [StaffController::class, 'modifyStaff']); //OK - TO BE REVIEWED LATE TO ENABLE UPDATE all STAFF INFO. So that it could be used on client side to update staff profil/details.
    Route::post('/staffs/updateManyStaffs', [StaffController::class, 'updateManyStaffs']); //OK
    Route::post('/staffs/deleteManyStaffs', [StaffController::class, 'deleteManyStaffs']); //OK
    Route::post('/staffs/assignACourse', [StaffController::class, 'assignACourse']); //OK
    Route::delete('/staffs/removeACourse', [StaffController::class, 'removeACourse']); //OK
    Route::delete('/staffs/removeALLCourses', [StaffController::class, 'removeALLCourses']); //OK    
    Route::post('/staffs/batchAssignCourses', [StaffController::class, 'batchAssignCourses']); //OK
    Route::post('/staffs/batchRemoveCourses', [StaffController::class, 'batchRemoveCourses']);
    Route::get('/staffs/arrangeSG', [StaffController::class, 'arrangeSG']); //OK - WORKs on REMOTE SERVER. NOT ON LOCAL XAMPP SERVER
    Route::get('/staffs/arrangeSGSimple', [StaffController::class, 'arrangeSGSimple']); //OK - WORKs on REMOTE SERVER. NOT ON LOCAL XAMPP SERVER
    Route::post('/staffs/uploadStaffPhoto', [StaffController::class, 'uploadStaffPhoto']);

    //==> ADMIN ON THPARAM
    Route::post('/th/saveThParam', [ThParamController::class, 'saveThParam']); //OK

    //==> ADMIN ON CLASSIFIEDPARAM
    Route::post('/settings/saveClassifiedParamOfYear', [ClassifiedparamController::class, 'saveClassifiedParamOfYear']); //OK

    //==> ADMIN ON LOCK
    Route::post('/lock/saveOrUpdateLocks', [LockController::class, 'saveOrUpdateLocks']);

    //==> ADMIN ON GROUPE
    Route::post('/groupes/deleteAGroupes', [GroupeController::class, 'deleteAGroupes']); //OK
    Route::post('/groupes/deleteManyGroupes', [GroupeController::class, 'deleteManyGroupes']); //OK
    Route::post('/groupes/saveGroupe', [GroupeController::class, 'saveGroupe']); //OK| but in DB the groupe_name is unique. So it is not possible to save group with same as one even in a differnt section
    Route::post('/groupes/updateManyGroupes', [GroupeController::class, 'updateManyGroupes']);

    //==> ADMIN ON SUBJECT
    Route::post('/subjects/saveSubject', [SubjectController::class, 'saveSubject']); //OK
    Route::post('/subjects/saveManySubjects', [SubjectController::class, 'saveManySubjects']); //OK
    Route::post('/subjects/saveManySC', [SubjectController::class, 'saveManySC']); //OK    
    Route::post('/subjects/updateSubject', [SubjectController::class, 'updateSubject']); //OK
    Route::post('/subjects/updateManySubjects', [SubjectController::class, 'updateManySubjects']); //OK
    Route::delete('/subjects/deleteASubject', [SubjectController::class, 'deleteASubject']); //OK
    Route::post('/subjects/deleteManySubjects', [SubjectController::class, 'deleteManySubjects']); //OK

    Route::delete('/subjects/deleteAllSubjectsOfSectionAndYear', [SubjectController::class, 'deleteAllSubjectsOfSectionAndYear']); //OK
    Route::delete('/subjects/deleteASubjectOfAClasseYearAndSection', [SubjectController::class, 'deleteASubjectOfAClasseYearAndSection']); //OK
    Route::post('/subjects/saveCompetence', [SubjectController::class, 'saveCompetence']); //OK
    Route::post('/subjects/updateManyCompetences', [SubjectController::class, 'updateManyCompetences']); //OK 
    Route::post('/subjects/updateACompetence', [SubjectController::class, 'updateACompetence']); //OK
    Route::post('/subjects/deleteManyCompetences', [SubjectController::class, 'deleteManyCompetences']); //OK
    Route::delete('/subjects/deleteACompetence', [SubjectController::class, 'deleteACompetence']); //OK
    Route::post('/subjects/calquerCompetences', [SubjectController::class, 'calquerCompetences']); //OK
    Route::post('/subjects/calquerCompetencesOfTerm', [SubjectController::class, 'calquerCompetencesOfTerm']); //OK
    Route::get('/subjects/calquerSubjects', [SubjectController::class, 'calquerSubjects']); //OK
    Route::post('/subjects/saveManyAttributions', [SubjectController::class, 'saveManyAttributions']); // MAY BE SIMILAR TO asignCoure of /staffs I'll check later
    Route::delete('/subjects/deleteCompetencesOfAClasse', [SubjectController::class, 'deleteCompetencesOfAClasse']); //OK
    Route::delete('/subjects/deleteCompetencesWithNoMarks', [SubjectController::class, 'deleteCompetencesWithNoMarks']);


    //==> ADMIN ON STUDENT
    Route::get('/students/updateStudentClasse2PromotionInfo', [StudentController::class, 'updateStudentClasse2PromotionInfo']);
    Route::post('/students/deleteStudents', [StudentController::class, 'deleteStudents']);
    Route::post('/students/saveStudents', [StudentController::class, 'saveStudents']);
    Route::post('/students/saveAStudent', [StudentController::class, 'saveAStudent']);
    Route::post('/students/updateStudents', [StudentController::class, 'updateStudents']);
    Route::post('/students/saveSeqMarks', [StudentController::class, 'saveSeqMarks']);
    Route::post('/students/saveCompMarks', [StudentController::class, 'saveCompMarks']);
    Route::get('/students/copyCompMarks', [StudentController::class, 'copyCompMarks']);
    Route::get('/students/copySeqMarks', [StudentController::class, 'copySeqMarks']);
    Route::get('/students/copyCompMarks2', [StudentController::class, 'copyCompMarks2']);
    Route::get('/students/copySeqMarks2', [StudentController::class, 'copySeqMarks2']);
    Route::post('/students/saveOrUpdateABS', [StudentController::class, 'saveOrUpdateABS']);
    Route::get('/students/resetPromotionInfo', [StudentController::class, 'resetPromotionInfo']);
    Route::post('/students/updatePromotionInfo', [StudentController::class, 'updatePromotionInfo']);
    Route::post('/students/updateDismiss', [StudentController::class, 'updateDismiss']);
    Route::post('/students/updateSolvable', [StudentController::class, 'updateSolvable']);
    Route::get('/students/addStudentToRepeatList', [StudentController::class, 'addStudentToRepeatList']);
    Route::get('/students/removeStudentFromClass', [StudentController::class, 'removeStudentFromClass']);
    Route::post('/students/setFatherMother', [StudentController::class, 'setFatherMother']);
    Route::get('/students/addStudentToClass', [StudentController::class, 'addStudentToClass']);
    Route::post('/students/uploadStudentPhoto', [StudentController::class, 'uploadStudentPhoto']);
});
//===================================================================== END ADMIN ROUTES =====================================================================================================





//******** ANY CONNECTED USER ROUTES
Route::middleware(['jwt.auth'])->group(function () {
    //==> ON SCHOOL CONFIG
    Route::get('/configs/allSchoolConfig', [SchoolInfoController::class, 'allSchoolConfig']); //ok
    Route::get('/configs/getSchoolYearID', [SchoolInfoController::class, 'getSchoolYearID']); //ok
    Route::get('/configs/getClassificationParam', [SchoolInfoController::class, 'getClassificationParam']); //ok
    Route::get('/configs/allSchoolConfigOfYear', [SchoolInfoController::class, 'allSchoolConfigOfYear']);
    Route::get('/configs/schoolLogo', [SchoolInfoController::class, 'schoolLogo']);

    //==> ANY CONNECTED USER ON ACCOUNTS
    Route::post('/accounts/updateAccount', [AccountController::class, 'updateAccount']); //ok. Any user can update its account

    //==> ANY CONNECTED USER ON FILIERE
    Route::get('/filieres/allFilieres', [FiliereController::class, 'allFilieres']); //ok

    //==> ANY CONNECTED USER ON SPECIALITY
    Route::get('/specialities/allSpecialitesOfSection', [SpecialityController::class, 'allSpecialitesOfSection']); //ok
    Route::get('/specialities/allSpecialitesOfYear', [SpecialityController::class, 'allSpecialitesOfYear']); //ok


    //==> ANY CONNECTED USER ON CLASSES
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


    //==> ANY CONNECTED USER ON STAFF
    Route::get('/staffs/allClassMastersOfYear', [StaffController::class, 'allClassMastersOfYear']); //OK
    Route::get('/staffs/allSgOfYear', [StaffController::class, 'allSgOfYear']); //OK
    Route::get('/staffs/allStaffs1', [StaffController::class, 'allStaffs1']); //OK
    Route::get('/staffs/allStaffs2', [StaffController::class, 'allStaffs2']); //OK
    Route::get('/staffs/allTeachingStaffOfYear', [StaffController::class, 'allTeachingStaffOfYear']); //OK
    Route::get('/staffs/allStaffsOfaSC', [StaffController::class, 'allStaffsOfaSC']); //OK. Important List teachers teaching a subject in a class
    Route::get('/staffs/subjectTaughtByaStaff', [StaffController::class, 'subjectTaughtByaStaff']); //OK
    Route::get('/staffs/teachFromAcc', [StaffController::class, 'teachFromAcc']); //OK
    Route::get('/staffs/AllAttributionsOfSection', [StaffController::class, 'AllAttributionsOfSection']); //ok
    Route::get('/staffs/subjectTaughtByaStaff2', [StaffController::class, 'subjectTaughtByaStaff2']); //OK
    Route::get('/staffs/staffPhoto', [StaffController::class, 'staffPhoto']);

    //==> ANY CONNECTED USER ON SECTION
    Route::get('/section/getSections', [SectionYearController::class, 'getSections']); //OK

    //==> ANY CONNECTED USER ON THPARAM 
    Route::get('/th/thParamOfYear', [ThParamController::class, 'thParamOfYear']); //OK

    //==> ANY CONNECTED USER ON CLASSIFIEDPARAM
    Route::get('/settings/classifiedParamOfYear', [ClassifiedparamController::class, 'classifiedParamOfYear']); //OK

    //==> ANY CONNECTED USER ON LOCK
    Route::get('/lock/locksOfYear', [LockController::class, 'locksOfYear']);

    //==> ANY CONNECTED USER ON GROUPE 
    Route::get('/groupes/allGroupes', [GroupeController::class, 'allGroupes']); //OK
    Route::get('/groupes/groupesOfYearAndSection', [GroupeController::class, 'groupesOfYearAndSection']); //OK    

    //==> ANY CONNECTED USER ON SUBJECT
    Route::get('/subjects/allSubjectOfSectionAndYear', [SubjectController::class, 'allSubjectOfSectionAndYear']); //OK 
    Route::get('/subjects/allCompetencesOfSection', [SubjectController::class, 'allCompetencesOfSection']); //OK
    Route::get('/subjects/subjectsNotOfClasse', [SubjectController::class, 'subjectsNotOfClasse']); //OK
    Route::get('/subjects/subjectOfClasse', [SubjectController::class, 'subjectOfClasse']); //OK
    Route::get('/subjects/allSubjectOfClasse', [SubjectController::class, 'allSubjectOfClasse']); //OK
    Route::get('/subjects/allCompetences', [SubjectController::class, 'allCompetences']); //OK
    Route::get('/subjects/allCompetences1', [SubjectController::class, 'allCompetences1']); //OK 
    Route::get('/subjects/allCompetences2', [SubjectController::class, 'allCompetences2']); //OK allCompetences1 VS allCompetences1????


    //==> ANY CONNECTED USER ON STUDENT
    Route::get('/students/allStudents', [StudentController::class, 'allStudents']); //OK
    Route::get('/students/allStudentsOfClasse', [StudentController::class, 'allStudentsOfClasse']); //OK
    Route::get('/students/allStudentsOfClasse2', [StudentController::class, 'allStudentsOfClasse2']); //OK
    Route::get('/students/allStudClassOfAClasse', [StudentController::class, 'allStudClassOfAClasse']); //OK
    Route::get('/students/getSeqMarks', [StudentController::class, 'getSeqMarks']);
    Route::get('/students/getSeqMarks2', [StudentController::class, 'getSeqMarks2']);
    Route::get('/students/getCompMarks', [StudentController::class, 'getCompMarks']);
    Route::get('/students/getCompMarks2', [StudentController::class, 'getCompMarks2']);
    Route::get('/students/getCompMarks', [StudentController::class, 'getCompMarks']);
    Route::get('/students/getAllSeqMarksSimple', [StudentController::class, 'getAllSeqMarksSimple']);
    Route::get('/students/getAllCompMarksSimple', [StudentController::class, 'getAllCompMarksSimple']);
    Route::get('/students/allStudentsForMarks', [StudentController::class, 'allStudentsForMarks']);
    Route::get('/students/allStudentsOfClasse3', [StudentController::class, 'allStudentsOfClasse3']);
    Route::get('/students/allStudentsOfClasseOfSchool', [StudentController::class, 'allStudentsOfClasseOfSchool']);
    Route::get('/students/allStudentSubjectOfTerm', [StudentController::class, 'allStudentSubjectOfTerm']);
    Route::get('/students/allStudentCompMarkOfTerm', [StudentController::class, 'allStudentCompMarkOfTerm']);
    Route::get('/students/allStudentsOfClasseForAbs', [StudentController::class, 'allStudentsOfClasseForAbs']);
    Route::get('/students/getDisciplineOfClasse', [StudentController::class, 'getDisciplineOfClasse']);
    Route::get('/students/allStudentSubjectOfTerm2', [StudentController::class, 'allStudentSubjectOfTerm2']);
    Route::get('/students/allStudentCompMarkOfTerm2', [StudentController::class, 'allStudentCompMarkOfTerm2']);
    Route::get('/students/allStudentSubject', [StudentController::class, 'allStudentSubject']);
    Route::get('/students/allStudentCompMark', [StudentController::class, 'allStudentCompMark']);
    Route::get('/students/getAllDisciplines', [StudentController::class, 'getAllDisciplines']);
    Route::get('/students/getAllDisciplines2', [StudentController::class, 'getAllDisciplines2']);
    Route::get('/students/allStudentsOfClasseOfSection', [StudentController::class, 'allStudentsOfClasseOfSection']);
    Route::get('/students/allStudentsSummaryOfSection', [StudentController::class, 'allStudentsSummaryOfSection']);
    Route::get('/students/fillRateNonApc', [StudentController::class, 'fillRateNonApc']);
    Route::get('/students/fillRateApc', [StudentController::class, 'fillRateApc']);
    Route::get('/students/studentPhoto', [StudentController::class, 'studentPhoto']);
    Route::get('/students/allStudClassOfYear', [StudentController::class, 'allStudClassOfYear']);
    Route::post('/students/uploadSeqMarks', [StudentController::class, 'uploadSeqMarks']);
    Route::post('/students/uploadCompMarks', [StudentController::class, 'uploadCompMarks']);


    //==> ANY CONNECTED USER ON BACKUP
    Route::get('/backup/backupDB', [BackupController::class, 'backupDB']);

    //==> ANY CONNECTED USER ON TEST
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
});
//===================================================================== END ANY CONNECTED USER ROUTES =====================================================================================================
